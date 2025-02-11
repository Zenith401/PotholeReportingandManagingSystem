<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\Auth;

class Super
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // Check if the user has the SuperAdmin role
        if ($user->role == 1) {
            return $next($request);
        }

        // If not authorized, abort with a 403 Forbidden response
        abort(403, 'Unauthorized action.');
    }
}

