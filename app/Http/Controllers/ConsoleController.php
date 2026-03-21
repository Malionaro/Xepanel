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
            $containerName = $service->docker_container_name;
            $dockerLogs = "";

            $res = $service->dockerApi('GET', '/v1.41/containers/' . urlencode($containerName) . '/logs?stdout=1&stderr=1&tail=' . $lines, [], 5);
            
            if ($res && $res['status'] === 200) {
                // Docker logs might contain invalid UTF-8 sequences (e.g., from Java process console)
                // which causes response()->json() to throw a 500 Malformed UTF-8 error.
                $rawLogs = $this->demultiplexDockerLogs($res['body']);
                $dockerLogs = mb_convert_encoding($rawLogs, 'UTF-8', 'UTF-8');
            }
            
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
            // Attach to the running Docker container and write to its STDIN. 
            // We use raw stream socket because the Docker API requires connection hijacking for stdin.
            $fp = @stream_socket_client('unix:///var/run/docker.sock', $errNo, $errStr, 5);
            if ($fp) {
                $containerId = urlencode($service->docker_container_name);
                $req = "POST /v1.41/containers/{$containerId}/attach?stdin=1&stream=1 HTTP/1.1\r\n";
                $req .= "Host: localhost\r\n";
                $req .= "Connection: Upgrade\r\n";
                $req .= "Upgrade: tcp\r\n\r\n";
                
                fwrite($fp, $req);
                // Give it a tiny fraction of a second to upgrade the protocol before blasting the command
                usleep(50000); 
                fwrite($fp, $command . "\n");
                fclose($fp);
            }
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
