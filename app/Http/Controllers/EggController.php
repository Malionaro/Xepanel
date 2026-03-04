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
            'icon' => 'nullable|string',
            'docker_image' => 'required_if:type,docker|nullable|string',
            'docker_main_mount' => 'nullable|string',
            'docker_ports' => 'nullable|string',
            'docker_network' => 'nullable|string',
            'start_command' => 'required_if:type,process|nullable|string',
            'stop_command' => 'nullable|string',
            'install_script' => 'nullable|string',
            'default_ram_mb' => 'required|integer|min:128',
            'default_cpu_percent' => 'required|integer|min:10',
            'default_disk_mb' => 'required|integer|min:100',
            'tags' => 'nullable|string',
            'variables' => 'nullable|array',
        ]);

        $egg = new Egg($data);
        $egg->variables = $request->input('variables', []);
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
            'icon' => 'nullable|string',
            'docker_image' => 'required_if:type,docker|nullable|string',
            'docker_main_mount' => 'nullable|string',
            'docker_ports' => 'nullable|string',
            'docker_network' => 'nullable|string',
            'start_command' => 'required_if:type,process|nullable|string',
            'stop_command' => 'nullable|string',
            'install_script' => 'nullable|string',
            'default_ram_mb' => 'required|integer|min:128',
            'default_cpu_percent' => 'required|integer|min:10',
            'default_disk_mb' => 'required|integer|min:100',
            'tags' => 'nullable|string',
            'variables' => 'nullable|array',
        ]);

        foreach ($data as $key => $value) {
            if ($key !== 'variables') $egg->{$key} = $value;
        }
        $egg->variables = $request->input('variables', []);
        $egg->save();

        ActivityLog::log("Updated Egg", "Egg: {$egg->name}");

        return redirect()->route('eggs.index')->with('status', 'Egg updated successfully!');
    }

    public function clone($id)
    {
        if (Auth::user()->role !== 'admin') abort(403);
        $egg = Egg::find($id);
        if (!$egg) abort(404);

        $newEgg = new Egg((array)$egg);
        $newEgg->id = null; // Force new ID
        $newEgg->name .= ' (Copy)';
        $newEgg->save();

        ActivityLog::log("Cloned Egg", "Original: {$egg->name}");

        return redirect()->route('eggs.index')->with('status', "Template '{$egg->name}' cloned successfully!");
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

    public function export($id)
    {
        if (Auth::user()->role !== 'admin') abort(403);
        $egg = Egg::find($id);
        if (!$egg) abort(404);

        $filename = "egg_template_" . \Str::slug($egg->name) . ".json";
        ActivityLog::log("Exported Egg", "Egg: {$egg->name}");

        return response()->streamDownload(function () use ($egg) {
            echo json_encode($egg, JSON_PRETTY_PRINT);
        }, $filename, ['Content-Type' => 'application/json']);
    }

    public function import(Request $request)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $request->validate([
            'import_file' => 'required|file|mimetypes:application/json,text/plain',
        ]);

        $fileContent = file_get_contents($request->file('import_file')->getRealPath());
        $data = json_decode($fileContent, true);

        if (!$data || !isset($data['name'], $data['type'])) {
            return back()->withErrors(['error' => 'Invalid Egg template file. Missing name or type.']);
        }

        // Generate a new ID to avoid collisions
        $data['id'] = uniqid();
        $egg = new Egg($data);
        $egg->save();

        ActivityLog::log("Imported Egg", "Egg: {$egg->name}");

        return redirect()->route('eggs.index')->with('status', "Template '{$egg->name}' imported successfully!");
    }
}
