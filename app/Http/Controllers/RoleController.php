<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Role::availablePermissions();
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'array'
        ]);

        $role = new Role();
        $role->name = $request->name;
        $role->permissions = $request->permissions ?? [];
        $role->save();

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully!');
    }

    public function edit($id)
    {
        $role = Role::find($id);
        if (!$role) return abort(404);
        
        $permissions = Role::availablePermissions();
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::find($id);
        if (!$role) return abort(404);

        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'array'
        ]);

        $role->name = $request->name;
        $role->permissions = $request->permissions ?? [];
        $role->save();

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully!');
    }

    public function destroy($id)
    {
        if ($id === 'admin' || $id === 'user') {
            return redirect()->back()->with('error', 'Cannot delete system roles.');
        }

        $role = Role::find($id);
        if ($role) $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully!');
    }
}
