<?php

namespace App\Http\Middleware;

use App\Enums\AlmaEnums;
use App\Interfaces\AlmaAPIInterface;
use App\Models\SlskeyGroup;
use App\Models\User;
use App\Services\API\AlmaAPIService;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthCloudApp
{
    protected $almaApiService;

    /**
     * Create a new middleware instance.
     *
     * @param AlmaAPIService $almaApiService
     */
    public function __construct(AlmaAPIInterface $almaApiService)
    {
        $this->almaApiService = $almaApiService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $token = $this->extractTokenFromRequest($request);

            if (!$token) {
                return new Response('Authorization failed. Token not provided.', 401);
            }
            $decodedToken = $this->decodeJWTToken($token);

            if (!$decodedToken) {
                return new Response('Authorization failed. Invalid token.', 401);
            }

            // If request is authentication, we need to check permissions
            if ($request->routeIs('cloudapp.authenticate')) {
                // Add or Remove User Permissions based on Alma roles and Slskeygroups configurations
                $permissionsResponse = $this->manageSlskeyGroupPermissions($decodedToken);
                if ($permissionsResponse) {
                    return $permissionsResponse;
                }
            }

            // Find user from token
            $user = $this->findUserFromToken($decodedToken);

            if (!$user) {
                return new Response('Authorization failed. User not found.', 403);
            }

            // Log the user in, so we can use Auth::user() in the controllers
            Auth::setUser($user);
            $user->updateLastLogin();

            return $next($request);
        } catch (\Exception $e) {
            return new Response("Authorization failed: {$e->getMessage()}", 401);
        }
    }

    /**
     * Manage SLSkey Group Permissions based on Alma Permisisons
     * Returns error response if failed, null if success
     *
     * @param  object  $decodedToken
     *
     * @return Response|null
     */
    protected function manageSlskeyGroupPermissions($decodedToken)
    {
        // Check if there are slskeygroups for this institution
        $slskeyGroups = SlskeyGroup::where('alma_iz', $decodedToken->inst_code)->get();

        if ($slskeyGroups->isEmpty()) {
            return new Response('Authorization failed. No SLSKey Groups found for this institution.', 403);
        }

        $user = $this->findUserFromToken($decodedToken);

        // Get SlskeyGroups that are allowed for user
        foreach ($slskeyGroups as $slskeyGroup) {
            $isPermitted = $this->checkSlskeyGroupPermitted($slskeyGroup, $decodedToken);
            if ($isPermitted) {
                $user = $this->createOrUpdateUserWithPermissionsForSlskeyGroup($decodedToken, $slskeyGroup);
            } else {
                if ($user) {
                    $user->removePermissions($slskeyGroup->slskey_code);
                }
            }
        }

        // Check if user has any permissions
        if (!$user || !$user->hasAnyPermissions()) {
            return new Response("Authorization failed. User has no permissions for any SLSKey Groups", 403);
        }
    }

    /**
     * Extract the JWT token from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function extractTokenFromRequest($request)
    {
        return $request->bearerToken();
    }

    /**
     * Decode the JWT token using the public key.
     *
     * @param  string  $jwt
     * @return object|null
     */
    protected function decodeJWTToken($jwt)
    {
        $filePath = storage_path('jwt/alma-public-key.pem');
        $pubKey = file_get_contents($filePath);

        return JWT::decode($jwt, new Key($pubKey, 'RS256'));
    }

    /**
     * Find the user from the decoded JWT token.
     *
     * @param  object  $token
     * @return \App\Models\User|null
     */
    protected function findUserFromToken(object $token)
    {
        return User::where('user_identifier', "$token->inst_code-$token->sub")->first();
    }

    /**
     * Check if the user has permissions in Alma for selected SLSKeyGroup
     *
     * @param SlskeyGroup $slskeyGroup
     * @param object $token
     * @return [success, message]
     */
    protected function checkSlskeyGroupPermitted(SlskeyGroup $slskeyGroup, object $token): bool
    {
        // is Cloud app forbidden by configuration of SLSKeyGroup
        if (!$slskeyGroup->cloud_app_allow) {
            return false;
        }

        // Get staff user from alma Iz
        $institution = $token->inst_code;
        $username = $token->sub;
        $almaServiceResponse = $this->almaApiService->getStaffUserFromSingleIz($username, $institution);

        if (!$almaServiceResponse->success) {
            return false;
        }

        $almaUser = $almaServiceResponse->almaUser;

        if ($almaUser->record_type != AlmaEnums::RECORD_TYPE_STAFF_USER) {
            // User is not a staff user in Alma.
            return false;
        }

        // Check if Cloud App usage is limited to alma roles or scopes
        if ($slskeyGroup->cloud_app_roles) {
            $cloudAppRoles = explode(';', $slskeyGroup->cloud_app_roles);
            $cloudAppRolesScopes = $slskeyGroup->cloud_app_roles_scopes ? explode(';', $slskeyGroup->cloud_app_roles_scopes) : null;
            print_r($cloudAppRoles);
            // Check if user has a role in almauser->roles, that exists in cloudAppRoles and scope exists in cloudAppRolesScopes
            $hasRole = false;
            foreach ($almaUser->roles as $role) {
                if (in_array($role->role, $cloudAppRoles)) {
                    if (!$cloudAppRolesScopes || in_array($role->scope, $cloudAppRolesScopes)) {
                        $hasRole = true;

                        break;
                    }
                }
            }
            if (!$hasRole) {
                return false;
            }
        }

        // Success
        return true;
    }

    /**
     * Create or update the user with permissions for the SLSKey groups.
     *
     * @param object $token
     * @param array $slskeyGroups
     * @return User user
     */
    protected function createOrUpdateUserWithPermissionsForSlskeyGroup(object $token, SlskeyGroup $slskeyGroup)
    {
        $user = User::updateOrCreate([
            'user_identifier' => "$token->inst_code-$token->sub",
        ], [
            'display_name' => $token->sub,
            'is_edu_id' => 0,
            'is_alma' => 1,
            'password' => bcrypt(str_random(10)),
        ]);

        $user->givePermissions($slskeyGroup->slskey_code);

        return $user;
    }
}
