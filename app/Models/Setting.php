<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

class Setting
{
    public static function all()
    {
        if (!Storage::disk('local')->exists('settings.json')) {
            return static::defaultSettings();
        }
        return json_decode(Storage::disk('local')->get('settings.json'), true) ?: static::defaultSettings();
    }

    public static function get($key, $default = null)
    {
        $settings = static::all();
        return $settings[$key] ?? $default;
    }

    public static function set($key, $value)
    {
        $settings = static::all();
        $settings[$key] = $value;
        Storage::disk('local')->put('settings.json', json_encode($settings, JSON_PRETTY_PRINT));
    }

    public static function setMany(array $data)
    {
        $settings = array_merge(static::all(), $data);
        Storage::disk('local')->put('settings.json', json_encode($settings, JSON_PRETTY_PRINT));
    }

    public static function defaultSettings()
    {
        return [
            'panel_name' => 'FilePanel',
            'max_backup_size_mb' => 500,
            'log_tail_lines' => 100,
            'enable_public_api' => true,
            'max_log_size_mb' => 10,
            'maintenance_mode' => false,
            'docker_base_path' => storage_path('app/docker'),
            'default_timezone' => 'UTC',
            'docker_default_network' => 'bridge',
            'github_repo' => 'Malionaro/Xepanel',
            'ui_theme' => 'system', // system, dark, light
        ];
    }
}
