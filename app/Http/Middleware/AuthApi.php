<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            // Return JSON error response for API requests
            return response()->json([
                'message' => 'Unauthenticated. Please login first.',
                'error' => 'Unauthorized'
            ], 401);
        }

        return $next($request);
    }
}
