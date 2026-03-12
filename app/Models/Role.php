<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

class Role
{
    public $id;
    public $name;
    public $permissions = [];

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public static function all()
    {
        if (!Storage::disk('local')->exists('roles.json')) {
            // Default roles
            $defaults = [
                [
                    'id' => 'admin',
                    'name' => 'Administrator',
                    'permissions' => static::availablePermissions()
                ],
                [
                    'id' => 'user',
                    'name' => 'User',
                    'permissions' => ['view_services', 'control_services']
                ]
            ];
            Storage::disk('local')->put('roles.json', json_encode($defaults, JSON_PRETTY_PRINT));
            return collect($defaults)->map(fn ($r) => new static($r));
        }

        $roles = json_decode(Storage::disk('local')->get('roles.json'), true);
        return collect($roles)->map(fn ($r) => new static($r));
    }

    public static function find($id)
    {
        return static::all()->where('id', $id)->first();
    }

    public function save()
    {
        $roles = static::all();
        if (!$this->id) {
            $this->id = strtolower(str_replace(' ', '_', $this->name));
            $roles->push($this);
        } else {
            $roles = $roles->map(function ($r) {
                return $r->id == $this->id ? $this : $r;
            });
        }
        Storage::disk('local')->put('roles.json', json_encode($roles->values()->toArray(), JSON_PRETTY_PRINT));
    }

    public function delete()
    {
        $roles = static::all()->reject(fn ($r) => $r->id == $this->id);
        Storage::disk('local')->put('roles.json', json_encode($roles->values()->toArray(), JSON_PRETTY_PRINT));
    }

    public static function availablePermissions()
    {
        return [
            'view_services',
            'create_services',
            'edit_services',
            'delete_services',
            'control_services',
            'manage_users',
            'manage_settings',
            'manage_roles',
            'view_logs',
            'manage_eggs',
            'manage_network'
        ];
    }
}
