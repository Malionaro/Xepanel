<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Facades\File;

class BackupController extends Controller
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

        $backupDir = "backups/{$service->id}";
        $backups = [];
        
        if (Storage::disk('local')->exists($backupDir)) {
            $files = Storage::disk('local')->files($backupDir);
            foreach ($files as $file) {
                if (str_ends_with($file, '.zip')) {
                    $backups[] = [
                        'name' => basename($file),
                        'path' => $file,
                        'size' => round(Storage::disk('local')->size($file) / 1024 / 1024, 2) . ' MB',
                        'time' => date('Y-m-d H:i:s', Storage::disk('local')->lastModified($file))
                    ];
                }
            }
        }
        
        // Sort by newest first
        usort($backups, fn($a, $b) => strtotime($b['time']) - strtotime($a['time']));

        return view('services.backups', compact('service', 'backups'));
    }

    public function store($id)
    {
        $service = Service::find($id);
        if (!$service) abort(404);
        $this->checkAccess($service);

        $sourcePath = realpath($service->working_dir);
        if (!$sourcePath || !is_dir($sourcePath)) {
            return back()->withErrors(['error' => 'Working directory does not exist or is invalid.']);
        }

        // Check size limit before starting
        $maxSizeMB = \App\Models\Setting::get('max_backup_size_mb', 500);
        $currentSizeMB = round((int)shell_exec("du -sm " . escapeshellarg($sourcePath) . " | cut -f1") ?? 0, 2);
        
        if ($currentSizeMB > $maxSizeMB) {
            return back()->withErrors(['error' => "Directory is too large ({$currentSizeMB} MB). Max allowed is {$maxSizeMB} MB."]);
        }

        $backupDir = storage_path("app/private/backups/{$service->id}");
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0775, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $zipFileName = "backup_{$timestamp}.zip";
        $zipFilePath = "{$backupDir}/{$zipFileName}";

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourcePath),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($sourcePath) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
            $zip->close();
            
            ActivityLog::log("Created Backup", "Service: {$service->name}, File: {$zipFileName}");
            return back()->with('status', 'Backup created successfully!');
        } else {
            return back()->withErrors(['error' => 'Failed to create zip file.']);
        }
    }

    public function download($id, $filename)
    {
        $service = Service::find($id);
        if (!$service) abort(404);
        $this->checkAccess($service);

        $path = "backups/{$service->id}/{$filename}";
        
        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        ActivityLog::log("Downloaded Backup", "Service: {$service->name}, File: {$filename}");
        return Storage::disk('local')->download($path);
    }

    public function destroy($id, $filename)
    {
        $service = Service::find($id);
        if (!$service) abort(404);
        $this->checkAccess($service);

        $path = "backups/{$service->id}/{$filename}";
        
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
            ActivityLog::log("Deleted Backup", "Service: {$service->name}, File: {$filename}");
        }

        return back()->with('status', 'Backup deleted!');
    }
}
