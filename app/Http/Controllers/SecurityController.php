<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\FileUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class SecurityController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermission('manage_settings')) abort(403);

        $logs = ActivityLog::all()->filter(function($log) {
            return in_array($log['action'], ['User Logged In', 'Failed Login Attempt', '2FA Challenge', 'User Logged Out']);
        })->take(50);

        $sessions = $this->getActiveSessions();

        return view('settings.security', compact('logs', 'sessions'));
    }

    public function destroySession($id)
    {
        if (!Auth::user()->hasPermission('manage_settings')) abort(403);

        $path = storage_path('framework/sessions/' . $id);
        if (File::exists($path)) {
            File::delete($path);
            ActivityLog::log("Terminated Session", "Session ID: " . $id);
            return back()->with('status', 'Session terminated successfully.');
        }

        return back()->withErrors(['error' => 'Session not found.']);
    }

    private function getActiveSessions()
    {
        $sessionPath = storage_path('framework/sessions');
        if (!File::isDirectory($sessionPath)) return [];

        $files = File::files($sessionPath);
        $sessions = [];
        $users = FileUser::all();

        foreach ($files as $file) {
            if ($file->getFilename() === '.gitignore') continue;

            $data = unserialize(file_get_contents($file->getRealPath()));
            $userId = $data['_admin_user_id'] ?? $data['login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d'] ?? null;
            
            // Laravel 11+ uses a different key for authenticated user ID in sessions
            if (!$userId) {
                foreach ($data as $key => $value) {
                    if (str_starts_with($key, 'login_web_')) {
                        $userId = $value;
                        break;
                    }
                }
            }

            $user = $userId ? $users->firstWhere('id', $userId) : null;

            $sessions[] = [
                'id' => $file->getFilename(),
                'user' => $user ? $user->name : 'Guest / Unknown',
                'last_activity' => $file->getMTime(),
                'ip' => $data['_ip'] ?? 'Unknown',
                'current' => $file->getFilename() === session()->getId()
            ];
        }

        // Sort by last activity
        usort($sessions, fn($a, $b) => $b['last_activity'] - $a['last_activity']);

        return $sessions;
    }
}
