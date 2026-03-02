@extends('layouts.app')

@section('header_title', 'Service Details')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="space-y-8">
    <!-- Service Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-3xl bg-brand-500/10 flex items-center justify-center text-brand-500 shadow-inner">
                <i data-lucide="terminal" class="w-8 h-8"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">{{ $service->name }}</h2>
                <div class="flex flex-wrap items-center mt-1 gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $service->getStatus() == 'running' ? 'bg-green-50 dark:bg-green-900/20 text-green-600 border border-green-100 dark:border-green-900/30' : 'bg-red-50 dark:bg-red-900/20 text-red-600 border border-red-100 dark:border-red-900/30' }}">
                        <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $service->getStatus() == 'running' ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}"></span>
                        {{ strtoupper($service->getStatus()) }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-gray-100 dark:bg-dark-hover text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-dark-border">
                        <i data-lucide="{{ $service->type === 'docker' ? 'container' : 'cpu' }}" class="w-3 h-3 mr-1.5"></i>
                        {{ $service->type === 'docker' ? 'DOCKER' : 'HOST PROCESS' }}
                    </span>
                    
                    @php
                        $displayIp = request()->getHost();
                        $displayPort = '';
                        if ($service->type === 'docker' && !empty($service->docker_ports)) {
                            $displayPort = explode(':', $service->docker_ports[0])[0];
                        }
                        $fullAddress = $displayPort ? $displayIp . ':' . $displayPort : $displayIp;
                    @endphp
                    
                    @if($service->type === 'docker' && $displayPort)
                    <button onclick="navigator.clipboard.writeText('{{ $fullAddress }}'); const t = this.querySelector('span').innerText; this.querySelector('span').innerText = 'COPIED!'; setTimeout(() => this.querySelector('span').innerText = t, 2000);" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black tracking-widest bg-brand-50 text-brand-600 border border-brand-100 dark:bg-brand-900/20 dark:border-brand-900/30 hover:bg-brand-100 dark:hover:bg-brand-900/40 transition-colors cursor-pointer group/copy" title="Click to copy IP">
                        <i data-lucide="copy" class="w-3 h-3 mr-1.5 group-active/copy:scale-90 transition-transform"></i>
                        <span>{{ $fullAddress }}</span>
                    </button>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Quick Action Bar -->
        <div class="flex flex-wrap items-center gap-2">
            @if(Auth::user()->role === 'admin')
            <a href="{{ route('services.permissions', $service->id) }}" class="group flex items-center px-3 py-2.5 rounded-xl bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border text-gray-500 hover:text-purple-500 transition-all duration-300 shadow-sm overflow-hidden max-w-[42px] hover:max-w-[200px]" title="Permissions">
                <i data-lucide="users" class="w-5 h-5 shrink-0"></i>
                <span class="ml-2 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-bold text-xs">Permissions</span>
            </a>
            @endif
            @if($service->type !== 'docker')
            <a href="{{ route('services.systemd', $service->id) }}" class="group flex items-center px-3 py-2.5 rounded-xl bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border text-gray-500 hover:text-indigo-500 transition-all duration-300 shadow-sm overflow-hidden max-w-[42px] hover:max-w-[200px]" title="Auto-Start (Systemd)">
                <i data-lucide="shield-check" class="w-5 h-5 shrink-0"></i>
                <span class="ml-2 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-bold text-xs">Systemd</span>
            </a>
            @endif
            <a href="{{ route('services.crash_logs', $service->id) }}" class="group flex items-center px-3 py-2.5 rounded-xl bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border text-gray-500 hover:text-red-500 transition-all duration-300 shadow-sm overflow-hidden max-w-[42px] hover:max-w-[200px]" title="Crash Logs">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                <span class="ml-2 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-bold text-xs">Crash Logs</span>
            </a>
            <a href="{{ route('services.schedules', $service->id) }}" class="group flex items-center px-3 py-2.5 rounded-xl bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border text-gray-500 hover:text-yellow-500 transition-all duration-300 shadow-sm overflow-hidden max-w-[42px] hover:max-w-[200px]" title="Scheduled Tasks">
                <i data-lucide="clock" class="w-5 h-5 shrink-0"></i>
                <span class="ml-2 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-bold text-xs">Schedules</span>
            </a>
            <a href="{{ route('services.export', $service->id) }}" class="group flex items-center px-3 py-2.5 rounded-xl bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border text-gray-500 hover:text-blue-500 transition-all duration-300 shadow-sm overflow-hidden max-w-[42px] hover:max-w-[200px]" title="Export JSON">
                <i data-lucide="download" class="w-5 h-5 shrink-0"></i>
                <span class="ml-2 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-bold text-xs">Export</span>
            </a>
            <a href="{{ route('services.backups', $service->id) }}" class="group flex items-center px-3 py-2.5 rounded-xl bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border text-gray-500 hover:text-green-500 transition-all duration-300 shadow-sm overflow-hidden max-w-[42px] hover:max-w-[200px]" title="Backups">
                <i data-lucide="archive" class="w-5 h-5 shrink-0"></i>
                <span class="ml-2 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-bold text-xs">Backups</span>
            </a>
            @if(!empty($service->installer_script) && $service->type !== 'docker')
            <form action="{{ route('services.reinstall', $service->id) }}" method="POST" class="inline" onsubmit="return confirm('WARNING: This will rerun the installation script. Existing files might be overwritten or updated. Continue?')">
                @csrf
                <button type="submit" class="group flex items-center px-3 py-2.5 rounded-xl bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border text-gray-500 hover:text-brand-500 transition-all duration-300 shadow-sm overflow-hidden max-w-[42px] hover:max-w-[200px]" title="Reinstall / Rerun Script">
                    <i data-lucide="refresh-ccw" class="w-5 h-5 shrink-0"></i>
                    <span class="ml-2 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-bold text-xs">Reinstall</span>
                </button>
            </form>
            @endif
            
            <div class="w-px h-8 bg-gray-200 dark:bg-dark-border mx-1"></div>

            <a href="{{ route('services.edit', $service->id) }}" class="flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-hover transition-all">
                <i data-lucide="edit-3" class="w-4 h-4"></i>
                <span>Edit</span>
            </a>
            <a href="{{ route('services.files', $service->id) }}" class="flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-brand-500 text-white text-sm font-bold shadow-lg shadow-brand-500/25 hover:bg-brand-600 transition-all">
                <i data-lucide="folder-open" class="w-4 h-4"></i>
                <span>File Manager</span>
            </a>
        </div>
    </div>

    @if(session('status'))
        <div class="bg-green-100 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 text-green-700 dark:text-green-400 p-4 rounded-2xl flex items-center space-x-3">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span class="text-sm font-medium">{{ session('status') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar Controls -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Controls Card -->
            <div class="card bg-white dark:bg-dark-card p-6 rounded-[2rem] border border-gray-200 dark:border-dark-border shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-400 mb-6 flex items-center">
                    <i data-lucide="zap" class="w-3 h-3 mr-2"></i>
                    Execution Control
                </h3>
                
                <div class="space-y-3">
                    @if($service->getStatus() == 'stopped')
                        <form action="{{ route('services.start', $service->id) }}" method="POST">
                            @csrf
                            <button class="group w-full flex items-center justify-center space-x-3 bg-green-500 hover:bg-green-600 text-white font-black py-4 rounded-2xl transition-all shadow-lg shadow-green-500/20 active:scale-95">
                                <i data-lucide="play" class="w-5 h-5 fill-current"></i>
                                <span>START SERVICE</span>
                            </button>
                        </form>
                    @else
                        <form action="{{ route('services.stop', $service->id) }}" method="POST">
                            @csrf
                            <button class="group w-full flex items-center justify-center space-x-3 bg-red-500 hover:bg-red-600 text-white font-black py-4 rounded-2xl transition-all shadow-lg shadow-red-500/20 active:scale-95">
                                <i data-lucide="square" class="w-5 h-5 fill-current"></i>
                                <span>STOP SERVICE</span>
                            </button>
                        </form>
                    @endif
                    
                    <form action="{{ route('services.stop', $service->id) }}" method="POST">
                        @csrf
                        <button class="w-full flex items-center justify-center space-x-3 bg-gray-100 dark:bg-dark-hover text-gray-700 dark:text-gray-300 font-bold py-3 rounded-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-700 active:scale-95">
                            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                            <span>RESTART</span>
                        </button>
                    </form>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-100 dark:border-dark-border">
                    <a href="{{ route('services.envs', $service->id) }}" class="flex items-center justify-between text-sm font-bold text-gray-500 dark:text-gray-400 hover:text-brand-500 transition-colors group">
                        <div class="flex items-center space-x-2">
                            <i data-lucide="list" class="w-4 h-4 text-gray-400 group-hover:text-brand-500"></i>
                            <span>Environment Variables</span>
                        </div>
                        <span class="bg-gray-100 dark:bg-dark-hover px-2 py-0.5 rounded text-[10px]">{{ count($service->env_vars ?? []) }}</span>
                    </a>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card bg-white dark:bg-dark-card p-6 rounded-[2rem] border border-gray-200 dark:border-dark-border shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-400 mb-6 flex items-center">
                    <i data-lucide="info" class="w-3 h-3 mr-2"></i>
                    System Info
                </h3>
                
                <div class="space-y-5">
                    <div>
                        <span class="text-[10px] font-black uppercase text-gray-400 block mb-1 tracking-widest">Working Directory</span>
                        <div class="flex items-center space-x-2">
                            <i data-lucide="folder" class="w-3 h-3 text-gray-400"></i>
                            <code class="text-xs font-mono text-brand-600 dark:text-brand-400 truncate break-all">{{ $service->working_dir }}</code>
                        </div>
                    </div>
                    <div>
                        <span class="text-[10px] font-black uppercase text-gray-400 block mb-1 tracking-widest">Start Command</span>
                        <div class="flex items-center space-x-2">
                            <i data-lucide="play-circle" class="w-3 h-3 text-gray-400"></i>
                            <code class="text-xs font-mono text-gray-700 dark:text-gray-300">{{ $service->start_command }}</code>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div>
                            <span class="text-[10px] font-black uppercase text-gray-400 block mb-1 tracking-widest">Process PID</span>
                            <span class="text-xs font-mono font-bold text-gray-900 dark:text-white">{{ $service->pid ?: 'NONE' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-black uppercase text-gray-400 block mb-1 tracking-widest">Auto-Restart</span>
                            <span class="text-xs font-bold {{ $service->auto_restart ? 'text-green-500' : 'text-gray-500' }}">{{ $service->auto_restart ? 'ENABLED' : 'DISABLED' }}</span>
                        </div>
                    </div>

                    @if($service->getStatus() == 'running')
                    <div class="pt-4 border-t border-gray-100 dark:border-dark-border space-y-6">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest">CPU Usage</span>
                                <span id="detail-cpu" class="text-xs font-mono font-black text-brand-500">...%</span>
                            </div>
                            <div class="h-24 w-full">
                                <canvas id="cpuChart"></canvas>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest">RAM Usage</span>
                                <span id="detail-ram" class="text-xs font-mono font-black text-purple-500">...</span>
                            </div>
                            <div class="h-24 w-full">
                                <canvas id="ramChart"></canvas>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @if(Auth::user()->role === 'admin')
            <div class="px-6">
                <form action="{{ route('services.destroy', $service->id) }}" method="POST" onsubmit="return confirm('CRITICAL: Are you sure you want to PERMANENTLY DELETE this service and all its configuration?')">
                    @csrf
                    @method('DELETE')
                    <button class="w-full flex items-center justify-center space-x-2 text-red-500/50 hover:text-red-500 text-[10px] font-black uppercase tracking-widest transition-colors">
                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                        <span>Destroy Service</span>
                    </button>
                </form>
            </div>
            @endif
        </div>

        <!-- Console / Terminal Area -->
        <div class="lg:col-span-3">
            <div class="card bg-[#0d1117] rounded-[2rem] border border-gray-200 dark:border-dark-border shadow-2xl h-[700px] flex flex-col overflow-hidden transition-all duration-500 hover:border-brand-500/20">
                <div class="bg-white dark:bg-dark-card px-6 py-4 border-b border-gray-200 dark:border-dark-border flex justify-between items-center relative z-10">
                    <div class="flex items-center space-x-3">
                        <div class="flex space-x-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-500/20 border border-red-500/40"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500/20 border border-yellow-500/40"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500/20 border border-green-500/40"></div>
                        </div>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-4">Service Terminal</span>
                    </div>
                    <button onclick="clearConsole()" class="text-[10px] font-black text-gray-400 hover:text-brand-500 transition-colors uppercase tracking-widest flex items-center">
                        <i data-lucide="trash-2" class="w-3 h-3 mr-1.5"></i>
                        Clear
                    </button>
                </div>
                
                <div id="console-output" class="flex-1 bg-black p-8 font-mono text-[11px] leading-relaxed overflow-y-auto text-green-500/90 whitespace-pre-wrap selection:bg-brand-500 selection:text-white">
                    <div class="animate-pulse flex space-x-2">
                        <span class="text-brand-500 font-bold">system@filepanel:~$</span>
                        <span class="text-white italic">Initialising secure stream...</span>
                    </div>
                </div>
                
                <!-- Web Terminal Input -->
                <div class="bg-white dark:bg-dark-card px-6 py-4 border-t border-gray-200 dark:border-dark-border">
                    <form id="terminal-form" class="flex items-center bg-gray-50 dark:bg-black border border-gray-200 dark:border-dark-border rounded-xl px-4 py-2 focus-within:border-brand-500/50 transition-all" onsubmit="sendCommand(event)">
                        <span class="text-brand-500 mr-3 font-mono text-sm font-black italic">λ</span>
                        <input type="text" id="terminal-input" class="flex-1 bg-transparent text-gray-700 dark:text-gray-300 font-mono text-sm outline-none placeholder:text-gray-400 dark:placeholder:text-gray-600" placeholder="Execute command in {{ $service->type === 'docker' ? ($service->docker_main_mount ?: '/app') : basename($service->working_dir) }}..." autocomplete="off">
                        <div class="flex items-center space-x-2 ml-4 opacity-50">
                            <span class="text-[9px] font-black text-gray-400 border border-gray-300 dark:border-dark-border px-1.5 py-0.5 rounded">ENTER</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const consoleOutput = document.getElementById('console-output');
    let lastLogContent = '';

    // Chart Configuration
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true, display: false },
            x: { display: false }
        },
        plugins: { legend: { display: false } },
        elements: {
            point: { radius: 0 },
            line: { tension: 0.4, borderWidth: 2 }
        }
    };

    let cpuChart, ramChart;

    function initCharts() {
        const cpuCtx = document.getElementById('cpuChart')?.getContext('2d');
        const ramCtx = document.getElementById('ramChart')?.getContext('2d');

        if (cpuCtx) {
            cpuChart = new Chart(cpuCtx, {
                type: 'line',
                data: { labels: [], datasets: [{ data: [], borderColor: '#0c91eb', backgroundColor: 'rgba(12, 145, 235, 0.1)', fill: true }] },
                options: chartOptions
            });
        }

        if (ramCtx) {
            ramChart = new Chart(ramCtx, {
                type: 'line',
                data: { labels: [], datasets: [{ data: [], borderColor: '#a855f7', backgroundColor: 'rgba(168, 85, 247, 0.1)', fill: true }] },
                options: chartOptions
            });
        }
    }

    function updateCharts() {
        fetch('{{ route('metrics.history', $service->id) }}')
            .then(res => res.json())
            .then(data => {
                if (!data || !data.length) return;
                
                const labels = data.map(p => p.time);
                const cpuData = data.map(p => p.cpu);
                const ramData = data.map(p => p.ram);

                if (cpuChart) {
                    cpuChart.data.labels = labels;
                    cpuChart.data.datasets[0].data = cpuData;
                    cpuChart.update('none');
                }

                if (ramChart) {
                    ramChart.data.labels = labels;
                    ramChart.data.datasets[0].data = ramData;
                    ramChart.update('none');
                }
            });
    }

    function fetchLogs() {
        fetch('{{ route('services.logs', $service->id) }}')
            .then(res => res.json())
            .then(data => {
                if (data.logs !== lastLogContent) {
                    consoleOutput.textContent = data.logs || '--- System: Awaiting application output stream ---';
                    consoleOutput.scrollTop = consoleOutput.scrollHeight;
                    lastLogContent = data.logs;
                }
            });
    }

    function clearConsole() {
        consoleOutput.textContent = '';
        lastLogContent = '';
    }

    function sendCommand(e) {
        e.preventDefault();
        const input = document.getElementById('terminal-input');
        const command = input.value.trim();
        
        if (!command) return;
        
        input.value = '';
        
        fetch('{{ route('services.command', $service->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ command: command })
        })
        .then(() => {
            const cmdLine = document.createElement('div');
            cmdLine.className = 'text-brand-400 mt-2 font-bold';
            cmdLine.textContent = '> ' + command;
            consoleOutput.appendChild(cmdLine);
            consoleOutput.scrollTop = consoleOutput.scrollHeight;
            
            setTimeout(fetchLogs, 500);
        });
    }

    function fetchStats() {
        @if($service->getStatus() == 'running')
        fetch('{{ route('metrics.service', $service->id) }}')
            .then(res => res.json())
            .then(data => {
                const cpuEl = document.getElementById('detail-cpu');
                const ramEl = document.getElementById('detail-ram');
                if (cpuEl && ramEl) {
                    cpuEl.textContent = data.cpu + '%';
                    ramEl.textContent = data.ram;
                }
                updateCharts();
            });
        @endif
    }

    initCharts();
    setInterval(fetchLogs, 2000);
    setInterval(fetchStats, 5000);
    fetchLogs();
    fetchStats();
    
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
