<?php

namespace App\Services;

class PanelScriptRunner
{
    public function writeExecutableScript(string $path, string $content): void
    {
        $dir = dirname($path);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new \RuntimeException('Could not create script directory.');
        }

        file_put_contents($path, $content);
        chmod($path, 0750);
    }

    public function runInBackground(string $scriptPath): void
    {
        exec('nohup '.escapeshellarg($scriptPath).' > /dev/null 2>&1 &');
    }
}
