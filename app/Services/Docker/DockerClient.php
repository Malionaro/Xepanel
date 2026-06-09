<?php

namespace App\Services\Docker;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DockerClient
{
    public function __construct(private readonly DockerEnvironment $environment) {}

    public function request(string $method, string $endpoint, array $data = [], int $timeout = 30): ?array
    {
        $this->environment->assertAvailable();

        $socket = env('DOCKER_HOST_SOCKET', '/var/run/docker.sock');
        $url = 'http://localhost'.$endpoint;

        try {
            $client = Http::withOptions([
                'curl' => [
                    CURLOPT_UNIX_SOCKET_PATH => $socket,
                ],
            ])->timeout($timeout);

            $response = match (strtoupper($method)) {
                'GET' => $client->get($url, $data),
                'POST' => $client->post($url, $data),
                'DELETE' => $client->delete($url, $data),
                default => null,
            };

            if (! $response) {
                return null;
            }

            return [
                'status' => $response->status(),
                'json' => $response->json(),
                'body' => $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('Docker API error: '.$e->getMessage());

            return null;
        }
    }

    public function attachStdin(string $containerName, string $command): bool
    {
        $this->environment->assertAvailable();

        $socket = env('DOCKER_HOST_SOCKET', '/var/run/docker.sock');
        $fp = @stream_socket_client('unix://'.$socket, $errNo, $errStr, 5);

        if (! $fp) {
            Log::error("Docker attach failed for {$containerName}: {$errStr}", ['code' => $errNo]);

            return false;
        }

        $containerId = urlencode($containerName);
        $request = "POST /v1.41/containers/{$containerId}/attach?stdin=1&stream=1 HTTP/1.1\r\n";
        $request .= "Host: localhost\r\n";
        $request .= "Connection: Upgrade\r\n";
        $request .= "Upgrade: tcp\r\n\r\n";

        fwrite($fp, $request);
        usleep(50000);

        $head = '';
        stream_set_timeout($fp, 1);
        while (! feof($fp)) {
            $char = fgetc($fp);
            if ($char === false) {
                break;
            }
            $head .= $char;
            if (str_ends_with($head, "\r\n\r\n")) {
                break;
            }
        }

        fwrite($fp, $command."\n");
        fclose($fp);

        return true;
    }
}
