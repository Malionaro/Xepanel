<?php

namespace App\Services\Docker;

use Symfony\Component\Process\Process;

class DockerEnvironment
{
    public function isAvailable(): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return $this->commandWorks(['docker', 'version', '--format', '{{.Server.Version}}']);
        }

        $socket = env('DOCKER_HOST_SOCKET', '/var/run/docker.sock');

        if (! file_exists($socket)) {
            return false;
        }

        return $this->dockerSocketResponds($socket);
    }

    public function assertAvailable(): void
    {
        if ($this->isAvailable()) {
            return;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            throw new DockerUnavailableException(
                'Docker ist auf diesem Windows-System nicht verfügbar. Installiere Docker Desktop und starte das Panel danach mit Docker Compose, oder verwende nur Prozess-Services.'
            );
        }

        throw new DockerUnavailableException(
            'Docker ist nicht erreichbar. Prüfe, ob /var/run/docker.sock existiert und der Panel-Prozess Zugriff darauf hat.'
        );
    }

    private function commandWorks(array $command): bool
    {
        try {
            $process = new Process($command, base_path(), null, null, 5);
            $process->run();

            return $process->isSuccessful();
        } catch (\Throwable) {
            return false;
        }
    }

    private function dockerSocketResponds(string $socket): bool
    {
        $fp = @stream_socket_client('unix://'.$socket, $errno, $errstr, 2);
        if (! $fp) {
            return false;
        }

        stream_set_timeout($fp, 2);
        fwrite($fp, "GET /_ping HTTP/1.1\r\nHost: localhost\r\nConnection: close\r\n\r\n");
        $response = stream_get_contents($fp);
        fclose($fp);

        return is_string($response) && str_contains($response, '200 OK') && str_contains($response, 'OK');
    }
}
