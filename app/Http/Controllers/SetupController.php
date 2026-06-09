<?php

namespace App\Http\Controllers;

use App\Models\FileUser;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SetupController extends Controller
{
    public function show()
    {
        if (FileUser::all()->isNotEmpty()) {
            return redirect()->route('login');
        }

        Role::all();

        return view('setup.index');
    }

    public function store(Request $request)
    {
        if (FileUser::all()->isNotEmpty()) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ]);

        Role::all();

        $user = new FileUser([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'role' => 'admin',
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'api_keys' => [],
            'max_ram_mb' => 999999,
            'max_cpu_percent' => 999999,
            'max_disk_mb' => 999999,
            'max_services' => 999999,
        ]);
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'Admin account created.');
    }
}
