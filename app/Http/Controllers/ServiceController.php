<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Egg;
use App\Models\Service;
use App\Services\PanelScriptRunner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $services = Service::all();

        if (! $user->isAdmin() && ! $user->hasPermission('view_services')) {
            $services = $services->filter(function ($service) use ($user) {
                return isset($service->allowed_users) && in_array($user->id, $service->allowed_users);
            });
        }

        return view('services.index', compact('services'));
    }

    private function checkAccess($service)
    {
        $user = Auth::user();
        if ($user->isAdmin() || $user->hasPermission('view_services')) {
            return true;
        }

        if (isset($service->allowed_users) && in_array($user->id, $service->allowed_users)) {
            return true;
        }

        abort(403, 'You do not have permission to access this service.');
    }

    public function create()
    {
        // Only admins can create services
        if (! Auth::user()->isAdmin() && ! Auth::user()->hasPermission('create_services')) {
            abort(403);
        }

        $eggs = Egg::all();
        if (count($eggs) === 0) {
            Egg::seedDefaults();
            $eggs = Egg::all();
        }

        return view('services.create', compact('eggs'));
    }

    public function store(Request $request, PanelScriptRunner $runner)
    {
        if (! Auth::user()->isAdmin() && ! Auth::user()->hasPermission('create_services')) {
            abort(403);
        }

        $data = $request->validate([
            'egg_id' => 'nullable|string',
            'type' => 'required|in:process,docker',
            'name' => 'required|string',
            'working_dir' => 'nullable|string',
            'start_command' => 'nullable|string',
            'stop_command' => 'nullable|string',
            'env_vars' => 'nullable|array',
            'auto_restart' => 'nullable|boolean',
            'webhook_url' => 'nullable|url',
            'tags' => 'nullable|string',
            'installer_script' => 'nullable|string',
            'docker_image' => 'nullable|string',
            'docker_main_mount' => 'nullable|string',
            'docker_ports' => 'nullable|string',
            'docker_volumes' => 'nullable|string',
            'docker_network' => 'nullable|string',
            'docker_container_name' => 'nullable|string',
        ]);

        $egg = null;
        if (! empty($data['egg_id'])) {
            $egg = Egg::find($data['egg_id']);
        }

        $service = new Service($data);
        $service->egg_id = $data['egg_id'] ?? null;

        if ($service->type === 'process' && empty($service->working_dir)) {
            $service->working_dir = storage_path('app/services/'.\Illuminate\Support\Str::slug($service->name).'-'.uniqid());
        }

        // Use Egg defaults if fields are empty
        if ($egg) {
            if (empty($data['start_command'])) {
                $service->start_command = $egg->start_command;
            }
            if (empty($data['stop_command'])) {
                $service->stop_command = $egg->stop_command;
            }
            if (empty($data['docker_image']) && $egg->type === 'docker') {
                $service->docker_image = $egg->docker_image;
            }
            if (empty($data['docker_main_mount']) && $egg->type === 'docker') {
                $service->docker_main_mount = $egg->docker_main_mount;
            }
            if (empty($data['docker_ports']) && $egg->type === 'docker') {
                $data['docker_ports'] = $egg->docker_ports;
            }
            if (empty($data['docker_network']) && $egg->type === 'docker') {
                $service->docker_network = $egg->docker_network;
            }

            // Auto-populate variables from Egg to env_vars
            foreach ($egg->variables ?? [] as $v) {
                if (! isset($service->env_vars[$v['key']])) {
                    $service->env_vars[$v['key']] = $v['default'] ?? '';
                }
            }
        }

        $service->auto_restart = (bool) $request->has('auto_restart');

        if (! empty($data['tags'])) {
            $service->tags = array_map('trim', explode(',', $data['tags']));
        }

        if ($service->type === 'docker') {
            $service->docker_main_mount = $service->docker_main_mount ?? '/app';
            if (! empty($data['docker_ports'])) {
                $service->docker_ports = array_map('trim', explode(',', $data['docker_ports']));
            }
            if (! empty($data['docker_volumes'])) {
                $service->docker_volumes = array_map('trim', explode(',', $data['docker_volumes']));
            }
            $service->docker_network = $service->docker_network ?? 'bridge';
        }

        $service->save();

        // Handle Installation Script from Egg or Manual
        $installScript = $egg->install_script ?? $data['installer_script'] ?? null;
        if ($installScript) {
            $logPath = storage_path("logs/services/{$service->id}.log");
            $cwd = $service->type === 'process' ? escapeshellarg($service->working_dir) : escapeshellarg(storage_path("app/docker/{$service->id}"));

            // Create a temporary script file
            $tmpScript = storage_path("app/temp_install_{$service->id}.sh");
            $runner->writeExecutableScript($tmpScript, "#!/bin/bash\n".$installScript);

            $fullCmd = "cd {$cwd} && (echo '--- STARTING INSTALLATION SCRIPT ---' && ".escapeshellarg($tmpScript).') >> '.escapeshellarg($logPath).' 2>&1 && rm '.escapeshellarg($tmpScript).' &';
            shell_exec($fullCmd);
            ActivityLog::log('Running Installation Script', "Service: {$service->name}");
        }

        ActivityLog::log('Created service', 'Service: '.$service->name);

        return redirect()->route('dashboard')->with('status', 'Service creation initiated!');
    }

    public function show($id)
    {
        $service = Service::find($id);
        if (! $service) {
            abort(404);
        }
        $this->checkAccess($service);

        return view('services.show', compact('service'));
    }

    public function edit($id)
    {
        if (! Auth::user()->isAdmin() && ! Auth::user()->hasPermission('edit_services')) {
            abort(403);
        }
        $service = Service::find($id);
        if (! $service) {
            abort(404);
        }
        $this->checkAccess($service);
        $eggs = \App\Models\Egg::all();

        return view('services.edit', compact('service', 'eggs'));
    }

    public function update(Request $request, $id)
    {
        if (! Auth::user()->isAdmin() && ! Auth::user()->hasPermission('edit_services')) {
            abort(403);
        }
        $service = Service::find($id);
        if (! $service) {
            abort(404);
        }
        $this->checkAccess($service);

        $data = $request->validate([
            'egg_id' => 'nullable|string',
            'type' => 'required|in:process,docker',
            'name' => 'required|string',
            'working_dir' => 'required_if:type,process|string|nullable',
            'start_command' => 'required_if:type,process|string|nullable',
            'stop_command' => 'nullable|string',
            'env_vars' => 'nullable|array',
            'auto_restart' => 'nullable|boolean',
            'webhook_url' => 'nullable|url',
            'tags' => 'nullable|string',
            'installer_script' => 'nullable|string',
            'docker_image' => 'required_if:type,docker|string|nullable',
            'docker_main_mount' => 'nullable|string',
            'docker_ports' => 'nullable|string',
            'docker_volumes' => 'nullable|string',
            'docker_network' => 'nullable|string',
            'docker_container_name' => 'nullable|string',
        ]);

        $service->egg_id = $data['egg_id'] ?? null;
        $service->type = $data['type'];
        $service->name = $data['name'];
        $service->working_dir = $data['working_dir'] ?? null;
        $service->start_command = $data['start_command'] ?? null;
        $service->stop_command = $data['stop_command'] ?? null;
        $service->env_vars = $data['env_vars'] ?? [];
        $service->auto_restart = (bool) $request->has('auto_restart');
        $service->webhook_url = $data['webhook_url'] ?? null;
        $service->installer_script = $data['installer_script'] ?? null;

        if (! empty($data['tags'])) {
            $service->tags = array_map('trim', explode(',', $data['tags']));
        } else {
            $service->tags = [];
        }

        if ($service->type === 'docker') {
            $service->docker_image = $data['docker_image'];
            $service->docker_main_mount = $data['docker_main_mount'] ?? '/app';
            if (! empty($data['docker_ports'])) {
                $service->docker_ports = array_map('trim', explode(',', $data['docker_ports']));
            } else {
                $service->docker_ports = [];
            }
            if (! empty($data['docker_volumes'])) {
                $service->docker_volumes = array_map('trim', explode(',', $data['docker_volumes']));
            } else {
                $service->docker_volumes = [];
            }
            $service->docker_network = $data['docker_network'] ?? 'bridge';
            $service->docker_container_name = $data['docker_container_name'] ?? 'service-'.$service->id;
        }

        $service->save();

        ActivityLog::log('Updated service', 'Service: '.$service->name);

        return redirect()->route('services.show', $service->id)->with('status', 'Service updated successfully!');
    }

    public function start($id)
    {
        if (! Auth::user()->isAdmin() && ! Auth::user()->hasPermission('control_services')) {
            abort(403);
        }
        $service = Service::find($id);
        if (! $service) {
            abort(404);
        }
        $this->checkAccess($service);

        $user = auth()->user();

        // Quota Enforcement (only for non-admins or if you want to limit admins too)
        if (! $user->isAdmin()) {
            $runningServices = Service::all()->filter(function ($s) use ($user) {
                return in_array($user->id, $s->allowed_users ?? []) && $s->getStatus() === 'running';
            });

            $currentRam = 0;
            foreach ($runningServices as $rs) {
                // Try to extract RAM from env vars (MEMORY key)
                $mem = $rs->env_vars['MEMORY'] ?? '512M';
                if (preg_match('/([0-9]+)([MG])/i', $mem, $matches)) {
                    $val = (int) $matches[1];
                    $unit = strtoupper($matches[2]);
                    $currentRam += ($unit === 'G') ? $val * 1024 : $val;
                }
            }

            // Check if starting THIS service exceeds RAM
            $thisMem = $service->env_vars['MEMORY'] ?? '512M';
            $thisVal = 512;
            if (preg_match('/([0-9]+)([MG])/i', $thisMem, $matches)) {
                $val = (int) $matches[1];
                $unit = strtoupper($matches[2]);
                $thisVal = ($unit === 'G') ? $val * 1024 : $val;
            }

            if (($currentRam + $thisVal) > ($user->max_ram_mb ?? 4096)) {
                return back()->withErrors(['error' => 'Quota Exceeded: You do not have enough RAM left to start this service.']);
            }
        }

        try {
            // Perform all logic that needs the session BEFORE this
            session_write_close();
            $service->start();
        } catch (\Exception $e) {
            // If an exception happens, we might need to restart the session to show errors
            @session_start();

            return back()->withErrors(['error' => $e->getMessage()]);
        }

        ActivityLog::log('Started service', 'Service: '.$service->name);

        return back();
    }

    public function stop($id)
    {
        if (! Auth::user()->isAdmin() && ! Auth::user()->hasPermission('control_services')) {
            abort(403);
        }
        $service = Service::find($id);
        if ($service) {
            $this->checkAccess($service);
            session_write_close(); // CRITICAL: Release session lock
            $service->stop();
            ActivityLog::log('Stopped service', 'Service: '.$service->name);
        }

        return back();
    }

    public function startAll()
    {
        $user = Auth::user();
        if (! $user->isAdmin() && ! $user->hasPermission('control_services')) {
            abort(403);
        }
        $services = Service::all();
        if (! $user->isAdmin() && ! $user->hasPermission('view_services')) {
            $services = $services->filter(fn ($s) => isset($s->allowed_users) && in_array($user->id, $s->allowed_users));
        }

        session_write_close();
        $count = 0;
        foreach ($services as $service) {
            if ($service->getStatus() === 'stopped') {
                $service->start();
                $count++;
            }
        }
        ActivityLog::log('Mass Action', "Started {$count} services.");

        return back()->with('status', "Started {$count} services.");
    }

    public function stopAll()
    {
        $user = Auth::user();
        if (! $user->isAdmin() && ! $user->hasPermission('control_services')) {
            abort(403);
        }
        $services = Service::all();
        if (! $user->isAdmin() && ! $user->hasPermission('view_services')) {
            $services = $services->filter(fn ($s) => isset($s->allowed_users) && in_array($user->id, $s->allowed_users));
        }

        session_write_close();
        $count = 0;
        foreach ($services as $service) {
            if ($service->getStatus() === 'running') {
                $service->stop();
                $count++;
            }
        }
        ActivityLog::log('Mass Action', "Stopped {$count} services.");

        return back()->with('status', "Stopped {$count} services.");
    }

    public function destroy($id)
    {
        if (! Auth::user()->isAdmin() && ! Auth::user()->hasPermission('delete_services')) {
            abort(403);
        }
        $service = Service::find($id);
        if ($service) {
            $this->checkAccess($service);
            ActivityLog::log('Deleted service', 'Service: '.$service->name);
            $service->delete();
        }

        return redirect()->route('dashboard');
    }

    public function envs($id)
    {
        if (! Auth::user()->isAdmin() && ! Auth::user()->hasPermission('edit_services')) {
            abort(403);
        }
        $service = Service::find($id);
        if (! $service) {
            abort(404);
        }
        $this->checkAccess($service);

        return view('services.envs', compact('service'));
    }

    public function storeEnv(Request $request, $id)
    {
        if (! Auth::user()->isAdmin() && ! Auth::user()->hasPermission('edit_services')) {
            abort(403);
        }
        $service = Service::find($id);
        if (! $service) {
            abort(404);
        }
        $this->checkAccess($service);

        $request->validate([
            'key' => 'required|string',
            'value' => 'required|string',
        ]);

        $service->env_vars[$request->key] = $request->value;
        $service->save();

        ActivityLog::log('Added ENV variable', "Service: {$service->name}, Key: {$request->key}");

        return back()->with('status', 'Environment variable added!');
    }

    public function destroyEnv(Request $request, $id)
    {
        if (! Auth::user()->isAdmin() && ! Auth::user()->hasPermission('edit_services')) {
            abort(403);
        }
        $service = Service::find($id);
        if (! $service) {
            abort(404);
        }
        $this->checkAccess($service);

        $key = $request->input('key');
        if (isset($service->env_vars[$key])) {
            unset($service->env_vars[$key]);
            $service->save();
            ActivityLog::log('Removed ENV variable', "Service: {$service->name}, Key: {$key}");
        }

        return back()->with('status', 'Environment variable removed!');
    }

    public function export($id)
    {
        $service = Service::find($id);
        if (! $service) {
            abort(404);
        }
        $this->checkAccess($service);

        $data = [
            'name' => $service->name.' (Export)',
            'working_dir' => $service->working_dir,
            'start_command' => $service->start_command,
            'stop_command' => $service->stop_command,
            'restart_command' => $service->restart_command,
            'env_vars' => $service->env_vars,
            'auto_restart' => $service->auto_restart,
            'webhook_url' => $service->webhook_url ?? null,
            'tags' => $service->tags ?? [],
        ];

        $filename = 'service_export_'.\Str::slug($service->name).'.json';

        ActivityLog::log('Exported Service', 'Service: '.$service->name);

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT);
        }, $filename, ['Content-Type' => 'application/json']);
    }

    public function showImport()
    {
        if (! Auth::user()->isAdmin() && ! Auth::user()->hasPermission('create_services')) {
            abort(403);
        }

        return view('services.import');
    }

    public function import(Request $request)
    {
        if (! Auth::user()->isAdmin() && ! Auth::user()->hasPermission('create_services')) {
            abort(403);
        }
        $request->validate([
            'import_file' => 'required|file|mimetypes:application/json,text/plain',
        ]);

        $fileContent = file_get_contents($request->file('import_file')->getRealPath());
        $data = json_decode($fileContent, true);

        if (! $data || ! isset($data['name'], $data['working_dir'], $data['start_command'])) {
            return back()->withErrors(['import_file' => 'Invalid service configuration file.']);
        }

        $service = new Service([
            'type' => $data['type'] ?? 'process',
            'name' => $data['name'],
            'working_dir' => $data['working_dir'] ?? null,
            'start_command' => $data['start_command'] ?? null,
            'stop_command' => $data['stop_command'] ?? null,
            'restart_command' => $data['restart_command'] ?? null,
            'env_vars' => $data['env_vars'] ?? [],
            'auto_restart' => $data['auto_restart'] ?? false,
            'webhook_url' => $data['webhook_url'] ?? null,
            'tags' => $data['tags'] ?? [],
            'docker_image' => $data['docker_image'] ?? null,
            'docker_main_mount' => $data['docker_main_mount'] ?? null,
            'docker_ports' => $data['docker_ports'] ?? [],
            'docker_volumes' => $data['docker_volumes'] ?? [],
            'docker_network' => $data['docker_network'] ?? 'bridge',
            'docker_container_name' => $data['docker_container_name'] ?? null,
        ]);

        $service->save();

        ActivityLog::log('Imported Service', 'Service: '.$service->name);

        return redirect()->route('dashboard')->with('status', 'Service imported successfully!');
    }

    public function crashLogs($id)
    {
        $service = Service::find($id);
        if (! $service) {
            abort(404);
        }
        $this->checkAccess($service);

        return view('services.crash_logs', compact('service'));
    }

    public function deleteCrashLog($id, $logId)
    {
        $service = Service::find($id);
        if (! $service) {
            abort(404);
        }
        $this->checkAccess($service);

        if (isset($service->crash_logs)) {
            $service->crash_logs = array_filter($service->crash_logs, function ($log) use ($logId) {
                return $log['id'] !== $logId;
            });
            $service->crash_logs = array_values($service->crash_logs);
            $service->save();
        }

        return back()->with('status', 'Crash log deleted.');
    }

    public function systemd($id)
    {
        $service = Service::find($id);
        if (! $service) {
            abort(404);
        }
        $this->checkAccess($service);

        $currentUser = get_current_user();

        $envVars = '';
        if (! empty($service->env_vars)) {
            foreach ($service->env_vars as $key => $value) {
                $envVars .= "Environment=\"{$key}={$value}\"\n";
            }
        }

        $unitFile = "[Unit]
Description={$service->name} (Managed by FilePanel)
After=network.target

[Service]
Type=simple
User={$currentUser}
WorkingDirectory={$service->working_dir}
ExecStart={$service->start_command}
".($service->stop_command ? "ExecStop={$service->stop_command}\n" : '')."{$envVars}Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target";

        return view('services.systemd', compact('service', 'unitFile'));
    }

    public function clone($id)
    {
        if (! Auth::user()->isAdmin() && ! Auth::user()->hasPermission('create_services')) {
            abort(403);
        }

        $service = Service::find($id);
        if (! $service) {
            abort(404);
        }

        $newService = new Service([
            'name' => $service->name.' (Copy)',
            'working_dir' => $service->working_dir,
            'start_command' => $service->start_command,
            'stop_command' => $service->stop_command,
            'restart_command' => $service->restart_command,
            'env_vars' => $service->env_vars ?? [],
            'auto_restart' => $service->auto_restart ?? false,
            'webhook_url' => $service->webhook_url ?? null,
            'tags' => $service->tags ?? [],
            'allowed_users' => $service->allowed_users ?? [],
        ]);

        $newService->save();

        ActivityLog::log('Cloned Service', "From: {$service->name} To: {$newService->name}");

        return redirect()->route('services.show', $newService->id)->with('status', 'Service cloned successfully!');
    }

    public function permissions($id)
    {
        if (! Auth::user()->isAdmin() && ! Auth::user()->hasPermission('manage_settings')) {
            abort(403);
        }

        $service = Service::find($id);
        if (! $service) {
            abort(404);
        }

        $users = \App\Models\FileUser::all()->where('role', 'user');

        return view('services.permissions', compact('service', 'users'));
    }

    public function updatePermissions(Request $request, $id)
    {
        if (! Auth::user()->isAdmin() && ! Auth::user()->hasPermission('manage_settings')) {
            abort(403);
        }

        $service = Service::find($id);
        if (! $service) {
            abort(404);
        }

        $service->allowed_users = $request->input('users', []);
        $service->save();

        ActivityLog::log('Updated Service Permissions', 'Service: '.$service->name);

        return back()->with('status', 'Permissions updated successfully!');
    }

    public function reinstall($id, PanelScriptRunner $runner)
    {
        if (! Auth::user()->isAdmin() && ! Auth::user()->hasPermission('control_services')) {
            abort(403);
        }
        $service = Service::find($id);
        if (! $service) {
            abort(404);
        }
        $this->checkAccess($service);

        $script = $service->installer_script;
        if (! $script) {
            return back()->withErrors(['error' => 'No auto-installer script is associated with this service.']);
        }

        session_write_close();
        $logPath = storage_path("logs/services/{$service->id}.log");
        $cwd = escapeshellarg($service->working_dir);

        $commands = [
            'nodejs' => 'npm install',
            'composer' => 'composer install',
            'minecraft' => 'wget -O server.jar https://piston-data.mojang.com/v1/objects/4707d00eb8343d1a24413707cb4f6fa38d2cdca4/server.jar && echo "eula=true" > eula.txt',
            'python' => 'pip install -r requirements.txt',
        ];

        if (isset($commands[$script])) {
            $cmd = $commands[$script];
            $scriptPath = storage_path("app/reinstall_{$service->id}.sh");
            $runner->writeExecutableScript($scriptPath, "#!/bin/bash\ncd {$cwd}\necho '\n\n--- RERUNNING AUTO-INSTALLER: {$cmd} ---'\n{$cmd}\n");
            shell_exec(escapeshellarg($scriptPath).' >> '.escapeshellarg($logPath).' 2>&1 &');
            ActivityLog::log('Reinstalled Service', "Service: {$service->name}, Script: {$script}");

            return back()->with('status', 'Reinstallation process started in background. Check console for logs.');
        }

        return back()->withErrors(['error' => 'The associated script is no longer available.']);
    }
}
