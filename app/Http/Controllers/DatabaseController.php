<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
use Exception;

class DatabaseController extends Controller
{
    /**
     * Display the database management view for a service.
     */
    public function index($id)
    {
        $service = Service::find($id);
        if (!$service) abort(404);

        $databases = $service->databases ?? [];

        return view('services.databases', compact('service', 'databases'));
    }

    /**
     * Create a new database and user on the remote host.
     */
    public function store(Request $request, $id)
    {
        $service = Service::find($id);
        if (!$service) abort(404);

        $request->validate([
            'database_name' => 'required|string|max:32|alpha_dash',
        ]);

        $dbName = 's' . $service->id . '_' . $request->database_name;
        $dbUser = $dbName;
        $dbPass = bin2hex(random_bytes(12));

        try {
            $pdo = $this->getRemoteConnection();
            
            // Create database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}`");
            
            // Create user and grant permissions
            $pdo->exec("CREATE USER '{$dbUser}'@'%' IDENTIFIED BY '{$dbPass}'");
            $pdo->exec("GRANT ALL PRIVILEGES ON `{$dbName}`.* TO '{$dbUser}'@'%'");
            $pdo->exec("FLUSH PRIVILEGES");

            // Save info to service
            $databases = $service->databases ?? [];
            $databases[] = [
                'id' => uniqid(),
                'db_name' => $dbName,
                'db_user' => $dbUser,
                'db_pass' => $dbPass,
                'db_host' => Setting::get('mysql_host', '127.0.0.1'),
                'db_port' => Setting::get('mysql_port', 3306),
                'created_at' => now()->toDateTimeString(),
            ];
            $service->databases = $databases;
            $service->save();

            ActivityLog::log("Database Created", "Service: {$service->name}, DB: {$dbName}");

            return back()->with('status', 'Database successfully provisioned.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Database failure: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete a database and its user.
     */
    public function destroy($serviceId, $dbId)
    {
        $service = Service::find($serviceId);
        if (!$service) abort(404);

        $databases = $service->databases ?? [];
        $key = array_search($dbId, array_column($databases, 'id'));

        if ($key === false) return back();

        $dbInfo = $databases[$key];

        try {
            $pdo = $this->getRemoteConnection();
            $pdo->exec("DROP DATABASE IF EXISTS `{$dbInfo['db_name']}`");
            $pdo->exec("DROP USER IF EXISTS '{$dbInfo['db_user']}'@'%'");

            unset($databases[$key]);
            $service->databases = array_values($databases);
            $service->save();

            ActivityLog::log("Database Deleted", "Service: {$service->name}, DB: {$dbInfo['db_name']}");

            return back()->with('status', 'Database and user permanently removed.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Deletion failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Establish a connection to the configured management host.
     */
    protected function getRemoteConnection()
    {
        $host = Setting::get('mysql_host', '127.0.0.1');
        $port = Setting::get('mysql_port', 3306);
        $user = Setting::get('mysql_root_username', 'root');
        $pass = Setting::get('mysql_root_password', '');

        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        return new PDO($dsn, $user, $pass, $options);
    }
}
