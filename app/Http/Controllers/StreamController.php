<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamController extends Controller
{
    public function streamLogs($id)
    {
        if (session_id()) session_write_close(); 
        
        $service = Service::find($id);
        if (!$service) return response()->json(['error' => 'Not found'], 404);

        return new StreamedResponse(function () use ($id, $service) {
            $path = storage_path("logs/services/{$id}.log");
            $lastPos = 0;
            $startTime = time();

            if (file_exists($path)) {
                $lines = (int) \App\Models\Setting::get('log_tail_lines', 100);
                $initialLogs = shell_exec("tail -n {$lines} " . escapeshellarg($path));
                echo "data: " . json_encode(['logs' => $initialLogs]) . "\n\n";
                if (ob_get_level() > 0) ob_flush();
                flush();
                $lastPos = filesize($path);
            }

            while (true) {
                // End stream after 30 seconds to free up the worker for other requests
                if (connection_aborted() || (time() - $startTime) > 30) break;

                if (file_exists($path)) {
                    clearstatcache(true, $path);
                    $currentSize = filesize($path);
                    if ($currentSize > $lastPos) {
                        $fp = fopen($path, 'r');
                        fseek($fp, $lastPos);
                        $newContent = fread($fp, max(1, $currentSize - $lastPos));
                        fclose($fp);
                        echo "data: " . json_encode(['logs' => $newContent, 'append' => true]) . "\n\n";
                        if (ob_get_level() > 0) ob_flush();
                        flush();
                        $lastPos = $currentSize;
                    }
                }
                usleep(500000); // 0.5s check
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function streamStats($id)
    {
        if (session_id()) session_write_close();

        return new StreamedResponse(function () use ($id) {
            $startTime = time();
            while (true) {
                if (connection_aborted() || (time() - $startTime) > 30) break;

                $stats = app(MetricsController::class)->getServiceStats($id)->getData();
                echo "data: " . json_encode($stats) . "\n\n";
                if (ob_get_level() > 0) ob_flush();
                flush();

                sleep(2);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
