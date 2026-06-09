<?php

namespace App\Http\Middleware;

use App\Models\FileUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToSetupIfNeeded
{
    public function handle(Request $request, Closure $next): Response
    {
        if (FileUser::all()->isEmpty() && ! $request->routeIs('setup.*')) {
            return redirect()->route('setup.index');
        }

        return $next($request);
    }
}
