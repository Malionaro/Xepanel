<?php

namespace App\Http\Controllers;

use App\Models\Egg;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EggController extends Controller
{
    public function index()
    {
        if (Auth::user()->role !== 'admin') abort(403);
        
        Egg::seedDefaults(); // Ensure some eggs exist
        $eggs = Egg::all();
        return view('eggs.index', compact('eggs'));
    }

    public function create()
    {
        if (Auth::user()->role !== 'admin') abort(403);
        return view('eggs.create');
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'type' => 'required|in:process,docker',
            'docker_image' => 'required_if:type,docker|nullable|string',
            'docker_main_mount' => 'nullable|string',
            'docker_ports' => 'nullable|string',
            'docker_network' => 'nullable|string',
            'start_command' => 'required_if:type,process|nullable|string',
            'stop_command' => 'nullable|string',
            'tags' => 'nullable|string',
        ]);

        $egg = new Egg($data);
        $egg->save();

        ActivityLog::log("Created Egg", "Egg: {$egg->name}");

        return redirect()->route('eggs.index')->with('status', 'Egg created successfully!');
    }

    public function edit($id)
    {
        if (Auth::user()->role !== 'admin') abort(403);
        $egg = Egg::find($id);
        if (!$egg) abort(404);
        return view('eggs.edit', compact('egg'));
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') abort(403);
        $egg = Egg::find($id);
        if (!$egg) abort(404);

        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'type' => 'required|in:process,docker',
            'docker_image' => 'required_if:type,docker|nullable|string',
            'docker_main_mount' => 'nullable|string',
            'docker_ports' => 'nullable|string',
            'docker_network' => 'nullable|string',
            'start_command' => 'required_if:type,process|nullable|string',
            'stop_command' => 'nullable|string',
            'tags' => 'nullable|string',
        ]);

        foreach ($data as $key => $value) {
            $egg->{$key} = $value;
        }
        $egg->save();

        ActivityLog::log("Updated Egg", "Egg: {$egg->name}");

        return redirect()->route('eggs.index')->with('status', 'Egg updated successfully!');
    }

    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') abort(403);
        $egg = Egg::find($id);
        if ($egg) {
            $egg->delete();
            ActivityLog::log("Deleted Egg", "Egg: {$egg->name}");
        }
        return redirect()->route('eggs.index')->with('status', 'Egg deleted successfully!');
    }
}
