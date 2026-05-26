<?php

namespace App\Models;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

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
        if (! $this->docker_container_name && $this->id) {
            $this->docker_container_name = 'service-'.$this->id;
        }
    }

    public static function all()
    {
        $files = Storage::disk('local')->files('services');

        return collect($files)->filter(fn ($f) => str_ends_with($f, '.json'))
            ->map(fn ($f) => new static(json_decode(Storage::disk('local')->get($f), true)));
    }

    public static function find($id)
    {
        $path = "services/{$id}.json";
        if (! Storage::disk('local')->exists($path)) {
            return null;
        }

        return new static(json_decode(Storage::disk('local')->get($path), true));
    }

    public function save()
    {
        if (! $this->id) {
            $this->id = uniqid();
            if (! $this->docker_container_name) {
                $this->docker_container_name = 'service-'.$this->id;
            }
        }
        Storage::disk('local')->put("services/{$this->id}.json", json_encode($this, JSON_PRETTY_PRINT));

        if ($this->type === 'process' && $this->working_dir && ! is_dir($this->working_dir)) {
            @mkdir($this->working_dir, 0775, true);
        } elseif ($this->type === 'docker') {
            $basePath = Setting::get('docker_base_path', storage_path('app/docker'));
            $dataDir = $basePath.'/'.$this->id;
            if (! is_dir($dataDir)) {
                @mkdir($dataDir, 0775, true);
            }
        }
    }

    public function delete()
    {
        if ($this->type === 'docker') {
            $this->dockerApi('DELETE', '/v1.41/containers/'.urlencode($this->docker_container_name).'?v=true&force=true', [], 10);
            $basePath = Setting::get('docker_base_path', storage_path('app/docker'));
            $dataDir = $basePath.'/'.$this->id;
            if (is_dir($dataDir)) {
                File::deleteDirectory($dataDir);
            }
        } else {
            if ($this->working_dir && is_dir($this->working_dir)) {
                File::deleteDirectory($this->working_dir);
            }
        }
        Storage::disk('local')->delete("services/{$this->id}.json");
        $logPath = storage_path("logs/services/{$this->id}.log");
        if (file_exists($logPath)) {
            @unlink($logPath);
        }
    }

    public function dockerApi($method, $endpoint, $data = [], $timeout = 60)
    {
        $url = 'http://localhost'.$endpoint;
        try {
            $client = \Illuminate\Support\Facades\Http::withOptions([
                'curl' => [
                    CURLOPT_UNIX_SOCKET_PATH => '/var/run/docker.sock',
                ],
            ])->timeout($timeout);

            if ($method === 'GET') {
                $response = $client->get($url, $data);
            } elseif ($method === 'POST') {
                $response = $client->post($url, $data);
            } elseif ($method === 'DELETE') {
                $response = $client->delete($url, $data);
            } else {
                return null;
            }

            return ['status' => $response->status(), 'json' => $response->json(), 'body' => $response->body()];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Docker API Error: '.$e->getMessage());

            return null;
        }
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
            $containerName = $this->docker_container_name;
            $res = $this->dockerApi('GET', '/v1.41/containers/'.urlencode($containerName).'/json');
            if ($res && $res['status'] === 200 && isset($res['json']['State']['Running'])) {
                $currentStatus = $res['json']['State']['Running'] ? 'running' : 'stopped';
            }
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
        $logSnippet = file_exists($logPath) ? shell_exec('tail -n 50 '.escapeshellarg($logPath)) : 'No log found.';
        $analysis = $this->performCrashAnalysis($logSnippet);
        $this->crash_logs[] = [
            'id' => uniqid(),
            'timestamp' => now()->toDateTimeString(),
            'log_snippet' => $logSnippet,
            'analysis' => $analysis['message'],
            'suggestion' => $analysis['suggestion'],
            'type' => $analysis['type'],
        ];
        if (count($this->crash_logs) > 10) {
            array_shift($this->crash_logs);
        }
        $this->save();
        \App\Models\ActivityLog::log('CRASH DETECTED', "Service: {$this->name}");
        if ($this->auto_restart) {
            $this->start();
        }
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
            if (preg_match($data['regex'], $log)) {
                return array_merge($data, ['type' => $type]);
            }
        }

        return ['type' => 'unknown', 'message' => 'Unknown error.', 'suggestion' => 'Check logs.'];
    }

    public function start()
    {
        if ($this->getStatus() === 'running') {
            return;
        }
        if ($this->type === 'docker' && ! empty($this->docker_ports)) {
            foreach ($this->docker_ports as $portMapping) {
                $hostPort = explode(':', $portMapping)[0];
                if ($this->isPortTaken($hostPort)) {
                    throw new \Exception("Port {$hostPort} is already in use.");
                }
            }
        }
        if ($this->type === 'docker') {
            $this->startDocker();
        } else {
            $this->startProcess();
        }
    }

    public function isPortTaken($port)
    {
        $port = (int) $port;
        if ($port < 1 || $port > 65535) {
            return true;
        }

        $check = shell_exec("ss -tuln 2>/dev/null | awk '{print $5}' | grep -Eq '(^|:)".$port."$' && echo taken");

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
        $scriptContent .= "nohup {$this->start_command} >> ".escapeshellarg($logPath).' 2>&1 & echo $! > '.escapeshellarg($scriptPath.'.pid')."\n";

        file_put_contents($scriptPath, $scriptContent);
        chmod($scriptPath, 0755);

        // Execute the script and immediately disconnect
        exec('nohup '.escapeshellarg($scriptPath).' > /dev/null 2>&1 &');

        // Wait a tiny bit for the PID file
        for ($i = 0; $i < 10; $i++) {
            if (file_exists($scriptPath.'.pid')) {
                $this->pid = (int) trim(file_get_contents($scriptPath.'.pid'));
                @unlink($scriptPath.'.pid');
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
        $logPath = storage_path("logs/services/{$this->id}.log");
        file_put_contents($logPath, "[Panel] Initiating Docker startup sequence via API...\n", FILE_APPEND);

        $image = $this->docker_image;
        $envVars = $this->env_vars ?? [];
        if (str_contains(strtolower($image), 'minecraft') && ! isset($envVars['EULA'])) {
            $envVars['EULA'] = 'TRUE';
        }

        file_put_contents($logPath, "[Panel] Pulling image {$image} (this may take a while)...\n", FILE_APPEND);
        // Pull the image (Sync, timeout 300s to allow large images to download)
        $this->dockerApi('POST', '/v1.41/images/create?fromImage='.urlencode($image), [], 300);
        file_put_contents($logPath, "[Panel] Image pull request completed.\n", FILE_APPEND);

        $basePath = Setting::get('docker_base_path', storage_path('app/docker'));
        $hostDataDir = $basePath.'/'.$this->id;
        $binds = [$hostDataDir.':'.($this->docker_main_mount ?: '/app')];
        foreach ($this->docker_volumes as $vol) {
            $binds[] = $vol;
        }

        $portBindings = [];
        $exposedPorts = [];
        foreach ($this->docker_ports as $mapping) {
            $parts = explode(':', $mapping);
            if (count($parts) === 2) {
                $hostPort = $parts[0];
                $containerPort = $parts[1];
                if (! str_contains($containerPort, '/')) {
                    $containerPort .= '/tcp';
                }
                $portBindings[$containerPort] = [['HostPort' => $hostPort]];
                $exposedPorts[$containerPort] = new \stdClass;
            }
        }

        $envList = [];
        foreach ($envVars as $k => $v) {
            $envList[] = "$k=$v";
        }

        // Handle Cmd array safely
        $cmd = trim($this->start_command);
        $cmdArray = [];
        if ($cmd) {
            // Note: simple explosion by spaces. For robust quoted argument parsing, a regex or dedicated parser would be used.
            $cmdArray = str_getcsv($cmd, ' ');
        }
        if (str_contains(strtolower($image), 'minecraft-server') && str_starts_with($cmd, '-')) {
            $cmdArray = [];
        }

        $containerConfig = [
            'Image' => $image,
            'Env' => $envList,
            'Tty' => true,
            'OpenStdin' => true,
            'StdinOnce' => false,
            'ExposedPorts' => empty($exposedPorts) ? new \stdClass : $exposedPorts,
            'HostConfig' => [
                'Binds' => $binds,
                'PortBindings' => empty($portBindings) ? new \stdClass : $portBindings,
                'NetworkMode' => $this->docker_network ?: 'bridge',
                'RestartPolicy' => ['Name' => 'unless-stopped'],
            ],
        ];

        if (! empty($cmdArray)) {
            $containerConfig['Cmd'] = $cmdArray;
        }

        file_put_contents($logPath, "[Panel] Creating container {$this->docker_container_name}...\n", FILE_APPEND);
        // Attempt to create the container
        $createRes = $this->dockerApi('POST', '/v1.41/containers/create?name='.urlencode($this->docker_container_name), $containerConfig, 10);
        if ($createRes && $createRes['status'] !== 201 && $createRes['status'] !== 409) {
            file_put_contents($logPath, '[Error] API Create Response: '.print_r($createRes['json'] ?? $createRes['body'], true)."\n", FILE_APPEND);
        }

        file_put_contents($logPath, "[Panel] Starting container...\n", FILE_APPEND);
        $startRes = $this->dockerApi('POST', '/v1.41/containers/'.urlencode($this->docker_container_name).'/start', [], 10);
        if ($startRes && $startRes['status'] !== 204 && $startRes['status'] !== 304) {
            file_put_contents($logPath, '[Error] API Start Response: '.print_r($startRes['json'] ?? $startRes['body'], true)."\n", FILE_APPEND);
        }

        $inspect = $this->dockerApi('GET', '/v1.41/containers/'.urlencode($this->docker_container_name).'/json');
        if ($inspect && $inspect['status'] === 200) {
            $this->pid = substr($inspect['json']['Id'], 0, 12);
        }

        $this->status = 'running';
        $this->save();
        file_put_contents($logPath, "[Panel] Container is now running. PID: {$this->pid}\n", FILE_APPEND);
    }

    public function stop()
    {
        if ($this->getStatus() === 'stopped') {
            return;
        }
        if ($this->type === 'docker') {
            $this->dockerApi('POST', '/v1.41/containers/'.urlencode($this->docker_container_name).'/stop?t=10', [], 20);
        } else {
            if ($this->stop_command) {
                $scriptPath = storage_path("app/stop_{$this->id}.sh");
                $scriptContent = "#!/bin/bash\ncd ".escapeshellarg($this->working_dir)."\n{$this->stop_command}\n";
                file_put_contents($scriptPath, $scriptContent);
                chmod($scriptPath, 0750);
                exec('nohup '.escapeshellarg($scriptPath).' > /dev/null 2>&1 &');
            } elseif ($this->pid) {
                posix_kill($this->pid, SIGTERM);
            }
            $this->pid = null;
        }
        $this->status = 'stopped';
        $this->save();
    }
}
