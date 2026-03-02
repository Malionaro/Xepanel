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
                'docker_image' => 'itzg/minecraft-server:latest',
                'docker_main_mount' => '/data',
                'docker_ports' => '25565:25565',
                'docker_network' => 'bridge',
                'env_vars' => ['EULA' => 'TRUE'],
                'variables' => [
                    ['key' => 'MEMORY', 'name' => 'Memory (RAM)', 'description' => 'Amount of RAM for the server (e.g. 2G, 4G)', 'default' => '2G'],
                    ['key' => 'TYPE', 'name' => 'Server Type', 'description' => 'PAPER, SPIGOT, FABRIC, VANILLA', 'default' => 'PAPER'],
                    ['key' => 'VERSION', 'name' => 'Minecraft Version', 'description' => 'LATEST or a specific version like 1.20.1', 'default' => 'LATEST']
                ],
                'start_command' => '',
                'tags' => 'Minecraft, Gaming'
            ],
            [
                'name' => 'Python Script',
                'description' => 'Runs a Python script and installs requirements.txt.',
                'type' => 'docker',
                'docker_image' => 'python:3.11-slim',
                'docker_main_mount' => '/app',
                'docker_network' => 'bridge',
                'variables' => [
                    ['key' => 'PYTHON_VERSION', 'name' => 'Python Version', 'description' => 'e.g. 3.12-slim, 3.11-slim, 3.10-slim', 'default' => '3.11-slim'],
                    ['key' => 'SCRIPT_FILE', 'name' => 'Main Script', 'description' => 'The file to run', 'default' => 'main.py'],
                    ['key' => 'AUTO_PIP', 'name' => 'Auto Pip Install', 'description' => 'TRUE to install requirements.txt automatically', 'default' => 'TRUE']
                ],
                'start_command' => 'sh -c "if [ \"${AUTO_PIP}\" = \"TRUE\" ] && [ -f requirements.txt ]; then pip install -r requirements.txt; fi && python ${SCRIPT_FILE}"',
                'tags' => 'Python, App'
            ],
            [
                'name' => 'Discord Bot (Python)',
                'description' => 'Discord bot with auto-pip support and pre-installed libraries.',
                'type' => 'docker',
                'docker_image' => 'python:3.11-slim',
                'docker_main_mount' => '/app',
                'docker_network' => 'bridge',
                'variables' => [
                    ['key' => 'PYTHON_VERSION', 'name' => 'Python Version', 'description' => 'e.g. 3.12-slim, 3.11-slim', 'default' => '3.11-slim'],
                    ['key' => 'DISCORD_TOKEN', 'name' => 'Bot Token', 'description' => 'Your Discord Bot Token', 'default' => ''],
                    ['key' => 'BOT_FILE', 'name' => 'Bot File', 'description' => 'The file to run', 'default' => 'bot.py'],
                    ['key' => 'ADDITIONAL_PACKAGES', 'name' => 'Extra Packages', 'description' => 'Space separated list of pip packages', 'default' => 'discord.py requests']
                ],
                'start_command' => 'sh -c "pip install ${ADDITIONAL_PACKAGES} && if [ -f requirements.txt ]; then pip install -r requirements.txt; fi && python ${BOT_FILE}"',
                'tags' => 'Discord, Bot'
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
