<?php

namespace App\Http\Controllers;

use App\Models\FileUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('user.account', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $existing = FileUser::findByEmail($data['email']);
        if ($existing && $existing->id !== $user->id) {
            return back()->withErrors(['email' => 'Email already in use by another account.'])->withInput();
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        
        $user->save();

        return redirect()->route('user.account')->with('status', 'Account updated successfully.');
    }
}
