<?php

namespace App\Services\Docker;

class DockerLogReader
{
    public function __construct(
        private readonly DockerClient $client,
        private readonly DockerEnvironment $environment,
    ) {}

    public function tail(string $containerName, int $lines = 100): ?string
    {
        if (! $this->environment->isAvailable()) {
            return null;
        }

        $res = $this->client->request('GET', '/v1.41/containers/'.urlencode($containerName).'/logs?stdout=1&stderr=1&tail='.$lines, [], 5);

        if (! $res || $res['status'] !== 200) {
            return null;
        }

        return mb_convert_encoding($this->demultiplex($res['body']), 'UTF-8', 'UTF-8');
    }

    private function demultiplex(string $body): string
    {
        if (strlen($body) === 0 || ! in_array(ord($body[0]), [1, 2], true)) {
            return $body;
        }

        $decoded = '';
        $offset = 0;
        $length = strlen($body);

        while ($offset < $length) {
            if ($offset + 8 > $length) {
                break;
            }

            $header = substr($body, $offset, 8);
            $type = ord($header[0]);
            if ($type > 2) {
                return preg_replace('/[^\x20-\x7E\n\r\t]/', '', $body);
            }

            $size = unpack('N', substr($header, 4, 4))[1];
            $offset += 8;
            if ($offset + $size > $length) {
                $size = $length - $offset;
            }
            $decoded .= substr($body, $offset, $size);
            $offset += $size;
        }

        return $decoded;
    }
}
