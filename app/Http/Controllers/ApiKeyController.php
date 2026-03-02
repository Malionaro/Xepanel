<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('users.api_keys', compact('user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        
        if (!isset($user->api_keys)) {
            $user->api_keys = [];
        }

        $newToken = Str::random(60);

        $user->api_keys[] = [
            'id' => uniqid(),
            'name' => $request->name,
            'token' => 'fp_' . $newToken,
            'created_at' => now()->toDateTimeString(),
        ];
        
        $user->save();

        ActivityLog::log("Generated API Key", "User: {$user->email}, Key Name: {$request->name}");

        return back()->with('status', 'API Key generated successfully! Make sure to copy it now, it will not be shown entirely again.');
    }

    public function destroy(Request $request, $keyId)
    {
        $user = Auth::user();

        if (isset($user->api_keys)) {
            $user->api_keys = array_filter($user->api_keys, function($key) use ($keyId) {
                return $key['id'] !== $keyId;
            });
            $user->api_keys = array_values($user->api_keys);
            $user->save();
            
            ActivityLog::log("Revoked API Key", "User: {$user->email}");
        }

        return back()->with('status', 'API Key revoked!');
    }
}
