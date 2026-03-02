<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ActivityLog
{
    public static function all()
    {
        if (!Storage::disk('local')->exists('activity.json')) {
            return collect();
        }
        return collect(json_decode(Storage::disk('local')->get('activity.json'), true));
    }

    public static function log($action, $details = null)
    {
        $logs = static::all()->toArray();
        array_unshift($logs, [
            'id' => uniqid(),
            'user' => Auth::user() ? Auth::user()->name : 'System',
            'action' => $action,
            'details' => $details,
            'ip' => request()->ip(),
            'timestamp' => now()->toDateTimeString()
        ]);
        
        // Keep only last 200 logs
        $logs = array_slice($logs, 0, 200);
        
        Storage::disk('local')->put('activity.json', json_encode($logs, JSON_PRETTY_PRINT));
    }
}
