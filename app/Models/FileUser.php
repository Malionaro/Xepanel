<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Storage;

class FileUser implements Authenticatable
{
    public $id;
    public $name;
    public $email;
    public $password;
    public $role;
    public $two_factor_secret;
    public $two_factor_enabled;
    public $api_keys = [];
    
    // Quotas
    public $max_ram_mb = 4096; // Default 4GB
    public $max_cpu_percent = 200; // Default 200% (2 cores)
    public $max_disk_mb = 10240; // Default 10GB
    public $max_services = 5; // Default 5 services

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public static function all()
    {
        if (!Storage::disk('local')->exists('users.json')) {
            return collect();
        }

        $users = json_decode(Storage::disk('local')->get('users.json'), true);
        return collect($users)->map(fn ($user) => new static($user));
    }

    public static function findByEmail($email)
    {
        return static::all()->where('email', $email)->first();
    }

    public static function findById($id)
    {
        return static::all()->where('id', $id)->first();
    }

    public function save()
    {
        $this->updateFile(function ($users) {
            if (!$this->id) {
                $this->id = uniqid();
                $users->push($this);
            } else {
                $users = $users->map(function ($u) {
                    return $u->id == $this->id ? $this : $u;
                });
            }
            return $users;
        });
    }

    public function delete()
    {
        $this->updateFile(function ($users) {
            return $users->reject(function ($u) {
                return $u->id == $this->id;
            });
        });
    }

    /**
     * Helper to safely update the users.json file with locking.
     */
    protected function updateFile(callable $callback)
    {
        $path = Storage::disk('local')->path('users.json');
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $fp = fopen($path, 'c+');
        if (!$fp) {
            throw new \Exception("Could not open users.json for writing.");
        }

        // Exclusive lock (wait until available)
        flock($fp, LOCK_EX);

        try {
            // Read current data
            clearstatcache(true, $path);
            $size = filesize($path);
            $content = $size > 0 ? fread($fp, $size) : '[]';
            $data = json_decode($content, true) ?: [];
            $users = collect($data)->map(fn ($u) => new static($u));

            // Apply changes via callback
            $updatedUsers = $callback($users);

            // Write back
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($updatedUsers->values()->toArray(), JSON_PRETTY_PRINT));
            fflush($fp);
        } finally {
            // Release lock and close
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

    public function getAuthIdentifierName() { return 'id'; }
    public function getAuthIdentifier() { return $this->id; }
    public function getAuthPasswordName() { return 'password'; }
    public function getAuthPassword() { return $this->password; }
    public function getRememberToken() { return null; }
    public function setRememberToken($value) {}
    public function getRememberTokenName() { return null; }
}
