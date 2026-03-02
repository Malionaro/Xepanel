<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NetworkController extends Controller
{
    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        // Try 'ss' first as it's the modern replacement for netstat
        $output = shell_exec('ss -tulnp 2>/dev/null');
        
        $ports = [];
        
        if ($output) {
            $lines = explode("\n", trim($output));
            // Skip header
            array_shift($lines);
            
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                
                // Example line:
                // tcp   LISTEN 0      128      0.0.0.0:80         0.0.0.0:*      users:(("nginx",pid=1234,fd=6))
                
                // Replace multiple spaces with a single space
                $line = preg_replace('/\s+/', ' ', $line);
                $parts = explode(' ', $line);
                
                if (count($parts) >= 6) {
                    $protocol = $parts[0];
                    $state = $parts[1];
                    $localAddress = $parts[4] ?? '';
                    $processInfo = implode(' ', array_slice($parts, 6)) ?? 'Unknown';
                    
                    // Extract port
                    $port = '';
                    if (strpos($localAddress, ':') !== false) {
                        $addrParts = explode(':', $localAddress);
                        $port = end($addrParts);
                    }
                    
                    // Clean up process info if possible
                    $processName = 'Unknown';
                    $pid = '-';
                    if (preg_match('/users:\(\("([^"]+)",pid=([0-9]+)/', $processInfo, $matches)) {
                        $processName = $matches[1];
                        $pid = $matches[2];
                    }

                    if ($port && is_numeric($port)) {
                        $ports[] = [
                            'protocol' => strtoupper($protocol),
                            'port' => $port,
                            'address' => str_replace(':' . $port, '', $localAddress),
                            'process' => $processName,
                            'pid' => $pid,
                        ];
                    }
                }
            }
        }
        
        // Sort by port number
        usort($ports, fn($a, $b) => (int)$a['port'] - (int)$b['port']);
        
        // Remove duplicates (sometimes ss shows multiple lines for same process/port)
        $uniquePorts = [];
        $seen = [];
        foreach ($ports as $p) {
            $key = $p['protocol'] . '-' . $p['port'];
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $uniquePorts[] = $p;
            }
        }

        return view('network.index', ['ports' => $uniquePorts]);
    }
}
