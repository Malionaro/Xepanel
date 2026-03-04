<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $settings = Setting::all();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        // Validate basic fields
        $request->validate([
            'panel_name' => 'required|string|max:255',
            'docker_base_path' => 'required|string',
            'global_webhook_url' => 'nullable|url',
        ]);

        // Explicitly collect data to ensure booleans are correct
        $data = [
            'panel_name' => $request->input('panel_name'),
            'max_backup_size_mb' => (int) $request->input('max_backup_size_mb', 500),
            'log_tail_lines' => (int) $request->input('log_tail_lines', 100),
            'max_log_size_mb' => (int) $request->input('max_log_size_mb', 10),
            'enable_public_api' => $request->has('enable_public_api'),
            'maintenance_mode' => $request->has('maintenance_mode'),
            'docker_base_path' => $request->input('docker_base_path'),
            'default_timezone' => $request->input('default_timezone', 'UTC'),
            'docker_default_network' => $request->input('docker_default_network', 'bridge'),
            'ui_theme' => $request->input('ui_theme', 'system'),
            'panel_language' => $request->input('panel_language', 'en'),
            'global_webhook_url' => $request->input('global_webhook_url'),
            'discord_bot_token' => $request->input('discord_bot_token'),
            'discord_public_key' => $request->input('discord_public_key'),
            'discord_client_id' => $request->input('discord_client_id'),
            'default_user_ram_mb' => (int) $request->input('default_user_ram_mb', 4096),
            'default_user_cpu_percent' => (int) $request->input('default_user_cpu_percent', 200),
            'default_user_disk_mb' => (int) $request->input('default_user_disk_mb', 10240),
            'default_user_services' => (int) $request->input('default_user_services', 5),
            'session_lifetime' => (int) $request->input('session_lifetime', 120),
            'allow_registration' => $request->has('allow_registration'),
            'branding_logo_url' => $request->input('branding_logo_url'),
        ];

        // Save everything at once
        Setting::setMany($data);

        // Immediately update session locale for instant feedback
        if (isset($data['panel_language'])) {
            session(['locale' => $data['panel_language']]);
            \App::setLocale($data['panel_language']);
        }

        ActivityLog::log("Updated Global Settings", "User: " . Auth::user()->name . " (Maintenance: " . ($data['maintenance_mode'] ? 'ON' : 'OFF') . ")");

        return redirect()->route('settings.index')->with('status', 'Settings updated successfully!');
    }
}
