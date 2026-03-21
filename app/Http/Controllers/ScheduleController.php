<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    private function checkAccess($service)
    {
        $user = auth()->user();
        if ($user->isAdmin() || $user->hasPermission('view_services')) return true;
        if (isset($service->allowed_users) && in_array($user->id, $service->allowed_users)) return true;
        abort(403);
    }

    public function index($id)
    {
        $service = Service::find($id);
        if (!$service) abort(404);
        $this->checkAccess($service);
        
        return view('services.schedules', compact('service'));
    }

    public function store(Request $request, $id)
    {
        $service = Service::find($id);
        if (!$service) abort(404);
        $this->checkAccess($service);

        $request->validate([
            'name' => 'required|string',
            'command' => 'required|string',
            'cron' => 'required|string',
        ]);

        if (!isset($service->schedules)) {
            $service->schedules = [];
        }

        $service->schedules[] = [
            'id' => uniqid(),
            'name' => $request->name,
            'command' => $request->command,
            'cron' => $request->cron,
            'last_run' => null
        ];
        
        $service->save();

        ActivityLog::log("Added Scheduled Task", "Service: {$service->name}, Task: {$request->name}");

        return back()->with('status', 'Scheduled task added successfully!');
    }

    public function edit($id, $taskId)
    {
        $service = Service::find($id);
        if (!$service) abort(404);
        $this->checkAccess($service);

        $task = collect($service->schedules)->firstWhere('id', $taskId);
        if (!$task) abort(404);

        return view('services.schedules_edit', compact('service', 'task'));
    }

    public function update(Request $request, $id, $taskId)
    {
        $service = Service::find($id);
        if (!$service) abort(404);
        $this->checkAccess($service);

        $request->validate([
            'name' => 'required|string',
            'command' => 'required|string',
            'cron' => 'required|string',
        ]);

        $service->schedules = array_map(function($task) use ($taskId, $request) {
            if ($task['id'] === $taskId) {
                return array_merge($task, [
                    'name' => $request->name,
                    'command' => $request->command,
                    'cron' => $request->cron,
                ]);
            }
            return $task;
        }, $service->schedules);

        $service->save();

        ActivityLog::log("Updated Scheduled Task", "Service: {$service->name}, Task: {$request->name}");

        return redirect()->route('services.schedules', $service->id)->with('status', 'Scheduled task updated!');
    }

    public function destroy($id, $taskId)
    {
        $service = Service::find($id);
        if (!$service) abort(404);
        $this->checkAccess($service);

        if (isset($service->schedules)) {
            $service->schedules = array_filter($service->schedules, function($task) use ($taskId) {
                return $task['id'] !== $taskId;
            });
            // Re-index array
            $service->schedules = array_values($service->schedules);
            $service->save();
            
            ActivityLog::log("Deleted Scheduled Task", "Service: {$service->name}");
        }

        return back()->with('status', 'Scheduled task deleted!');
    }
}
