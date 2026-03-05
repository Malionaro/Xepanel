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

        // Save everything provided in the request
        $settings = $request->except(['_token']);
        
        // Handle checkboxes (booleans)
        $settings['enable_public_api'] = $request->has('enable_public_api');
        $settings['maintenance_mode'] = $request->has('maintenance_mode');
        $settings['allow_registration'] = $request->has('allow_registration');

        Setting::setMany($settings);

        if (isset($settings['panel_language'])) {
            session(['locale' => $settings['panel_language']]);
            \App::setLocale($settings['panel_language']);
        }

        ActivityLog::log("Updated Global Settings", "User: " . Auth::user()->name);

        return redirect()->route('settings.index')->with('status', 'Settings updated successfully!');
    }

    /**
     * Check for new commits on GitHub.
     */
    public function checkForUpdates()
    {
        $token = Setting::get('github_token');
        $repo = Setting::get('github_repo', 'malo/panel'); // Fallback to a default if not set
        
        $url = "https://api.github.com/repos/{$repo}/commits/main";
        
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: PHP',
                    $token ? "Authorization: token {$token}" : ""
                ]
            ]
        ];
        
        try {
            $context = stream_context_create($opts);
            $response = file_get_contents($url, false, $context);
            $commit = json_decode($response, true);
            
            $latestSha = $commit['sha'] ?? null;
            $currentSha = trim(shell_exec('git rev-parse HEAD'));
            
            return response()->json([
                'current' => substr($currentSha, 0, 7),
                'latest' => substr($latestSha, 0, 7),
                'has_update' => $latestSha !== $currentSha,
                'message' => $commit['commit']['message'] ?? 'No message'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not connect to GitHub'], 500);
        }
    }

    /**
     * Perform the update.
     */
    public function runUpdate()
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $logPath = storage_path('logs/update.log');
        
        // Background script to run the update
        $script = "#!/bin/bash\n";
        $script .= "cd " . base_path() . "\n";
        $script .= "git pull origin main >> {$logPath} 2>&1\n";
        $script .= "composer install --no-interaction --no-dev >> {$logPath} 2>&1\n";
        $script .= "php artisan migrate --force >> {$logPath} 2>&1\n";
        $script .= "php artisan config:clear >> {$logPath} 2>&1\n";
        $script .= "php artisan view:clear >> {$logPath} 2>&1\n";
        
        $scriptPath = storage_path('app/run_update.sh');
        file_put_contents($scriptPath, $script);
        chmod($scriptPath, 0755);
        
        exec("nohup {$scriptPath} > /dev/null 2>&1 &");
        
        ActivityLog::log("System Update Started", "The update process was initiated from the web interface.");
        
        return response()->json(['status' => 'Update started in background. Check update.log for details.']);
    }
}
