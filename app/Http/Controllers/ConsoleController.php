<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ActivityLog;
use App\Services\Docker\DockerClient;
use App\Services\Docker\DockerLogReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConsoleController extends Controller
{
    private function checkAccess($service)
    {
        $user = auth()->user();
        if ($user->isAdmin() || $user->hasPermission('view_services')) return true;
        if (isset($service->allowed_users) && in_array($user->id, $service->allowed_users)) return true;
        abort(403);
    }

    private function demultiplexDockerLogs($body)
    {
        // If the container was not started with TTY enabled, Docker multiplexes stdout/stderr
        // with an 8-byte header per frame. Format: [8 BYTE HEADER][PAYLOAD]
        // Header: [1 byte type (0=stdin,1=stdout,2=stderr)] [3 bytes reserved] [4 bytes uint32 payload size]
        
        // If content is clean plain text without the tricky unprintable bytes at the very start, return as is.
        // We do a quick check to see if the first byte is 0x01 or 0x02.
        if (strlen($body) > 0 && (ord($body[0]) === 1 || ord($body[0]) === 2)) {
            $decoded = '';
            $offset = 0;
            $len = strlen($body);
            while ($offset < $len) {
                if ($offset + 8 > $len) break;
                
                $header = substr($body, $offset, 8);
                $type = ord($header[0]);
                
                if ($type > 2) {
                    // Fallback if we accidentally caught something that doesn't follow the multiplex protocol
                    return preg_replace('/[^\x20-\x7E\n\r\t]/', '', $body);
                }
                
                $sizeArray = unpack('N', substr($header, 4, 4));
                $size = $sizeArray[1];
                
                $offset += 8;
                if ($offset + $size > $len) { $size = $len - $offset; } // Clamp if truncated
                $decoded .= substr($body, $offset, $size);
                $offset += $size;
            }
            return $decoded;
        }
        
        return $body;
    }

    public function getLogs($id)
    {
        $service = Service::find($id);
        if (!$service) return response()->json(['error' => 'Not found'], 404);
        $this->checkAccess($service);

        $lines = (int) \App\Models\Setting::get('log_tail_lines', 100);

        if ($service->type === 'docker') {
            $dockerLogs = app(DockerLogReader::class)->tail($service->docker_container_name, $lines) ?? "";
            
            // Also check the local log file for pull progress/startup info
            $logPath = storage_path("logs/services/{$id}.log");
            $localLogs = "";
            if (file_exists($logPath)) {
                $localLogs = trim(shell_exec("tail -n {$lines} " . escapeshellarg($logPath)));
                $localLogs = mb_convert_encoding($localLogs, 'UTF-8', 'UTF-8');
            }

            $combined = "";
            if ($localLogs) $combined .= "[Panel] Startup logs:\n" . $localLogs . "\n";
            if ($dockerLogs) $combined .= "[Docker] Container Logs:\n" . $dockerLogs;

            return response()->json(['logs' => $combined ?: "--- Docker ist nicht erreichbar. Installiere Docker Desktop oder starte das Panel mit Docker Compose. ---"]);
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
            app(DockerClient::class)->attachStdin($service->docker_container_name, $command);
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
