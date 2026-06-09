<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Services\PanelScriptRunner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index()
    {
        if (! Auth::user()->hasPermission('manage_settings')) {
            abort(403);
        }

        $settings = Setting::all();

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        if (! Auth::user()->hasPermission('manage_settings')) {
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

        ActivityLog::log('Updated Global Settings', 'User: '.Auth::user()->name);

        return redirect()->route('settings.index')->with('status', 'Settings updated successfully!');
    }

    /**
     * Check for new commits on GitHub.
     */
    public function checkForUpdates()
    {
        $token = Setting::get('github_token');
        $repo = Setting::get('github_repo', 'Malionaro/Xepanel'); // Corrected default repo

        if (! preg_match('/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/', $repo)) {
            return response()->json(['error' => 'Invalid GitHub repository format'], 422);
        }

        $url = "https://api.github.com/repos/{$repo}/commits/main";

        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: PHP',
                    $token ? "Authorization: token {$token}" : '',
                ],
            ],
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
                'message' => $commit['commit']['message'] ?? 'No message',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not connect to GitHub'], 500);
        }
    }

    /**
     * Perform the update.
     */
    public function runUpdate(PanelScriptRunner $runner)
    {
        if (! Auth::user()->hasPermission('manage_settings')) {
            abort(403);
        }

        $logPath = storage_path('logs/update.log');
        $command = [PHP_BINARY ?: 'php', 'artisan', 'panel:update', '--repo='.Setting::get('github_repo', 'Malionaro/Xepanel')];

        if (PHP_OS_FAMILY === 'Windows') {
            $encoded = base64_encode(json_encode($command));
            $launcher = storage_path('app/run_update.ps1');
            file_put_contents($launcher, '$command = [System.Text.Encoding]::UTF8.GetString([System.Convert]::FromBase64String("'.$encoded.'")) | ConvertFrom-Json'."\n".
                '$psi = New-Object System.Diagnostics.ProcessStartInfo'."\n".
                '$psi.FileName = $command[0]'."\n".
                '$psi.WorkingDirectory = "'.str_replace('\\', '\\\\', base_path()).'"'."\n".
                'foreach ($arg in $command[1..($command.Length - 1)]) { [void]$psi.ArgumentList.Add($arg) }'."\n".
                '$psi.RedirectStandardOutput = $true'."\n".
                '$psi.RedirectStandardError = $true'."\n".
                '$psi.UseShellExecute = $false'."\n".
                '$p = [System.Diagnostics.Process]::Start($psi)'."\n".
                '$p.StandardOutput.ReadToEnd() + $p.StandardError.ReadToEnd() | Out-File -FilePath "'.str_replace('\\', '\\\\', $logPath).'" -Append -Encoding utf8'."\n");
            pclose(popen('start /B "" powershell -ExecutionPolicy Bypass -File '.escapeshellarg($launcher), 'r'));
        } else {
            $script = "#!/bin/bash\ncd ".escapeshellarg(base_path())."\n";
            $script .= implode(' ', array_map('escapeshellarg', $command)).' >> '.escapeshellarg($logPath)." 2>&1 &\n";
            $scriptPath = storage_path('app/run_update.sh');
            $runner->writeExecutableScript($scriptPath, $script);
            $runner->runInBackground($scriptPath);
        }

        ActivityLog::log('System Update Started', 'The update process was initiated from the web interface.');

        return response()->json(['status' => 'Update started in background. Check storage/logs/update.log for details.']);
    }
}
