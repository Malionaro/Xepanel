<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MetricsController extends Controller
{
    public function getSystemStats()
    {
        // CPU Usage (1 minute load average normalized by cores)
        $cpuLoad = sys_getloadavg()[0];
        $numCores = shell_exec('nproc') ?: 1;
        $cpuPercent = min(100, round(($cpuLoad / $numCores) * 100));

        // RAM Usage (using free)
        $free = shell_exec('free -m');
        $free = (string)trim($free);
        $freeLines = explode("\n", $free);
        $mem = preg_split('/\s+/', $freeLines[1]);
        $memTotal = $mem[1];
        $memUsed = $mem[2];
        $memPercent = round(($memUsed / $memTotal) * 100);

        // Disk Usage (using df)
        $diskTotal = disk_total_space("/");
        $diskFree = disk_free_space("/");
        $diskUsed = $diskTotal - $diskFree;
        $diskPercent = round(($diskUsed / $diskTotal) * 100);

        return response()->json([
            'cpu' => [
                'percent' => $cpuPercent,
                'label' => $cpuLoad . ' (Load Avg)'
            ],
            'ram' => [
                'percent' => $memPercent,
                'used' => round($memUsed / 1024, 2) . ' GB',
                'total' => round($memTotal / 1024, 2) . ' GB'
            ],
            'disk' => [
                'percent' => $diskPercent,
                'used' => round($diskUsed / (1024 * 1024 * 1024), 2) . ' GB',
                'total' => round($diskTotal / (1024 * 1024 * 1024), 2) . ' GB'
            ]
        ]);
    }

    public function getServiceStats($id)
    {
        $service = \App\Models\Service::find($id);
        
        if (!$service || $service->getStatus() !== 'running') {
            return response()->json([
                'cpu' => '0.0',
                'ram' => '0.0 MB'
            ]);
        }

        $cpu = '0.0';
        $ramText = '0.0 MB';
        $ramRaw = 0; // MB

        if ($service->type === 'docker') {
            $containerName = escapeshellarg($service->docker_container_name);
            $output = shell_exec("docker stats --no-stream --format '{{.CPUPerc}},{{.MemUsage}}' {$containerName} 2>/dev/null");
            
            if ($output) {
                $parts = explode(',', trim($output));
                $cpu = str_replace('%', '', $parts[0] ?? '0.0');
                $ramText = explode('/', $parts[1] ?? '0.0 MB / 0.0 MB')[0];
                
                // Parse raw RAM MB for history
                if (preg_match('/([0-9\.]+)\s*(MB|GB|B|KiB|MiB|GiB)/i', $ramText, $matches)) {
                    $value = (float)$matches[1];
                    $unit = strtoupper($matches[2]);
                    if ($unit === 'GB' || $unit === 'GIB') $value *= 1024;
                    if ($unit === 'B') $value /= (1024 * 1024);
                    if ($unit === 'KIB' || $unit === 'KB') $value /= 1024;
                    $ramRaw = $value;
                }
            }
        } else {
            if ($service->pid) {
                $output = shell_exec("ps -p " . escapeshellarg($service->pid) . " -o %cpu,rss --no-headers");
                if ($output) {
                    $parts = preg_split('/\s+/', trim($output));
                    $cpu = isset($parts[0]) ? number_format((float)$parts[0], 1) : '0.0';
                    $ramKb = isset($parts[1]) ? (int)$parts[1] : 0;
                    $ramRaw = $ramKb / 1024;
                    $ramText = number_format($ramRaw, 1) . ' MB';
                }
            }
        }

        // Save history (last 30 points)
        $this->saveHistory($id, (float)$cpu, (float)$ramRaw);

        return response()->json([
            'cpu' => number_format((float)$cpu, 1),
            'ram' => trim($ramText)
        ]);
    }

    private function saveHistory($id, $cpu, $ram)
    {
        $path = "metrics_history/{$id}.json";
        $history = [];
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
            $history = json_decode(\Illuminate\Support\Facades\Storage::disk('local')->get($path), true) ?: [];
        }

        $history[] = [
            'time' => now()->format('H:i:s'),
            'cpu' => $cpu,
            'ram' => $ram
        ];

        // Keep last 30 entries
        if (count($history) > 30) {
            array_shift($history);
        }

        \Illuminate\Support\Facades\Storage::disk('local')->put($path, json_encode($history));
    }

    public function getServiceHistory($id)
    {
        $path = "metrics_history/{$id}.json";
        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
            return response()->json([]);
        }
        return response()->json(json_decode(\Illuminate\Support\Facades\Storage::disk('local')->get($path), true));
    }

    public function getServiceHistory24h($id)
    {
        $path24h = "metrics_24h/{$id}.json";
        $pathShort = "metrics_history/{$id}.json";
        
        $history = [];
        
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($path24h)) {
            $history = json_decode(\Illuminate\Support\Facades\Storage::disk('local')->get($path24h), true) ?: [];
        }
        
        // Fallback: If 24h history is empty, use the short-term history to avoid 0% displays
        if (empty($history) && \Illuminate\Support\Facades\Storage::disk('local')->exists($pathShort)) {
            $shortHistory = json_decode(\Illuminate\Support\Facades\Storage::disk('local')->get($pathShort), true) ?: [];
            foreach ($shortHistory as $entry) {
                $history[] = [
                    'time' => now()->subMinutes(30)->format('Y-m-d') . ' ' . $entry['time'],
                    'cpu' => $entry['cpu'],
                    'ram' => $entry['ram']
                ];
            }
        }
        
        return response()->json($history);
    }
}
