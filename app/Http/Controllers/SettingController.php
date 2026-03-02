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
        ];

        // Save everything at once
        Setting::setMany($data);

        ActivityLog::log("Updated Global Settings", "User: " . Auth::user()->name . " (Maintenance: " . ($data['maintenance_mode'] ? 'ON' : 'OFF') . ")");

        return redirect()->route('settings.index')->with('status', 'Settings updated successfully!');
    }
}
