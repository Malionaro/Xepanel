<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\DiscordMessage;
use App\Models\ActivityLog;
use App\Services\Docker\DockerMetrics;

class DashboardController extends Controller
{
    public function index()
    {
        // Ensure default eggs with new variables are loaded
        \App\Models\Egg::seedDefaults();

        $user = auth()->user();
        $services = Service::all();

        if (!$user->isAdmin()) {
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
        if (!auth()->user()->isAdmin()) abort(403);
        session_write_close(); // CRITICAL: Release session for long stats query
        
        $services = Service::all();
        $stats = $this->calculateAdminStats($services);
        
        return response()->json($stats);
    }

    private function calculateAdminStats($services) {
        $running = $services->filter(fn($s) => $s->getStatus() === 'running');
        
        $totalCpu = 0;
        $totalRamMb = 0;

        $dockerServices = $running->filter(fn($s) => $s->type === 'docker');
        if ($dockerServices->isNotEmpty()) {
            $stats = app(DockerMetrics::class)->batchStats($dockerServices->pluck('docker_container_name'));
            foreach ($stats as $stat) {
                $totalCpu += (float) $stat['cpu'];
                $totalRamMb += (float) $stat['ram_mb'];
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
