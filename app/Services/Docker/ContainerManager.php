<?php

namespace App\Services\Docker;

use App\Models\Service;
use App\Models\Setting;

class ContainerManager
{
    public function __construct(
        private readonly DockerClient $client,
        private readonly DockerEnvironment $environment,
    ) {}

    public function status(Service $service): string
    {
        if (! $this->environment->isAvailable()) {
            return 'stopped';
        }

        $res = $this->client->request('GET', '/v1.41/containers/'.urlencode($service->docker_container_name).'/json', [], 5);

        if ($res && $res['status'] === 200 && isset($res['json']['State']['Running'])) {
            return $res['json']['State']['Running'] ? 'running' : 'stopped';
        }

        return 'stopped';
    }

    public function start(Service $service): void
    {
        $this->environment->assertAvailable();

        $logPath = storage_path("logs/services/{$service->id}.log");
        file_put_contents($logPath, "[Panel] Starting Docker container via API...\n", FILE_APPEND);

        $image = $service->docker_image;
        $envVars = $service->env_vars ?? [];
        if (str_contains(strtolower($image), 'minecraft') && ! isset($envVars['EULA'])) {
            $envVars['EULA'] = 'TRUE';
        }

        file_put_contents($logPath, "[Panel] Pulling image {$image}...\n", FILE_APPEND);
        $this->client->request('POST', '/v1.41/images/create?fromImage='.urlencode($image), [], 300);

        $createRes = $this->client->request(
            'POST',
            '/v1.41/containers/create?name='.urlencode($service->docker_container_name),
            $this->containerConfig($service, $envVars),
            10
        );

        if ($createRes && ! in_array($createRes['status'], [201, 409], true)) {
            file_put_contents($logPath, '[Error] API Create Response: '.print_r($createRes['json'] ?? $createRes['body'], true)."\n", FILE_APPEND);
        }

        $startRes = $this->client->request('POST', '/v1.41/containers/'.urlencode($service->docker_container_name).'/start', [], 10);
        if ($startRes && ! in_array($startRes['status'], [204, 304], true)) {
            file_put_contents($logPath, '[Error] API Start Response: '.print_r($startRes['json'] ?? $startRes['body'], true)."\n", FILE_APPEND);
        }

        $inspect = $this->client->request('GET', '/v1.41/containers/'.urlencode($service->docker_container_name).'/json', [], 5);
        if ($inspect && $inspect['status'] === 200) {
            $service->pid = substr($inspect['json']['Id'], 0, 12);
        }

        $service->status = 'running';
        $service->save();
    }

    public function stop(Service $service): void
    {
        $this->client->request('POST', '/v1.41/containers/'.urlencode($service->docker_container_name).'/stop?t=10', [], 20);
    }

    public function delete(Service $service): void
    {
        $this->client->request('DELETE', '/v1.41/containers/'.urlencode($service->docker_container_name).'?v=true&force=true', [], 10);
    }

    public function execDetached(Service $service, string $command): void
    {
        $create = $this->client->request('POST', '/v1.41/containers/'.urlencode($service->docker_container_name).'/exec', [
            'AttachStdout' => false,
            'AttachStderr' => false,
            'Tty' => false,
            'Cmd' => ['sh', '-lc', $command],
        ], 5);

        $execId = $create['json']['Id'] ?? null;
        if ($execId) {
            $this->client->request('POST', '/v1.41/exec/'.urlencode($execId).'/start', [
                'Detach' => true,
                'Tty' => false,
            ], 5);
        }
    }

    private function containerConfig(Service $service, array $envVars): array
    {
        $basePath = Setting::get('docker_base_path', storage_path('app/docker'));
        $hostDataDir = $basePath.'/'.$service->id;
        $binds = [$hostDataDir.':'.($service->docker_main_mount ?: '/app')];

        foreach ($service->docker_volumes ?? [] as $volume) {
            $binds[] = $volume;
        }

        $portBindings = [];
        $exposedPorts = [];
        foreach ($service->docker_ports ?? [] as $mapping) {
            $parts = explode(':', $mapping);
            if (count($parts) !== 2) {
                continue;
            }
            $hostPort = $parts[0];
            $containerPort = str_contains($parts[1], '/') ? $parts[1] : $parts[1].'/tcp';
            $portBindings[$containerPort] = [['HostPort' => $hostPort]];
            $exposedPorts[$containerPort] = new \stdClass;
        }

        $config = [
            'Image' => $service->docker_image,
            'Env' => collect($envVars)->map(fn ($value, $key) => "{$key}={$value}")->values()->all(),
            'Tty' => true,
            'OpenStdin' => true,
            'StdinOnce' => false,
            'ExposedPorts' => empty($exposedPorts) ? new \stdClass : $exposedPorts,
            'Labels' => [
                'xepanel.service_id' => (string) $service->id,
                'autoheal' => 'true',
            ],
            'HostConfig' => [
                'Binds' => $binds,
                'PortBindings' => empty($portBindings) ? new \stdClass : $portBindings,
                'NetworkMode' => $service->docker_network ?: 'bridge',
                'RestartPolicy' => ['Name' => 'unless-stopped'],
            ],
        ];

        $cmd = trim((string) $service->start_command);
        if ($cmd !== '' && ! (str_contains(strtolower($service->docker_image), 'minecraft-server') && str_starts_with($cmd, '-'))) {
            $config['Cmd'] = str_getcsv($cmd, ' ');
        }

        return $config;
    }
}
