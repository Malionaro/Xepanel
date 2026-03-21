<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NetworkController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermission('manage_network')) {
            abort(403);
        }

        $services = Service::all();
        
        // Try 'ss' first as it's the modern replacement for netstat
        $output = shell_exec('ss -tulnp 2>/dev/null');
        
        $ports = [];
        
        if ($output) {
            $lines = explode("\n", trim($output));
            // Skip header
            array_shift($lines);
            
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                
                $line = preg_replace('/\s+/', ' ', $line);
                $parts = explode(' ', $line);
                
                if (count($parts) >= 6) {
                    $protocol = $parts[0];
                    $localAddress = $parts[4] ?? '';
                    $processInfo = implode(' ', array_slice($parts, 6));
                    
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
                        // Find if it belongs to a panel service
                        $owningService = null;
                        foreach ($services as $s) {
                            if ($s->type === 'docker') {
                                foreach ($s->docker_ports ?? [] as $dp) {
                                    $hostPort = explode(':', $dp)[0];
                                    if ($hostPort == $port) {
                                        $owningService = $s;
                                        break 2;
                                    }
                                }
                            } else {
                                if ($s->pid == $pid) {
                                    $owningService = $s;
                                    break;
                                }
                            }
                        }

                        $ports[] = [
                            'protocol' => strtoupper($protocol),
                            'port' => (int)$port,
                            'address' => str_replace(':' . $port, '', $localAddress),
                            'process' => $processName,
                            'pid' => $pid,
                            'service' => $owningService,
                        ];
                    }
                }
            }
        }
        
        // Sort by port number
        usort($ports, fn($a, $b) => $a['port'] - $b['port']);
        
        // Remove duplicates
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
