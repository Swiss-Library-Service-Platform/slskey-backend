<?php

namespace App\Http\Middleware;

use App\Enums\WebhookResponseEnums;
use App\Enums\WorkflowEnums;
use App\Models\SlskeyGroup;
use Closure;
use GuzzleHttp\Psr7\Request;

class AuthWebhooks
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        // Check if institution has webhook workflow
        $slskeyCode = $request->route()->parameter('slskey_code');
        $institution = $request->input('institution.value');
        if (! $institution) {
            return response(WebhookResponseEnums::ERROR_NO_INSTITUTION, 422);
        }

        // Get SlskeyGroup Details
        $slskeyGroup = SlskeyGroup::where('slskey_code', $slskeyCode)
            ->where('workflow', WorkflowEnums::WEBHOOK)
            ->where('alma_iz', $institution)->first();

        if (! $slskeyGroup) {
            return response(WebhookResponseEnums::ERROR_NO_SLSKEY_GROUP, 422);
        }

        // Validate signature of request
        $secret = $slskeyGroup->webhook_secret ?? '';
        $signature = $request->header('HTTP_X_EXL_SIGNATURE') ?? $request->header('X-Exl-Signature');
        $validRequest = $this->isHashValid($secret, $request->getContent(), $signature);
        if (! $validRequest) {
            return response(WebhookResponseEnums::ERROR_INVALID_SECRET, 422);
        }

        // Process request
        return $next($request);
    }

    /**
     * Check if a given hash is valid by comparing it with the computed hash.
     *
     * @param  string  $secret  The secret key used for hashing.
     * @param  string  $payload  The payload to be hashed.
     * @param  string  $verify  The hash to be compared with the computed hash.
     * @return bool True if the hash is valid, false otherwise.
     */
    protected function isHashValid($secret, $payload, $verify)
    {
        $computedHash = $this->computeHash($secret, $payload);

        return hash_equals($verify, $computedHash);
    }

    /**
     * Compute a hashed representation of the payload using HMAC-SHA256.
     *
     * @param  string  $secret  The secret key used for hashing.
     * @param  string  $payload  The payload to be hashed.
     * @return string The computed hash in base64-encoded format.
     */
    protected function computeHash($secret, $payload)
    {
        $hexHash = hash_hmac('sha256', $payload, utf8_encode($secret));
        $base64Hash = base64_encode(hex2bin($hexHash));

        return $base64Hash;
    }
}
