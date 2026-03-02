<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FileManagerController extends Controller
{
    private function checkAccess($service)
    {
        $user = auth()->user();
        if ($user->role === 'admin') return true;
        if (isset($service->allowed_users) && in_array($user->id, $service->allowed_users)) return true;
        abort(403);
    }

    private function getSafePath($service, $path = '')
    {
        if ($service->type === 'docker') {
            $basePath = \App\Models\Setting::get('docker_base_path', '/var/lib/panel/docker');
            $root = $basePath . '/' . $service->id;
        } else {
            $root = $service->working_dir;
        }

        if (!is_dir($root)) {
            @mkdir($root, 0775, true);
        }

        $root = realpath($root);
        $fullPath = realpath($root . '/' . ($path ?: ''));
        
        if (!$fullPath || !str_starts_with($fullPath, $root)) {
            return $root;
        }
        return $fullPath;
    }

    public function index($id, Request $request)
    {
        $service = Service::find($id);
        if (!$service) abort(404);
        $this->checkAccess($service);
        
        $path = $this->getSafePath($service, $request->get('path', ''));
        $files = File::files($path);
        $directories = File::directories($path);
        
        if ($service->type === 'docker') {
            $basePath = \App\Models\Setting::get('docker_base_path', '/var/lib/panel/docker');
            $rootPath = realpath($basePath . '/' . $service->id);
        } else {
            $rootPath = realpath($service->working_dir);
        }

        $relativePath = str_replace($rootPath, '', $path);

        return view('services.files', compact('service', 'files', 'directories', 'relativePath'));
    }

    public function show($id, Request $request)
    {
        $service = Service::find($id);
        if (!$service) abort(404);
        $this->checkAccess($service);

        $path = $this->getSafePath($service, $request->get('file'));
        
        if (!File::isFile($path)) abort(404);
        
        return response()->json([
            'content' => File::get($path),
            'filename' => basename($path)
        ]);
    }

    public function save($id, Request $request)
    {
        $service = Service::find($id);
        if (!$service) abort(404);
        $this->checkAccess($service);

        $path = $this->getSafePath($service, $request->get('file'));
        
        File::put($path, $request->get('content'));
        ActivityLog::log("Modified file", "Service: {$service->name}, File: " . basename($path));

        return back()->with('status', 'File saved!');
    }

    public function createFile($id, Request $request)
    {
        $service = Service::find($id);
        if (!$service) abort(404);
        $this->checkAccess($service);

        $request->validate([
            'filename' => 'required|string|max:255',
            'path' => 'nullable|string'
        ]);

        $dirPath = $this->getSafePath($service, $request->get('path', ''));
        $filePath = $dirPath . '/' . basename($request->get('filename'));

        if (File::exists($filePath)) {
            return back()->withErrors(['error' => 'A file or directory with that name already exists.']);
        }

        File::put($filePath, '');

        ActivityLog::log("Created empty file", "Service: {$service->name}, File: " . basename($filePath));

        return back()->with('status', 'File created successfully!');
    }

    public function createDirectory($id, Request $request)
    {
        $service = Service::find($id);
        if (!$service) abort(404);
        $this->checkAccess($service);

        $request->validate([
            'dirname' => 'required|string|max:255',
            'path' => 'nullable|string'
        ]);

        $parentPath = $this->getSafePath($service, $request->get('path', ''));
        $newPath = $parentPath . '/' . basename($request->get('dirname'));

        if (File::exists($newPath)) {
            return back()->withErrors(['error' => 'A file or directory with that name already exists.']);
        }

        File::makeDirectory($newPath, 0775, true);

        ActivityLog::log("Created directory", "Service: {$service->name}, Folder: " . basename($newPath));

        return back()->with('status', 'Directory created successfully!');
    }

    public function destroy($id, Request $request)
    {
        $service = Service::find($id);
        if (!$service) abort(404);
        $this->checkAccess($service);

        $path = $this->getSafePath($service, $request->get('file'));
        
        if (File::isFile($path)) {
            ActivityLog::log("Deleted file", "Service: {$service->name}, File: " . basename($path));
            File::delete($path);
        } elseif (File::isDirectory($path)) {
            ActivityLog::log("Deleted directory", "Service: {$service->name}, Dir: " . basename($path));
            File::deleteDirectory($path);
        }
        
        return back()->with('status', 'Deleted!');
    }

    public function massDestroy($id, Request $request)
    {
        $service = Service::find($id);
        if (!$service) abort(404);
        $this->checkAccess($service);

        $files = $request->input('files', []);
        
        if (empty($files)) {
            return back()->withErrors(['error' => 'No files selected for deletion.']);
        }

        $deletedCount = 0;

        foreach ($files as $file) {
            $path = $this->getSafePath($service, $file);
            
            if (File::isFile($path)) {
                File::delete($path);
                $deletedCount++;
            } elseif (File::isDirectory($path)) {
                File::deleteDirectory($path);
                $deletedCount++;
            }
        }

        ActivityLog::log("Mass Deleted Files", "Service: {$service->name}, Count: {$deletedCount}");

        return back()->with('status', "Successfully deleted {$deletedCount} items!");
    }

    public function upload($id, Request $request)
    {
        $service = Service::find($id);
        if (!$service) abort(404);
        $this->checkAccess($service);

        $request->validate([
            'upload_file' => 'required|file',
            'path' => 'nullable|string'
        ]);

        $path = $this->getSafePath($service, $request->get('path', ''));
        $file = $request->file('upload_file');
        
        $file->move($path, $file->getClientOriginalName());

        ActivityLog::log("Uploaded file", "Service: {$service->name}, File: " . $file->getClientOriginalName());

        return back()->with('status', 'File uploaded successfully!');
    }

    public function multiUpload($id, Request $request)
    {
        $service = Service::find($id);
        if (!$service) return response()->json(['error' => 'Service not found'], 404);
        $this->checkAccess($service);

        $request->validate([
            'files.*' => 'required|file',
            'path' => 'nullable|string'
        ]);

        $path = $this->getSafePath($service, $request->get('path', ''));
        $uploadedCount = 0;

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $file->move($path, $file->getClientOriginalName());
                $uploadedCount++;
            }
        }

        ActivityLog::log("Multi-Upload", "Service: {$service->name}, Count: {$uploadedCount} files");

        return response()->json(['success' => true, 'count' => $uploadedCount]);
    }

    public function extract($id, Request $request)
    {
        $service = Service::find($id);
        if (!$service) abort(404);
        $this->checkAccess($service);

        $path = $this->getSafePath($service, $request->get('file'));
        
        if (!File::isFile($path) || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'zip') {
            return back()->withErrors(['error' => 'File is not a valid zip archive.']);
        }

        $zip = new \ZipArchive;
        if ($zip->open($path) === TRUE) {
            $extractPath = dirname($path);
            $zip->extractTo($extractPath);
            $zip->close();
            
            ActivityLog::log("Extracted ZIP", "Service: {$service->name}, File: " . basename($path));
            return back()->with('status', 'Archive extracted successfully!');
        }

        return back()->withErrors(['error' => 'Failed to open the zip file.']);
    }

    public function massExtract($id, Request $request)
    {
        $service = Service::find($id);
        if (!$service) abort(404);
        $this->checkAccess($service);

        $files = $request->input('files', []);
        if (empty($files)) {
            return back()->withErrors(['error' => 'No files selected.']);
        }

        $extractedCount = 0;
        foreach ($files as $file) {
            $path = $this->getSafePath($service, $file);
            if (File::isFile($path) && strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'zip') {
                $zip = new \ZipArchive;
                if ($zip->open($path) === TRUE) {
                    $zip->extractTo(dirname($path));
                    $zip->close();
                    $extractedCount++;
                }
            }
        }

        ActivityLog::log("Mass Extracted ZIPs", "Service: {$service->name}, Count: {$extractedCount}");
        return back()->with('status', "Successfully extracted {$extractedCount} archives!");
    }

    public function compress($id, Request $request)
    {
        $service = Service::find($id);
        if (!$service) abort(404);
        $this->checkAccess($service);

        $files = $request->input('files', []);
        if (empty($files)) {
            return back()->withErrors(['error' => 'No items selected to compress.']);
        }

        $baseDir = $this->getSafePath($service, $request->get('path', ''));
        $zipName = 'archive_' . now()->format('Y-m-d_His') . '.zip';
        $zipPath = $baseDir . '/' . $zipName;

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            foreach ($files as $file) {
                $fullPath = $this->getSafePath($service, $file);
                if (File::isFile($fullPath)) {
                    $zip->addFile($fullPath, basename($fullPath));
                } elseif (File::isDirectory($fullPath)) {
                    $filesInDir = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($fullPath),
                        \RecursiveIteratorIterator::LEAVES_ONLY
                    );
                    foreach ($filesInDir as $fileInDir) {
                        if (!$fileInDir->isDir()) {
                            $filePath = $fileInDir->getRealPath();
                            $relativePath = basename($fullPath) . '/' . substr($filePath, strlen($fullPath) + 1);
                            $zip->addFile($filePath, $relativePath);
                        }
                    }
                }
            }
            $zip->close();
            
            ActivityLog::log("Compressed Items", "Service: {$service->name}, Created: {$zipName}");
            return back()->with('status', 'Archive created successfully!');
        }

        return back()->withErrors(['error' => 'Failed to create the zip file.']);
    }

    public function rename($id, Request $request)
    {
        $service = Service::find($id);
        if (!$service) abort(404);
        $this->checkAccess($service);

        $request->validate([
            'file' => 'required|string',
            'new_name' => 'required|string|max:255'
        ]);

        $oldPath = $this->getSafePath($service, $request->get('file'));
        $parentDir = dirname($oldPath);
        $newPath = $parentDir . '/' . basename($request->get('new_name'));

        if (File::exists($newPath)) {
            return back()->withErrors(['error' => 'A file or directory with that name already exists.']);
        }

        if (File::isFile($oldPath)) {
            File::move($oldPath, $newPath);
        } else {
            rename($oldPath, $newPath);
        }

        ActivityLog::log("Renamed item", "Service: {$service->name}, From: " . basename($oldPath) . " To: " . basename($newPath));

        return back()->with('status', 'Item renamed successfully!');
    }
}
