<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Models\ActivityLog;
use Illuminate\Console\Command;
use Cron\CronExpression;

class MonitorServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'panel:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitors services for auto-restarts and executes scheduled tasks.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $services = Service::all();
        $now = new \DateTime('now');

        foreach ($services as $service) {
            // Check for unexpected stops / crashes
            if ($service->status === 'running' && $service->getStatus() === 'stopped') {
                $this->info("Service '{$service->name}' stopped unexpectedly.");
                
                // Capture Crash Log
                $lastLogLines = "Log data not available.";
                if ($service->type === 'docker') {
                    $containerName = escapeshellarg($service->docker_container_name);
                    $lastLogLines = shell_exec("docker logs --tail 50 {$containerName} 2>&1");
                } else {
                    $logPath = storage_path("logs/services/{$service->id}.log");
                    if (file_exists($logPath)) {
                        $lastLogLines = shell_exec("tail -n 50 " . escapeshellarg($logPath));
                    }
                }

                if (!isset($service->crash_logs)) {
                    $service->crash_logs = [];
                }

                array_unshift($service->crash_logs, [
                    'id' => uniqid(),
                    'timestamp' => $now->format('Y-m-d H:i:s'),
                    'log_snippet' => $lastLogLines ?: "No log output captured."
                ]);

                // Keep only last 10 crash logs
                $service->crash_logs = array_slice($service->crash_logs, 0, 10);
                $service->save();

                ActivityLog::log("Service Crashed", "Service: {$service->name}");

                // Alert Discord Webhook if configured
                if (!empty($service->webhook_url)) {
                    try {
                        \Illuminate\Support\Facades\Http::post($service->webhook_url, [
                            'embeds' => [
                                [
                                    'title' => '🚨 Service Crashed',
                                    'description' => "The service **{$service->name}** (" . ($service->type === 'docker' ? 'Docker' : 'Process') . ") has stopped unexpectedly.",
                                    'color' => 16711680, // Red
                                    'fields' => [
                                        [
                                            'name' => 'Auto-Restart',
                                            'value' => $service->auto_restart ? 'Enabled - Attempting restart...' : 'Disabled',
                                        ]
                                    ],
                                    'timestamp' => now()->toIso8601String(),
                                ]
                            ]
                        ]);
                    } catch (\Exception $e) {
                        $this->error("Failed to send webhook for '{$service->name}': " . $e->getMessage());
                    }
                }

                // Auto-Restart
                if ($service->auto_restart) {
                    $this->info("Auto-restarting '{$service->name}'...");
                    $service->start();
                    ActivityLog::log("Auto-Restart triggered", "Service: {$service->name} was restarted after crash.");
                }
            }

            // Check Scheduled Tasks
            if (isset($service->schedules) && is_array($service->schedules)) {
                $needsSave = false;
                foreach ($service->schedules as &$task) {
                    try {
                        $cron = new CronExpression($task['cron']);
                        if ($cron->isDue($now)) {
                            $this->info("Executing scheduled task '{$task['name']}' for service '{$service->name}'...");
                            
                            if ($service->type === 'docker') {
                                $containerName = escapeshellarg($service->docker_container_name);
                                $cmd = "docker exec -d {$containerName} " . $task['command'];
                            } else {
                                $cmd = "cd " . escapeshellarg($service->working_dir) . " && " . $task['command'];
                                $cmd = "nohup {$cmd} > /dev/null 2>&1 &";
                            }
                            
                            shell_exec($cmd);
                            
                            $task['last_run'] = $now->format('Y-m-d H:i:s');
                            $needsSave = true;
                            
                            ActivityLog::log("Executed Task", "Service: {$service->name}, Task: {$task['name']}");
                        }
                    } catch (\Exception $e) {
                        $this->error("Invalid cron expression for task '{$task['name']}' in service '{$service->name}': " . $e->getMessage());
                    }
                }
                
                if ($needsSave) {
                    $service->save();
                }
            }

            // Log Rotation (Process only, Docker handles its own logs)
            if ($service->type === 'process') {
                $logPath = storage_path("logs/services/{$service->id}.log");
                if (file_exists($logPath)) {
                    $maxSizeMB = \App\Models\Setting::get('max_log_size_mb', 10);
                    $maxSizeBytes = $maxSizeMB * 1024 * 1024;
                    
                    if (filesize($logPath) > $maxSizeBytes) {
                        $this->info("Rotating log for '{$service->name}'...");
                        // Keep the last 1000 lines
                        shell_exec("tail -n 1000 " . escapeshellarg($logPath) . " > " . escapeshellarg($logPath . ".tmp") . " && mv " . escapeshellarg($logPath . ".tmp") . " " . escapeshellarg($logPath));
                        
                        // Add a rotation notice
                        $notice = "\n[SYSTEM] Log rotated at " . now()->format('Y-m-d H:i:s') . "\n";
                        file_put_contents($logPath, $notice, FILE_APPEND);
                    }
                }
            }
        }
    }
}
