<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    /**
     * Handle an incoming request.
     * This middleware sanitizes input data by trimming whitespace and stripping HTML tags
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $sanitized = collect($request->all())->map(function ($value) {
            return is_string($value) ? trim(strip_tags($value)) : $value;
        });

        $request->merge(input: $sanitized->toArray());

        return $next($request);
    }
}
