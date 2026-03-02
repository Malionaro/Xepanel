<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConsoleController extends Controller
{
    private function checkAccess($service)
    {
        $user = auth()->user();
        if ($user->role === 'admin') return true;
        if (isset($service->allowed_users) && in_array($user->id, $service->allowed_users)) return true;
        abort(403);
    }

    public function getLogs($id)
    {
        $service = Service::find($id);
        if (!$service) return response()->json(['error' => 'Not found'], 404);
        $this->checkAccess($service);

        $lines = (int) \App\Models\Setting::get('log_tail_lines', 100);

        if ($service->type === 'docker') {
            $containerName = escapeshellarg($service->docker_container_name);
            
            // Check if container exists (quietly)
            $exists = shell_exec("docker inspect {$containerName} > /dev/null 2>&1 && echo 'yes'");
            $dockerLogs = "";

            if (trim($exists) === 'yes') {
                $dockerLogs = shell_exec("docker logs --tail {$lines} {$containerName} 2>&1");
            }
            
            // Also check the local log file for pull progress/startup info
            $logPath = storage_path("logs/services/{$id}.log");
            $localLogs = "";
            if (file_exists($logPath)) {
                $localLogs = shell_exec("tail -n {$lines} " . escapeshellarg($logPath));
            }

            $combined = "";
            if ($localLogs) $combined .= "[Panel] Startup/Pull Logs:\n" . $localLogs . "\n";
            if ($dockerLogs) $combined .= "[Docker] Container Logs:\n" . $dockerLogs;

            return response()->json(['logs' => $combined ?: '--- Awaiting container startup ---']);
        }

        $path = storage_path("logs/services/{$id}.log");
        if (!file_exists($path)) return response()->json(['logs' => '']);

        // Read last N lines
        $logs = shell_exec("tail -n {$lines} " . escapeshellarg($path));
        return response()->json(['logs' => $logs]);
    }

    public function executeCommand(Request $request, $id)
    {
        $service = Service::find($id);
        if (!$service) return response()->json(['error' => 'Not found'], 404);
        $this->checkAccess($service);

        $command = $request->input('command');
        if (!$command) return response()->json(['success' => false]);

        if ($service->type === 'docker') {
            $containerName = escapeshellarg($service->docker_container_name);
            // Commands in docker exec -d don't usually show up in 'docker logs' unless piped to stdout
            // but we'll try to execute it
            $safeCommand = "docker exec -d {$containerName} " . $command;
            shell_exec($safeCommand);
        } else {
            $logPath = storage_path("logs/services/{$id}.log");
            $cwd = escapeshellarg($service->working_dir);
            
            // Write the command prompt to the log visually
            $prompt = "\n> " . $command . "\n";
            file_put_contents($logPath, $prompt, FILE_APPEND);

            // Execute and append output to log
            $safeCommand = "cd {$cwd} && (" . $command . ") >> " . escapeshellarg($logPath) . " 2>&1 &";
            shell_exec($safeCommand);
        }
        
        ActivityLog::log("Executed Terminal Command", "Service: {$service->name}, Command: {$command}");

        return response()->json(['success' => true]);
    }
}
