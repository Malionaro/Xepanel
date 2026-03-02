<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\FileUser;
use Symfony\Component\HttpFoundation\Response;

class CheckApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!\App\Models\Setting::get('enable_public_api', true)) {
            return response()->json(['error' => 'API is currently disabled globally.'], 403);
        }

        $token = $request->bearerToken() ?? $request->input('api_token');

        if (!$token) {
            return response()->json(['error' => 'API token missing'], 401);
        }

        $users = FileUser::all();
        $authenticatedUser = null;

        foreach ($users as $user) {
            if (isset($user->api_keys) && is_array($user->api_keys)) {
                foreach ($user->api_keys as $keyData) {
                    if ($keyData['token'] === $token) {
                        $authenticatedUser = $user;
                        break 2;
                    }
                }
            }
        }

        if (!$authenticatedUser) {
            return response()->json(['error' => 'Invalid API token'], 401);
        }

        // Optional: you can log in the user for the request if needed
        // Auth::login($authenticatedUser);

        return $next($request);
    }
}
