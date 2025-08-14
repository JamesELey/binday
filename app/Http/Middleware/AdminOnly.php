<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // For local development, allow all requests
        // In production, you would check authentication here
        if (config('app.env') === 'local') {
            return $next($request);
        }
        
        // In production, add proper admin authentication check here
        // For now, allow all requests to admin routes
        return $next($request);
    }
}
