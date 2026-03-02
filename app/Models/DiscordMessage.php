<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

class DiscordMessage
{
    public static function all()
    {
        if (!Storage::disk('local')->exists('discord/messages.json')) {
            return collect();
        }
        return collect(json_decode(Storage::disk('local')->get('discord/messages.json'), true));
    }

    public static function add(array $data)
    {
        $messages = static::all()->toArray();
        array_unshift($messages, [
            'id' => uniqid(),
            'user' => $data['username'] ?? 'Unknown',
            'avatar' => $data['avatar_url'] ?? null,
            'content' => $data['content'] ?? '',
            'timestamp' => now()->toDateTimeString(),
            'attachments' => $data['attachments'] ?? []
        ]);
        
        // Keep only last 50 messages
        $messages = array_slice($messages, 0, 50);
        
        Storage::disk('local')->put('discord/messages.json', json_encode($messages, JSON_PRETTY_PRINT));
    }
}
