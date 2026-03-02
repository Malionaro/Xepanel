<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ConsoleController;
use App\Http\Controllers\FileManagerController;
use App\Http\Controllers\DiscordController;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\MetricsController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\EggController;
use App\Models\Service;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 2FA Verification during login
Route::get('/two-factor-challenge', [TwoFactorController::class, 'prompt'])->name('two-factor.prompt');
Route::post('/two-factor-challenge', [TwoFactorController::class, 'verify'])->name('two-factor.verify');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Services
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    
    // API Keys
    Route::get('/user/api-keys', [ApiKeyController::class, 'index'])->name('user.api-keys');
    Route::post('/user/api-keys', [ApiKeyController::class, 'store'])->name('user.api-keys.store');
    Route::delete('/user/api-keys/{keyId}', [ApiKeyController::class, 'destroy'])->name('user.api-keys.destroy');
    
    // 2FA Setup
    Route::get('/user/two-factor', [TwoFactorController::class, 'showSetup'])->name('user.two-factor');
    Route::post('/user/two-factor/enable', [TwoFactorController::class, 'enable'])->name('user.two-factor.enable');
    Route::post('/user/two-factor/disable', [TwoFactorController::class, 'disable'])->name('user.two-factor.disable');
    
    // System Metrics
    Route::get('/metrics/system', [MetricsController::class, 'getSystemStats'])->name('metrics.system');
    Route::get('/metrics/service/{id}', [MetricsController::class, 'getServiceStats'])->name('metrics.service');
    Route::get('/metrics/service/{id}/history', [MetricsController::class, 'getServiceHistory'])->name('metrics.history');
    
    // Activity Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('logs.index');
    
    // User Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    
    // Services Mass Actions
    Route::post('/services/start-all', [ServiceController::class, 'startAll'])->name('services.start-all');
    Route::post('/services/stop-all', [ServiceController::class, 'stopAll'])->name('services.stop-all');

    // Services
    Route::get('/services/import', [ServiceController::class, 'showImport'])->name('services.import');
    Route::post('/services/import', [ServiceController::class, 'import'])->name('services.do_import');
    Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{id}', [ServiceController::class, 'show'])->name('services.show');
    Route::get('/services/{id}/export', [ServiceController::class, 'export'])->name('services.export');
    Route::post('/services/{id}/clone', [ServiceController::class, 'clone'])->name('services.clone');
    Route::get('/services/{id}/edit', [ServiceController::class, 'edit'])->name('services.edit');
    Route::put('/services/{id}', [ServiceController::class, 'update'])->name('services.update');
    Route::post('/services/{id}/start', [ServiceController::class, 'start'])->name('services.start');
    Route::post('/services/{id}/stop', [ServiceController::class, 'stop'])->name('services.stop');
    Route::post('/services/{id}/reinstall', [ServiceController::class, 'reinstall'])->name('services.reinstall');
    Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->name('services.destroy');
    
    // Service ENVs
    Route::get('/services/{id}/envs', [ServiceController::class, 'envs'])->name('services.envs');
    Route::post('/services/{id}/envs', [ServiceController::class, 'storeEnv'])->name('services.envs.store');
    Route::delete('/services/{id}/envs', [ServiceController::class, 'destroyEnv'])->name('services.envs.destroy');

    // Service Permissions
    Route::get('/services/{id}/permissions', [ServiceController::class, 'permissions'])->name('services.permissions');
    Route::post('/services/{id}/permissions', [ServiceController::class, 'updatePermissions'])->name('services.permissions.update');

    // Service Backups
    Route::get('/services/{id}/backups', [BackupController::class, 'index'])->name('services.backups');
    Route::post('/services/{id}/backups', [BackupController::class, 'store'])->name('services.backups.store');
    Route::get('/services/{id}/backups/{filename}/download', [BackupController::class, 'download'])->name('services.backups.download');
    Route::delete('/services/{id}/backups/{filename}', [BackupController::class, 'destroy'])->name('services.backups.destroy');
    
    // Scheduled Tasks
    Route::get('/services/{id}/schedules', [ScheduleController::class, 'index'])->name('services.schedules');
    Route::post('/services/{id}/schedules', [ScheduleController::class, 'store'])->name('services.schedules.store');
    Route::get('/services/{id}/schedules/{taskId}/edit', [ScheduleController::class, 'edit'])->name('services.schedules.edit');
    Route::put('/services/{id}/schedules/{taskId}', [ScheduleController::class, 'update'])->name('services.schedules.update');
    Route::delete('/services/{id}/schedules/{taskId}', [ScheduleController::class, 'destroy'])->name('services.schedules.destroy');

    // Crash Logs
    Route::get('/services/{id}/crash-logs', [ServiceController::class, 'crashLogs'])->name('services.crash_logs');
    Route::delete('/services/{id}/crash-logs/{logId}', [ServiceController::class, 'deleteCrashLog'])->name('services.crash_logs.destroy');

    // Systemd
    Route::get('/services/{id}/systemd', [ServiceController::class, 'systemd'])->name('services.systemd');

    // Console
    Route::get('/services/{id}/logs', [ConsoleController::class, 'getLogs'])->name('services.logs');
    Route::post('/services/{id}/command', [ConsoleController::class, 'executeCommand'])->name('services.command');
    
    // File Manager
    Route::get('/services/{id}/files', [FileManagerController::class, 'index'])->name('services.files');
    Route::get('/services/{id}/files/content', [FileManagerController::class, 'show'])->name('services.files.content');
    Route::post('/services/{id}/files/save', [FileManagerController::class, 'save'])->name('services.files.save');
    Route::post('/services/{id}/files/create', [FileManagerController::class, 'createFile'])->name('services.files.create');
    Route::post('/services/{id}/files/create-dir', [FileManagerController::class, 'createDirectory'])->name('services.files.create_dir');
    Route::post('/services/{id}/files/upload', [FileManagerController::class, 'upload'])->name('services.files.upload');
    Route::post('/services/{id}/files/multi-upload', [FileManagerController::class, 'multiUpload'])->name('services.files.multi_upload');
    Route::post('/services/{id}/files/extract', [FileManagerController::class, 'extract'])->name('services.files.extract');
    Route::post('/services/{id}/files/mass-extract', [FileManagerController::class, 'massExtract'])->name('services.files.mass_extract');
    Route::post('/services/{id}/files/compress', [FileManagerController::class, 'compress'])->name('services.files.compress');
    Route::post('/services/{id}/files/rename', [FileManagerController::class, 'rename'])->name('services.files.rename');
    Route::delete('/services/{id}/files/mass', [FileManagerController::class, 'massDestroy'])->name('services.files.mass_destroy');
    Route::delete('/services/{id}/files', [FileManagerController::class, 'destroy'])->name('services.files.destroy');
    
    // Global Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    // Egg Management
    Route::get('/eggs', [EggController::class, 'index'])->name('eggs.index');
    Route::get('/eggs/create', [EggController::class, 'create'])->name('eggs.create');
    Route::post('/eggs', [EggController::class, 'store'])->name('eggs.store');
    Route::get('/eggs/{id}/edit', [EggController::class, 'edit'])->name('eggs.edit');
    Route::put('/eggs/{id}', [EggController::class, 'update'])->name('eggs.update');
    Route::delete('/eggs/{id}', [EggController::class, 'destroy'])->name('eggs.destroy');
    
    // Network/Ports
    Route::get('/network', [NetworkController::class, 'index'])->name('network.index');
    
    // Discord Feed
    Route::get('/discord/messages', [DiscordController::class, 'getMessages']);
});

// Discord Webhook (External)
Route::post('/webhooks/discord', [DiscordController::class, 'webhook']);

// API Routes
Route::prefix('api')->middleware('api.auth')->group(function () {
    Route::get('/services', function () {
        return response()->json(\App\Models\Service::all());
    });

    Route::post('/services/{id}/start', function ($id) {
        $service = \App\Models\Service::find($id);
        if (!$service) return response()->json(['error' => 'Not found'], 404);
        $service->start();
        return response()->json(['status' => 'started', 'service' => $service->name]);
    });

    Route::post('/services/{id}/stop', function ($id) {
        $service = \App\Models\Service::find($id);
        if (!$service) return response()->json(['error' => 'Not found'], 404);
        $service->stop();
        return response()->json(['status' => 'stopped', 'service' => $service->name]);
    });

    Route::get('/services/{id}/status', function ($id) {
        $service = \App\Models\Service::find($id);
        if (!$service) return response()->json(['error' => 'Not found'], 404);
        return response()->json(['status' => $service->getStatus(), 'pid' => $service->pid]);
    });
});
