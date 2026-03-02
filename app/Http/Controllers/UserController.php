<?php

namespace App\Http\Controllers;

use App\Models\FileUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    private function checkAdmin()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action. Admin role required.');
        }
    }

    public function index()
    {
        $this->checkAdmin();
        $users = FileUser::all();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $this->checkAdmin();
        return view('users.create');
    }

    public function store(Request $request)
    {
        $this->checkAdmin();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,user',
            'max_ram_mb' => 'required|integer|min:128',
            'max_cpu_percent' => 'required|integer|min:10',
            'max_disk_mb' => 'required|integer|min:100',
            'max_services' => 'required|integer|min:1',
        ]);

        if (FileUser::findByEmail($data['email'])) {
            return back()->withErrors(['email' => 'Email already exists.'])->withInput();
        }

        $user = new FileUser();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->role = $data['role'];
        $user->max_ram_mb = (int)$data['max_ram_mb'];
        $user->max_cpu_percent = (int)$data['max_cpu_percent'];
        $user->max_disk_mb = (int)$data['max_disk_mb'];
        $user->max_services = (int)$data['max_services'];
        $user->save();

        return redirect()->route('users.index')->with('status', 'User created successfully.');
    }

    public function edit($id)
    {
        $this->checkAdmin();
        $user = FileUser::findById($id);
        if (!$user) abort(404);
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $this->checkAdmin();
        $user = FileUser::findById($id);
        if (!$user) abort(404);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,user',
            'max_ram_mb' => 'required|integer|min:128',
            'max_cpu_percent' => 'required|integer|min:10',
            'max_disk_mb' => 'required|integer|min:100',
            'max_services' => 'required|integer|min:1',
        ]);

        $existing = FileUser::findByEmail($data['email']);
        if ($existing && $existing->id !== $id) {
            return back()->withErrors(['email' => 'Email already in use by another account.'])->withInput();
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        
        // Prevent admin from removing their own admin role
        if ($user->id == Auth::id() && $data['role'] !== 'admin') {
            return back()->withErrors(['role' => 'You cannot remove your own admin role.']);
        }

        $user->role = $data['role'];
        $user->max_ram_mb = (int)$data['max_ram_mb'];
        $user->max_cpu_percent = (int)$data['max_cpu_percent'];
        $user->max_disk_mb = (int)$data['max_disk_mb'];
        $user->max_services = (int)$data['max_services'];
        $user->save();

        return redirect()->route('users.index')->with('status', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $this->checkAdmin();
        $user = FileUser::findById($id);
        if (!$user) abort(404);

        if ($user->id == Auth::id()) {
            return back()->withErrors(['error' => 'You cannot delete yourself.']);
        }

        $user->delete();
        return redirect()->route('users.index')->with('status', 'User deleted successfully.');
    }
}
