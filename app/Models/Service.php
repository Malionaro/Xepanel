<?php

namespace App\Models;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class Service
{
    public $id;
    public $egg_id;
    public $type = 'process';
    public $name;
    public $working_dir;
    public $start_command;
    public $stop_command;
    public $restart_command;
    public $env_vars = [];
    public $status = 'stopped';
    public $pid = null;
    public $auto_restart = false;
    public $schedules = [];
    public $crash_logs = [];
    public $webhook_url = null;
    public $allowed_users = [];
    public $tags = [];
    public $installer_script = null;

    // Docker specific fields
    public $docker_image;
    public $docker_ports = [];
    public $docker_volumes = [];
    public $docker_main_mount = '/app';
    public $docker_container_name;
    public $docker_network = 'bridge';

    protected static $statusCache = [];

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }
        if (!$this->docker_container_name && $this->id) {
            $this->docker_container_name = 'service-' . $this->id;
        }
    }

    public static function all()
    {
        $files = Storage::disk('local')->files('services');
        return collect($files)->filter(fn($f) => str_ends_with($f, '.json'))
            ->map(fn($f) => new static(json_decode(Storage::disk('local')->get($f), true)));
    }

    public static function find($id)
    {
        $path = "services/{$id}.json";
        if (!Storage::disk('local')->exists($path)) return null;
        return new static(json_decode(Storage::disk('local')->get($path), true));
    }

    public function save()
    {
        if (!$this->id) {
            $this->id = uniqid();
            if (!$this->docker_container_name) {
                $this->docker_container_name = 'service-' . $this->id;
            }
        }
        Storage::disk('local')->put("services/{$this->id}.json", json_encode($this, JSON_PRETTY_PRINT));
        
        if ($this->type === 'process' && $this->working_dir && !is_dir($this->working_dir)) {
            @mkdir($this->working_dir, 0775, true);
        } elseif ($this->type === 'docker') {
            $basePath = Setting::get('docker_base_path', storage_path('app/docker'));
            $dataDir = $basePath . '/' . $this->id;
            if (!is_dir($dataDir)) {
                @mkdir($dataDir, 0775, true);
            }
        }
    }

    public function delete()
    {
        if ($this->type === 'docker') {
            shell_exec("docker rm -f " . escapeshellarg($this->docker_container_name));
            $basePath = Setting::get('docker_base_path', storage_path('app/docker'));
            $dataDir = $basePath . '/' . $this->id;
            if (is_dir($dataDir)) { shell_exec("rm -rf " . escapeshellarg($dataDir)); }
        } else {
            if ($this->working_dir && is_dir($this->working_dir)) { shell_exec("rm -rf " . escapeshellarg($this->working_dir)); }
        }
        Storage::disk('local')->delete("services/{$this->id}.json");
        $logPath = storage_path("logs/services/{$this->id}.log");
        if (file_exists($logPath)) { @unlink($logPath); }
    }

    public function getStatus()
    {
        $cacheKey = $this->id;
        if (isset(static::$statusCache[$cacheKey]) && (time() - static::$statusCache[$cacheKey]['time'] < 2)) {
            return static::$statusCache[$cacheKey]['status'];
        }

        $oldStatus = $this->status;
        $currentStatus = 'stopped';

        if ($this->type === 'docker') {
            $containerName = escapeshellarg($this->docker_container_name);
            $inspect = shell_exec("timeout 2 docker inspect -f '{{.State.Running}}' {$containerName} 2>/dev/null");
            $currentStatus = trim($inspect) === 'true' ? 'running' : 'stopped';
        } else {
            if ($this->pid) {
                $processRunning = @posix_kill($this->pid, 0);
                if ($processRunning) {
                    $currentStatus = 'running';
                } else {
                    $this->pid = null;
                    $currentStatus = 'stopped';
                }
            }
        }

        if ($oldStatus === 'running' && $currentStatus === 'stopped') {
            $this->status = 'stopped';
            $this->save();
            $this->handleCrash();
        }

        static::$statusCache[$cacheKey] = ['status' => $currentStatus, 'time' => time()];
        return $currentStatus;
    }

    protected function handleCrash()
    {
        $logPath = storage_path("logs/services/{$this->id}.log");
        $logSnippet = file_exists($logPath) ? shell_exec("tail -n 50 " . escapeshellarg($logPath)) : "No log found.";
        $analysis = $this->performCrashAnalysis($logSnippet);
        $this->crash_logs[] = [
            'id' => uniqid(),
            'timestamp' => now()->toDateTimeString(),
            'log_snippet' => $logSnippet,
            'analysis' => $analysis['message'],
            'suggestion' => $analysis['suggestion'],
            'type' => $analysis['type']
        ];
        if (count($this->crash_logs) > 10) { array_shift($this->crash_logs); }
        $this->save();
        \App\Models\ActivityLog::log("CRASH DETECTED", "Service: {$this->name}");
        if ($this->auto_restart) { $this->start(); }
    }

    protected function performCrashAnalysis($log)
    {
        $patterns = [
            'out_of_memory' => ['regex' => '/OutOfMemoryError|killed|OOM/i', 'message' => 'Service was killed due to Out-of-Memory (OOM).', 'suggestion' => 'Increase the RAM limit.'],
            'port_in_use' => ['regex' => '/Address already in use|EADDRINUSE/i', 'message' => 'Port already occupied.', 'suggestion' => 'Change the port settings.'],
            'eula_missing' => ['regex' => '/eula=false|accept the EULA/i', 'message' => 'EULA not accepted.', 'suggestion' => 'Set EULA=TRUE.'],
            'permission_denied' => ['regex' => '/Permission denied|EACCES/i', 'message' => 'Permission error.', 'suggestion' => 'Check file permissions.'],
        ];
        foreach ($patterns as $type => $data) {
            if (preg_match($data['regex'], $log)) { return array_merge($data, ['type' => $type]); }
        }
        return ['type' => 'unknown', 'message' => 'Unknown error.', 'suggestion' => 'Check logs.'];
    }

    public function start()
    {
        if ($this->getStatus() === 'running') return;
        if ($this->type === 'docker' && !empty($this->docker_ports)) {
            foreach ($this->docker_ports as $portMapping) {
                $hostPort = explode(':', $portMapping)[0];
                if ($this->isPortTaken($hostPort)) { throw new \Exception("Port {$hostPort} is already in use."); }
            }
        }
        if ($this->type === 'docker') { $this->startDocker(); } else { $this->startProcess(); }
    }

    public function isPortTaken($port)
    {
        $check = shell_exec("ss -tuln | grep -q ':" . escapeshellarg($port) . " ' && echo 'taken'");
        return trim($check) === 'taken';
    }

    protected function startProcess()
    {
        $logPath = storage_path("logs/services/{$this->id}.log");
        $cwd = escapeshellarg($this->working_dir);
        $scriptPath = storage_path("app/run_{$this->id}.sh");

        // Create a dedicated start script
        $scriptContent = "#!/bin/bash\n";
        $scriptContent .= "cd {$cwd}\n";
        $scriptContent .= "nohup {$this->start_command} >> " . escapeshellarg($logPath) . " 2>&1 & echo $! > " . escapeshellarg($scriptPath . ".pid") . "\n";
        
        file_put_contents($scriptPath, $scriptContent);
        chmod($scriptPath, 0755);

        // Execute the script and immediately disconnect
        exec("nohup {$scriptPath} > /dev/null 2>&1 &");
        
        // Wait a tiny bit for the PID file
        for ($i = 0; $i < 10; $i++) {
            if (file_exists($scriptPath . ".pid")) {
                $this->pid = (int) trim(file_get_contents($scriptPath . ".pid"));
                @unlink($scriptPath . ".pid");
                break;
            }
            usleep(10000);
        }

        $this->status = 'running';
        $this->save();
        @unlink($scriptPath);
    }

    protected function startDocker()
    {
        $containerName = escapeshellarg($this->docker_container_name);
        $image = escapeshellarg($this->docker_image);
        $logPath = storage_path("logs/services/{$this->id}.log");
        $scriptPath = storage_path("app/start_docker_{$this->id}.sh");

        $ports = ""; foreach ($this->docker_ports as $port) { $ports .= "-p " . escapeshellarg($port) . " "; }
        $basePath = Setting::get('docker_base_path', storage_path('app/docker'));
        $hostDataDir = $basePath . '/' . $this->id;
        $volumes = "-v " . escapeshellarg($hostDataDir . ":" . ($this->docker_main_mount ?: '/app')) . " ";
        foreach ($this->docker_volumes as $volume) { $volumes .= "-v " . escapeshellarg($volume) . " "; }
        $envs = ""; 
        $envVars = $this->env_vars ?? [];

        // Auto-accept EULA for Minecraft servers if image matches
        if (str_contains(strtolower($this->docker_image), 'minecraft') && !isset($envVars['EULA'])) {
            $envVars['EULA'] = 'TRUE';
        }

        foreach ($envVars as $key => $value) { 
            $envs .= "-e " . escapeshellarg("{$key}={$value}") . " "; 
        }

        // Build the full background script
        $fullCmd = "#!/bin/bash\n";
        $fullCmd .= "docker rm -f {$containerName} > /dev/null 2>&1\n";
        $fullCmd .= "docker pull {$image} >> " . escapeshellarg($logPath) . " 2>&1\n";
        $fullCmd .= "docker run -d --name {$containerName} {$ports} {$volumes} {$envs} --network " . escapeshellarg($this->docker_network) . " {$image} {$this->start_command} >> " . escapeshellarg($logPath) . " 2>&1\n";

        file_put_contents($scriptPath, $fullCmd);        chmod($scriptPath, 0755);

        // Execute and forget
        exec("nohup {$scriptPath} > /dev/null 2>&1 &");

        $this->status = 'running';
        $this->save();
        // The script will be deleted by the system or next run, we leave it for a second to ensure it starts
    }

    public function stop()
    {
        if ($this->getStatus() === 'stopped') return;
        if ($this->type === 'docker') {
            exec("nohup docker stop " . escapeshellarg($this->docker_container_name) . " > /dev/null 2>&1 &");
        } else {
            if ($this->stop_command) {
                exec("nohup bash -c 'cd " . escapeshellarg($this->working_dir) . " && {$this->stop_command}' > /dev/null 2>&1 &");
            } elseif ($this->pid) {
                posix_kill($this->pid, SIGTERM);
            }
            $this->pid = null;
        }
        $this->status = 'stopped';
        $this->save();
    }
}
