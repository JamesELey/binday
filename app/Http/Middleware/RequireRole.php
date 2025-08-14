<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|array $roles - Required roles (comma-separated string or array)
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to access this page.');
        }

        $user = auth()->user();

        // Check if user is active
        if (!$user->active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account has been deactivated.');
        }

        // If no roles specified, just check authentication
        if (empty($roles)) {
            return $next($request);
        }

        // Handle comma-separated roles (e.g., "admin,worker")
        $allowedRoles = [];
        foreach ($roles as $role) {
            if (strpos($role, ',') !== false) {
                $allowedRoles = array_merge($allowedRoles, explode(',', $role));
            } else {
                $allowedRoles[] = $role;
            }
        }
        
        // Remove duplicates and trim whitespace
        $allowedRoles = array_unique(array_map('trim', $allowedRoles));

        // Check if user has any of the required roles
        if (in_array($user->role, $allowedRoles)) {
            return $next($request);
        }

        // User doesn't have required role
        abort(403, 'You do not have permission to access this page.');
    }
}
