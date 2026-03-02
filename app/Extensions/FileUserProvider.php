<?php

namespace App\Extensions;

use App\Models\FileUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

class FileUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        return FileUser::findById($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // Not implemented
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) ||
           (count($credentials) === 1 &&
            array_key_exists('password', $credentials))) {
            return null;
        }

        return FileUser::findByEmail($credentials['email'] ?? null);
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return Hash::check($credentials['password'], $user->getAuthPassword());
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        return null;
    }
}
