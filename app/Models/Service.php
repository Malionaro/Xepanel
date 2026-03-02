<?php

namespace App\Models;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class Service
{
    public $id;
    public $egg_id; // Keep track of the egg template
    public $type = 'process'; // 'process' or 'docker'
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
    public $docker_ports = []; // e.g., ["80:80"]
    public $docker_volumes = []; // Additional volumes
    public $docker_main_mount = '/app'; // The main data directory mapping inside the container
    public $docker_container_name;
    public $docker_network = 'bridge';

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
        
        // Ensure working directory or Docker data directory exists
        if ($this->type === 'process' && $this->working_dir && !is_dir($this->working_dir)) {
            mkdir($this->working_dir, 0775, true);
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
            // Stop and remove container
            shell_exec("docker rm -f " . escapeshellarg($this->docker_container_name));
            
            // Remove data directory
            $basePath = Setting::get('docker_base_path', storage_path('app/docker'));
            $dataDir = $basePath . '/' . $this->id;
            if (is_dir($dataDir)) {
                shell_exec("rm -rf " . escapeshellarg($dataDir));
            }
        } else {
            // Remove working directory for process services
            if ($this->working_dir && is_dir($this->working_dir)) {
                shell_exec("rm -rf " . escapeshellarg($this->working_dir));
            }
        }

        // Delete the configuration file
        Storage::disk('local')->delete("services/{$this->id}.json");
        
        // Delete logs
        $logPath = storage_path("logs/services/{$this->id}.log");
        if (file_exists($logPath)) {
            @unlink($logPath);
        }
    }

    public function getStatus()
    {
        if ($this->type === 'docker') {
            $containerName = escapeshellarg($this->docker_container_name);
            $status = trim(shell_exec("docker inspect -f '{{.State.Running}}' {$containerName} 2>/dev/null"));
            return $status === 'true' ? 'running' : 'stopped';
        }

        if (!$this->pid) return 'stopped';
        
        // Use kill -0 to safely check if the process exists without sending a signal
        $processRunning = @posix_kill($this->pid, 0);

        if ($processRunning) {
            return 'running';
        }
        
        // If we reach here, the process died unexpectedly
        $this->pid = null;
        return 'stopped';
    }

    public function start()
    {
        if ($this->getStatus() === 'running') return;

        if ($this->type === 'docker') {
            $this->startDocker();
        } else {
            $this->startProcess();
        }
    }

    protected function startProcess()
    {
        $process = Process::fromShellCommandline($this->start_command, $this->working_dir);
        $process->setEnv($this->env_vars);
        $process->setTimeout(null);
        
        // Use nohup to keep it running
        $command = "nohup {$this->start_command} > " . storage_path("logs/services/{$this->id}.log") . " 2>&1 & echo $!";
        $pid = shell_exec("cd " . escapeshellarg($this->working_dir) . " && " . $command);
        
        $this->pid = (int) trim($pid);
        $this->status = 'running';
        $this->save();
    }

    protected function startDocker()
    {
        $containerName = escapeshellarg($this->docker_container_name);
        $image = escapeshellarg($this->docker_image);
        $logPath = storage_path("logs/services/{$this->id}.log");
        
        // Ensure log directory exists
        if (!is_dir(dirname($logPath))) {
            @mkdir(dirname($logPath), 0775, true);
        }

        $ports = "";
        foreach ($this->docker_ports as $port) {
            $ports .= "-p " . escapeshellarg($port) . " ";
        }

        $basePath = Setting::get('docker_base_path', storage_path('app/docker'));
        $hostDataDir = $basePath . '/' . $this->id;
        
        // Ensure data directory exists
        if (!is_dir($hostDataDir)) {
            @mkdir($hostDataDir, 0775, true);
        }

        $volumes = "-v " . escapeshellarg($hostDataDir . ":" . ($this->docker_main_mount ?: '/app')) . " ";

        foreach ($this->docker_volumes as $volume) {
            $volumes .= "-v " . escapeshellarg($volume) . " ";
        }

        $envs = "";
        $envVars = $this->env_vars ?? [];
        
        // Failsafe for Minecraft EULA
        if (str_contains(strtolower($image), 'minecraft') && !isset($envVars['EULA'])) {
            $envVars['EULA'] = 'TRUE';
        }

        foreach ($envVars as $key => $value) {
            $envs .= "-e " . escapeshellarg("{$key}={$value}") . " ";
        }

        // Parse start_command for ENV variables (e.g., EULA=TRUE MEMORY=2G)
        $finalCommandParts = [];
        if ($this->start_command) {
            $parts = explode(' ', $this->start_command);
            foreach ($parts as $part) {
                if (str_contains($part, '=')) {
                    $envs .= "-e " . escapeshellarg($part) . " ";
                } else {
                    $finalCommandParts[] = $part;
                }
            }
        }

        $finalCommand = implode(' ', array_map('escapeshellarg', array_filter($finalCommandParts)));

        // Create the background command chain
        $fullCmd = "docker rm -f {$containerName} > /dev/null 2>&1; docker pull {$image} && docker run -d --name {$containerName} {$ports} {$volumes} {$envs} --network " . escapeshellarg($this->docker_network) . " {$image}";
        if (trim($finalCommand)) {
            $fullCmd .= " " . $finalCommand;
        }
        
        // Wrap in bash -c and use nohup for background execution
        $bashCmd = "bash -c " . escapeshellarg($fullCmd);
        shell_exec("nohup {$bashCmd} >> " . escapeshellarg($logPath) . " 2>&1 &");

        $this->status = 'running';
        $this->save();
    }

    public function stop()
    {
        if ($this->getStatus() === 'stopped') return;

        if ($this->type === 'docker') {
            shell_exec("docker stop " . escapeshellarg($this->docker_container_name));
        } else {
            if ($this->stop_command) {
                $process = Process::fromShellCommandline($this->stop_command, $this->working_dir);
                $process->run();
            } else {
                posix_kill($this->pid, SIGTERM);
            }
            $this->pid = null;
        }

        $this->status = 'stopped';
        $this->save();
    }
}
