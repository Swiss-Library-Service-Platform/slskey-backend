<?php

namespace App\Http\Middleware;

use App\Models\LogApi;
use Closure;
use Illuminate\Support\Facades\Log;

class LogApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // Log api request information
        LogApi::create([
            'method' => $request->method(),
            'url' => $request->url(),
            'ip' => $request->ip(),
            'input' => json_encode($request->all()),
            'headers' => json_encode($request->header()),
        ]);

        return $next($request);
    }
}
