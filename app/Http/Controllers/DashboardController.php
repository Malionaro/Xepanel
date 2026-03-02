<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\DiscordMessage;
use App\Models\ActivityLog;

class DashboardController extends Controller
{
    public function index()
    {
        // Ensure default eggs with new variables are loaded
        \App\Models\Egg::seedDefaults();

        $user = auth()->user();
        $services = Service::all();

        if ($user->role !== 'admin') {
            $services = $services->filter(function($service) use ($user) {
                return isset($service->allowed_users) && in_array($user->id, $service->allowed_users);
            });
        }

        $discordMessages = DiscordMessage::all();
        $latestActivities = ActivityLog::all()->take(5);
        
        return view('dashboard', compact('services', 'discordMessages', 'latestActivities'));
    }
}
