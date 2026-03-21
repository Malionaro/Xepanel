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

    public function adminStats()
    {
        if (auth()->user()->role !== 'admin') abort(403);
        session_write_close(); // CRITICAL: Release session for long stats query
        
        $services = Service::all();
        $stats = $this->calculateAdminStats($services);
        
        return response()->json($stats);
    }

    private function calculateAdminStats($services) {
        $running = $services->filter(fn($s) => $s->getStatus() === 'running');
        
        $totalCpu = 0;
        $totalRamMb = 0;

        // BATCH DOCKER STATS (Extremely faster than per-service)
        $dockerServices = $running->filter(fn($s) => $s->type === 'docker');
        if ($dockerServices->isNotEmpty()) {
            $names = $dockerServices->map(fn($s) => escapeshellarg($s->docker_container_name))->implode(' ');
            $output = shell_exec("timeout 5 docker stats --no-stream --format '{{.Name}},{{.CPUPerc}},{{.MemUsage}}' {$names} 2>/dev/null");
            
            if ($output) {
                $lines = explode("\n", trim($output));
                foreach ($lines as $line) {
                    $parts = explode(',', $line);
                    if (count($parts) >= 3) {
                        $totalCpu += (float)str_replace('%', '', $parts[1]);
                        $ramText = explode('/', $parts[2])[0];
                        if (preg_match('/([0-9\.]+)\s*(MB|GB|B|KiB|MiB|GiB)/i', $ramText, $matches)) {
                            $val = (float)$matches[1];
                            $unit = strtoupper($matches[2]);
                            if (str_starts_with($unit, 'G')) $val *= 1024;
                            if ($unit === 'B') $val /= (1024 * 1024);
                            if (str_starts_with($unit, 'K')) $val /= 1024;
                            $totalRamMb += $val;
                        }
                    }
                }
            }
        }

        // Process-based services
        foreach ($running->filter(fn($s) => $s->type !== 'docker') as $s) {
            if ($s->pid) {
                $output = shell_exec("ps -p " . escapeshellarg($s->pid) . " -o %cpu,rss --no-headers");
                if ($output) {
                    $parts = preg_split('/\s+/', trim($output));
                    $totalCpu += (float)($parts[0] ?? 0);
                    $totalRamMb += ((int)($parts[1] ?? 0)) / 1024;
                }
            }
        }

        // Host system stats
        $hostStats = app(MetricsController::class)->getSystemStats()->getData(true);

        return [
            'total_services' => $services->count(),
            'running_services' => $running->count(),
            'stopped_services' => $services->count() - $running->count(),
            'services_cpu' => round($totalCpu, 1),
            'services_ram_mb' => round($totalRamMb, 1),
            'host' => $hostStats
        ];
    }
}
