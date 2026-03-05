<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

class Egg
{
    public $id;
    public $name;
    public $description;
    public $type; // 'process' or 'docker'
    public $docker_image;
    public $docker_main_mount;
    public $docker_ports;
    public $docker_network;
    public $env_vars = [];
    public $variables = []; // New: Detailed variables for UI
    public $start_command;
    public $stop_command;
    public $tags;
    
    // New Extended Fields
    public $icon = 'box';
    public $install_script = '';
    public $default_ram_mb = 1024;
    public $default_cpu_percent = 100;
    public $default_disk_mb = 5120;

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public static function all()
    {
        $files = Storage::disk('local')->files('eggs');
        return collect($files)->filter(fn($f) => str_ends_with($f, '.json'))
            ->map(fn($f) => new static(json_decode(Storage::disk('local')->get($f), true)));
    }

    public static function find($id)
    {
        $path = "eggs/{$id}.json";
        if (!Storage::disk('local')->exists($path)) return null;
        return new static(json_decode(Storage::disk('local')->get($path), true));
    }

    public function save()
    {
        if (!$this->id) {
            $this->id = uniqid();
        }
        Storage::disk('local')->put("eggs/{$this->id}.json", json_encode($this, JSON_PRETTY_PRINT));
    }

    public function delete()
    {
        Storage::disk('local')->delete("eggs/{$this->id}.json");
    }

    public static function seedDefaults()
    {
        $defaults = [
            [
                'name' => 'Minecraft Server',
                'description' => 'A high-performance Minecraft server (Paper/Spigot).',
                'type' => 'docker',
                'icon' => 'gamepad-2',
                'docker_image' => 'itzg/minecraft-server:latest',
                'docker_main_mount' => '/data',
                'docker_ports' => '25565:25565',
                'docker_network' => 'bridge',
                'env_vars' => ['EULA' => 'TRUE'],
                'default_ram_mb' => 2048,
                'variables' => [
                    ['key' => 'MEMORY', 'name' => 'Memory (RAM)', 'description' => 'Amount of RAM for the server (e.g. 2G, 4G)', 'default' => '2G'],
                    ['key' => 'TYPE', 'name' => 'Server Type', 'description' => 'PAPER, SPIGOT, FABRIC, VANILLA', 'default' => 'PAPER'],
                    ['key' => 'VERSION', 'name' => 'Minecraft Version', 'description' => 'LATEST or a specific version like 1.20.1', 'default' => 'LATEST']
                ],
                'tags' => 'Minecraft, Gaming'
            ],
            [
                'name' => 'Discord Bot (Node.js)',
                'description' => 'Node.js environment for Discord bots with auto-npm install.',
                'type' => 'docker',
                'icon' => 'message-square',
                'docker_image' => 'node:20-slim',
                'docker_main_mount' => '/app',
                'docker_network' => 'bridge',
                'default_ram_mb' => 512,
                'variables' => [
                    ['key' => 'BOT_JS', 'name' => 'Main JS File', 'description' => 'The file to run (e.g. index.js, bot.js)', 'default' => 'index.js'],
                    ['key' => 'AUTO_INSTALL', 'name' => 'Auto NPM Install', 'description' => 'TRUE to run npm install on start', 'default' => 'TRUE']
                ],
                'start_command' => 'sh -c "if [ \"${AUTO_INSTALL}\" = \"TRUE\" ] && [ -f package.json ]; then npm install; fi && node ${BOT_JS}"',
                'tags' => 'NodeJS, Discord, Bot'
            ],
            [
                'name' => 'FiveM Server (Linux)',
                'description' => 'CitizenFX server for GTA V Roleplay.',
                'type' => 'docker',
                'icon' => 'car',
                'docker_image' => 'spritsail/fivem',
                'docker_main_mount' => '/config',
                'docker_ports' => '30120:30120',
                'docker_network' => 'bridge',
                'default_ram_mb' => 4096,
                'variables' => [
                    ['key' => 'LICENSE_KEY', 'name' => 'FiveM License Key', 'description' => 'Get one at keymaster.fivem.net', 'default' => ''],
                    ['key' => 'SV_HOSTNAME', 'name' => 'Server Name', 'description' => 'Hostname displayed in the browser', 'default' => 'New Roleplay Server']
                ],
                'tags' => 'FiveM, GTA, Roleplay'
            ],
            [
                'name' => 'BungeeCord Proxy',
                'description' => 'Lightweight Minecraft proxy to link multiple servers.',
                'type' => 'docker',
                'icon' => 'network',
                'docker_image' => 'itzg/bungeecord',
                'docker_main_mount' => '/server',
                'docker_ports' => '25577:25577',
                'docker_network' => 'bridge',
                'default_ram_mb' => 1024,
                'variables' => [
                    ['key' => 'BUNGEE_VERSION', 'name' => 'Bungee Version', 'description' => 'LATEST or specific version', 'default' => 'LATEST']
                ],
                'tags' => 'Minecraft, Proxy'
            ],
            [
                'name' => 'Webserver (Nginx)',
                'description' => 'Highly efficient Nginx server for static HTML/JS websites.',
                'type' => 'docker',
                'icon' => 'globe',
                'docker_image' => 'nginx:alpine',
                'docker_main_mount' => '/usr/share/nginx/html',
                'docker_ports' => '80:80',
                'docker_network' => 'bridge',
                'default_ram_mb' => 256,
                'tags' => 'Web, Nginx, HTML'
            ],
            [
                'name' => 'PHP Web Application',
                'description' => 'Apache with PHP 8.2 for dynamic web applications.',
                'type' => 'docker',
                'icon' => 'code-2',
                'docker_image' => 'php:8.2-apache',
                'docker_main_mount' => '/var/www/html',
                'docker_ports' => '80:80',
                'docker_network' => 'bridge',
                'default_ram_mb' => 1024,
                'tags' => 'PHP, Apache, Web'
            ],
            [
                'name' => 'Python Application',
                'description' => 'Generic Python environment with auto-pip support.',
                'type' => 'docker',
                'icon' => 'terminal',
                'docker_image' => 'python:3.11-slim',
                'docker_main_mount' => '/app',
                'docker_network' => 'bridge',
                'default_ram_mb' => 1024,
                'variables' => [
                    ['key' => 'SCRIPT_FILE', 'name' => 'Main Script', 'description' => 'The file to run', 'default' => 'main.py'],
                    ['key' => 'AUTO_PIP', 'name' => 'Auto Pip Install', 'description' => 'TRUE to install requirements.txt automatically', 'default' => 'TRUE']
                ],
                'start_command' => 'sh -c "if [ \"${AUTO_PIP}\" = \"TRUE\" ] && [ -f requirements.txt ]; then pip install -r requirements.txt; fi && python ${SCRIPT_FILE}"',
                'tags' => 'Python, App'
            ]
        ];

        foreach ($defaults as $data) {
            $egg = collect(static::all())->firstWhere('name', $data['name']);
            if (!$egg) {
                (new static($data))->save();
            } else {
                foreach ($data as $key => $value) { $egg->{$key} = $value; }
                $egg->save();
            }
        }
    }
}
