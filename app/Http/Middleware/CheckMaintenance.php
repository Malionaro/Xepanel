<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class CheckMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Setting::get('maintenance_mode', false)) {
            // Check if user is logged in and is admin
            if (Auth::check() && Auth::user()->isAdmin()) {
                return $next($request);
            }

            // Allow logout and login routes to avoid lockouts or allow admin to log in
            if ($request->is('login') || $request->is('logout') || $request->routeIs('login')) {
                return $next($request);
            }

            // Show a simple maintenance view or abort
            return response()->view('errors.maintenance', [], 503);
        }

        return $next($request);
    }
}
