<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;
use Illuminate\Support\Facades\App;

class SetLanguage
{
    public function handle(Request $request, Closure $next): Response
    {
        // Force language from settings file
        $lang = Setting::get('panel_language', 'en');
        
        App::setLocale($lang);
        config(['app.locale' => $lang]);

        return $next($request);
    }
}
