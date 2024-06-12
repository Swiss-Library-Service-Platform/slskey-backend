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
use Illuminate\Database\Eloquent\Collection;
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
                // Check if there are slskeygroups for this institution
                $slskeyGroups = SlskeyGroup::where('alma_iz', $decodedToken->inst_code)->get();

                if ($slskeyGroups->isEmpty()) {
                    return new Response('Authorization failed. No SLSKey Groups found for this institution.', 403);
                }

                // Check if user has alma permissions
                $userHasAlmaPermission = $this->checkUserHasAlmaPermissions($decodedToken);

                if (!$userHasAlmaPermission['success']) {
                    // Check if permissions have to be removed
                    $user = $this->findUserFromToken($decodedToken);
                    if ($user) {
                        foreach ($slskeyGroups as $slskeyGroup) {
                            $user->removePermissions($slskeyGroup->slskey_code);
                        }
                    }

                    return new Response("Authorization failed. {$userHasAlmaPermission['message']}", 403);
                }

                // Create or update User with SLSKey Group Permissions
                $user = $this->createOrUpdateUserWithPermissionsForSlskeyGroups($decodedToken, $slskeyGroups);
            } else {
                // Find user from token
                $user = $this->findUserFromToken($decodedToken);

                if (!$user) {
                    return new Response('Authorization failed. User not found.', 403);
                }
            }

            // Log the user in, so we can use Auth::user() in the controllers
            Auth::setUser($user);

            // Store institution in session to make it available in the controllers
            session(['alma_institution' => $decodedToken->inst_code]);

            return $next($request);
        } catch (\Exception $e) {
            return new Response("Authorization failed: {$e->getMessage()}", 401);
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
        return User::where('user_identifier', $token->sub)->first();
    }

    /**
     * Check if the user has permissions in Alma.
     *
     * @param object $token
     * @return [success, message]
     */
    protected function checkUserHasAlmaPermissions(object $token)
    {
        $institution = $token->inst_code;
        $username = $token->sub;

       //TODO: set different key depending on IZ

        $almaServiceResponse = $this->almaApiService->getUserByIdentifier($username);

        if (!$almaServiceResponse->success) {
            return [
                'success' => false,
                'message' => $almaServiceResponse->errorText
            ];
        }

        $almaUser = $almaServiceResponse->almaUser;

        if ($almaUser->record_type != AlmaEnums::RECORD_TYPE_STAFF_USER) {
            return [
                'success' => false,
                'message' => 'User is not a staff user in Alma.'
            ];
        }

        // TODO: check if there are roles in the user for this institution
        $roles = $almaUser->roles;
        if (!$roles) {
            return [
                'success' => false,
                'message' => 'User has no roles in Alma.'
            ];
        }

        // Success
        return [
            'success' => true,
            'message' => 'User has roles in Alma.'
        ];
    }

    /**
     * Create or update the user with permissions for the SLSKey groups.
     *
     * @param object $token
     * @param array $slskeyGroups
     * @return User user
     */
    protected function createOrUpdateUserWithPermissionsForSlskeyGroups(object $token, Collection $slskeyGroups)
    {
        $user = User::updateOrCreate([
            'user_identifier' => $token->sub,
        ], [
            'display_name' => $token->sub,

            // TODO: what about these fields?
            'is_edu_id' => 0,
            'password' => bcrypt('FIXME'),
        ]);

        foreach ($slskeyGroups as $slskeyGroup) {
            $user->givePermissions($slskeyGroup->slskey_code);
        }

        return $user;
    }
}
