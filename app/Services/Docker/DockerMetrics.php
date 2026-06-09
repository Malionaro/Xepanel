<?php

namespace App\Services\Docker;

class DockerMetrics
{
    public function __construct(
        private readonly DockerClient $client,
        private readonly DockerEnvironment $environment,
    ) {}

    public function stats(string $containerName): array
    {
        if (! $this->environment->isAvailable()) {
            return ['cpu' => 0.0, 'ram_mb' => 0.0, 'ram' => '0.0 MB'];
        }

        $res = $this->client->request('GET', '/v1.41/containers/'.urlencode($containerName).'/stats?stream=false', [], 5);

        if (! $res || $res['status'] !== 200 || ! is_array($res['json'])) {
            return ['cpu' => 0.0, 'ram_mb' => 0.0, 'ram' => '0.0 MB'];
        }

        $data = $res['json'];
        $cpu = $this->cpuPercent($data);
        $ramMb = isset($data['memory_stats']['usage']) ? $data['memory_stats']['usage'] / 1024 / 1024 : 0.0;

        return [
            'cpu' => round($cpu, 1),
            'ram_mb' => round($ramMb, 1),
            'ram' => number_format($ramMb, 1).' MB',
        ];
    }

    public function batchStats(iterable $containerNames): array
    {
        $stats = [];
        foreach ($containerNames as $name) {
            $stats[$name] = $this->stats($name);
        }

        return $stats;
    }

    private function cpuPercent(array $data): float
    {
        $cpuDelta = ($data['cpu_stats']['cpu_usage']['total_usage'] ?? 0) - ($data['precpu_stats']['cpu_usage']['total_usage'] ?? 0);
        $systemDelta = ($data['cpu_stats']['system_cpu_usage'] ?? 0) - ($data['precpu_stats']['system_cpu_usage'] ?? 0);
        $onlineCpus = $data['cpu_stats']['online_cpus'] ?? count($data['cpu_stats']['cpu_usage']['percpu_usage'] ?? []);

        if ($systemDelta <= 0 || $cpuDelta <= 0 || $onlineCpus <= 0) {
            return 0.0;
        }

        return ($cpuDelta / $systemDelta) * $onlineCpus * 100;
    }
}
