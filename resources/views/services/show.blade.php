@extends('layouts.app')

@section('header_title', __('panel.service_details'))

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="space-y-10">
    <!-- Service Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 md:gap-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:space-x-6">
            <div class="w-16 h-16 md:w-20 md:h-20 rounded-2xl md:rounded-[2rem] bg-brand-500/10 flex items-center justify-center text-brand-500 shadow-xl border border-brand-500/20 shrink-0">
                <i data-lucide="terminal" class="w-8 h-8 md:w-10 md:h-10"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-[0.2em] {{ $service->getStatus() == 'running' ? 'bg-green-500/10 text-green-500 border border-green-500/20' : 'bg-red-500/10 text-red-500 border border-red-500/20' }}">
                        <span class="w-1.5 h-1.5 rounded-full mr-2 {{ $service->getStatus() == 'running' ? 'bg-green-500 animate-pulse glow-green' : 'bg-red-500' }}"></span>
                        {{ __($service->getStatus() == 'running' ? 'panel.online' : 'panel.offline') }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-[0.2em] bg-slate-500/10 text-slate-500 dark:text-slate-400 border border-slate-500/20">
                        <i data-lucide="{{ $service->type === 'docker' ? 'container' : 'cpu' }}" class="w-3 h-3 mr-2"></i>
                        {{ $service->type }}
                    </span>
                </div>
                <h2 class="text-3xl md:text-4xl font-black tracking-tight text-slate-900 dark:text-white truncate">{{ $service->name }}</h2>
                
                @php
                    $displayIp = request()->getHost();
                    $displayPort = '';
                    if ($service->type === 'docker' && !empty($service->docker_ports)) {
                        $displayPort = explode(':', $service->docker_ports[0])[0];
                    }
                    $fullAddress = $displayPort ? $displayIp . ':' . $displayPort : $displayIp;
                @endphp

                <div class="mt-4">
                    <button onclick="navigator.clipboard.writeText('{{ $fullAddress }}'); this.querySelector('span').textContent = '{{ __('panel.copied') }}'; setTimeout(() => this.querySelector('span').textContent = '{{ $fullAddress }}', 2000)" 
                            class="group flex items-center space-x-3 px-3 md:px-4 py-2 rounded-2xl bg-white/50 dark:bg-white/5 border border-slate-200 dark:border-white/10 hover:border-brand-500/50 transition-all shadow-sm glass max-w-full overflow-hidden">
                        <div class="w-8 h-8 rounded-xl bg-brand-500/10 flex items-center justify-center text-brand-500 shrink-0">
                            <i data-lucide="copy" class="w-4 h-4"></i>
                        </div>
                        <span class="text-[10px] md:text-xs font-mono font-black text-slate-600 dark:text-slate-300 tracking-wider truncate">{{ $fullAddress }}</span>
                        <div class="hidden sm:block px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-white/10 text-[8px] font-black uppercase text-slate-400 group-hover:text-brand-500 transition-colors shrink-0">{{ __('panel.click_to_copy') }}</div>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Quick Action Bar -->
        <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-3">
            <div class="flex items-center justify-around sm:justify-start glass dark:bg-dark-card p-1.5 rounded-2xl border-slate-200 dark:border-dark-border shadow-sm">
                @if(Auth::user()->role === 'admin')
                <a href="{{ route('services.permissions', $service->id) }}" class="group flex items-center px-3 py-2.5 rounded-xl text-slate-500 hover:text-purple-500 hover:bg-purple-500/10 transition-all duration-500 overflow-hidden sm:max-w-[46px] sm:hover:max-w-[200px]" title="{{ __('panel.permissions') }}">
                    <i data-lucide="users" class="w-5 h-5 shrink-0"></i>
                    <span class="ml-3 whitespace-nowrap opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-all duration-500 font-black text-[10px] uppercase tracking-[0.2em] hidden sm:block">{{ __('panel.permissions') }}</span>
                </a>
                @endif
                <a href="{{ route('services.schedules', $service->id) }}" class="group flex items-center px-3 py-2.5 rounded-xl text-slate-500 hover:text-yellow-500 hover:bg-yellow-500/10 transition-all duration-500 overflow-hidden sm:max-w-[46px] sm:hover:max-w-[200px]" title="{{ __('panel.schedules') }}">
                    <i data-lucide="clock" class="w-5 h-5 shrink-0"></i>
                    <span class="ml-3 whitespace-nowrap opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-all duration-500 font-black text-[10px] uppercase tracking-[0.2em] hidden sm:block">{{ __('panel.schedules') }}</span>
                </a>
                <a href="{{ route('services.backups', $service->id) }}" class="group flex items-center px-3 py-2.5 rounded-xl text-slate-500 hover:text-green-500 hover:bg-green-500/10 transition-all duration-500 overflow-hidden sm:max-w-[46px] sm:hover:max-w-[200px]" title="{{ __('panel.backups') }}">
                    <i data-lucide="archive" class="w-5 h-5 shrink-0"></i>
                    <span class="ml-3 whitespace-nowrap opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-all duration-500 font-black text-[10px] uppercase tracking-[0.2em] hidden sm:block">{{ __('panel.backups') }}</span>
                </a>
                <a href="{{ route('services.databases', $service->id) }}" class="group flex items-center px-3 py-2.5 rounded-xl text-slate-500 hover:text-emerald-500 hover:bg-emerald-500/10 transition-all duration-500 overflow-hidden sm:max-w-[46px] sm:hover:max-w-[200px]" title="{{ __('panel.databases') }}">
                    <i data-lucide="database" class="w-5 h-5 shrink-0"></i>
                    <span class="ml-3 whitespace-nowrap opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-all duration-500 font-black text-[10px] uppercase tracking-[0.2em] hidden sm:block">{{ __('panel.databases') }}</span>
                </a>
                <div class="w-px h-5 bg-slate-200 dark:bg-slate-800 mx-1"></div>
                <a href="{{ route('services.edit', $service->id) }}" class="group flex items-center px-3 py-2.5 rounded-xl text-slate-500 hover:text-brand-500 hover:bg-brand-500/10 transition-all duration-500 overflow-hidden sm:max-w-[46px] sm:hover:max-w-[200px]" title="{{ __('panel.settings') }}">
                    <i data-lucide="settings" class="w-5 h-5 shrink-0"></i>
                    <span class="ml-3 whitespace-nowrap opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-all duration-500 font-black text-[10px] uppercase tracking-[0.2em] hidden sm:block">{{ __('panel.settings') }}</span>
                </a>
            </div>

            <a href="{{ route('services.files', $service->id) }}" class="flex items-center justify-center space-x-3 px-8 py-3.5 rounded-2xl bg-brand-500 text-white text-xs font-black uppercase tracking-[0.2em] shadow-xl shadow-brand-500/25 hover:bg-brand-600 transition-all hover:-translate-y-1 active:scale-95">
                <i data-lucide="folder-open" class="w-4 h-4"></i>
                <span>{{ __('panel.file_manager') }}</span>
            </a>
        </div>
    </div>

    @if(session('status'))
        <div class="bg-green-500/10 border border-green-500/20 text-green-600 dark:text-green-400 p-5 rounded-3xl flex items-center space-x-4 animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="w-10 h-10 rounded-xl bg-green-500/20 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
            <span class="text-sm font-bold">{{ session('status') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 md:gap-10">
        <!-- Sidebar Controls -->
        <div class="lg:col-span-1 space-y-6 md:space-y-8 order-2 lg:order-1">
            <!-- Controls Card -->
            <div class="glass dark:bg-dark-card p-6 md:p-8 rounded-[2rem] md:rounded-[2.5rem] border border-slate-200 dark:border-dark-border shadow-sm">
                <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-6 md:mb-8 flex items-center">
                    <i data-lucide="zap" class="w-3.5 h-3.5 mr-2.5 text-brand-500"></i>
                    {{ __('panel.execution_control') }}
                </h3>
                
                <div class="space-y-4">
                    @if($service->getStatus() == 'stopped')
                        <form action="{{ route('services.start', $service->id) }}" method="POST">
                            @csrf
                            <button class="w-full flex items-center justify-center space-x-3 bg-green-500 hover:bg-green-600 text-white font-black py-4 md:py-5 rounded-2xl transition-all shadow-xl shadow-green-500/20 active:scale-95 group text-xs">
                                <i data-lucide="play" class="w-5 h-5 md:w-6 md:h-6 fill-current group-hover:scale-110 transition-transform"></i>
                                <span class="tracking-widest uppercase">{{ __('panel.start') }}</span>
                            </button>
                        </form>
                    @else
                        <form action="{{ route('services.stop', $service->id) }}" method="POST">
                            @csrf
                            <button class="w-full flex items-center justify-center space-x-3 bg-red-500 hover:bg-red-600 text-white font-black py-4 md:py-5 rounded-2xl transition-all shadow-xl shadow-red-500/20 active:scale-95 group text-xs">
                                <i data-lucide="square" class="w-5 h-5 md:w-6 md:h-6 fill-current group-hover:scale-110 transition-transform"></i>
                                <span class="tracking-widest uppercase">{{ __('panel.stop') }}</span>
                            </button>
                        </form>
                    @endif
                    
                    <form action="{{ route('services.stop', $service->id) }}" method="POST">
                        @csrf
                        <button class="w-full flex items-center justify-center space-x-3 bg-slate-100 dark:bg-slate-800/50 text-slate-600 dark:text-slate-300 font-black py-3.5 md:py-4 rounded-2xl transition-all hover:bg-slate-200 dark:hover:bg-slate-800 active:scale-95 border border-slate-200 dark:border-slate-700">
                            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                            <span class="tracking-widest text-[10px] uppercase">{{ __('panel.restart') }}</span>
                        </button>
                    </form>
                </div>

                <div class="mt-8 pt-8 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('services.envs', $service->id) }}" class="flex items-center justify-between group">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-brand-500/10 flex items-center justify-center text-brand-500 group-hover:bg-brand-500 group-hover:text-white transition-all">
                                <i data-lucide="list" class="w-4 h-4"></i>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 group-hover:text-brand-500 transition-colors">{{ __('panel.docker_infra') }}</span>
                        </div>
                        <span class="bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-lg text-[9px] font-black text-slate-500">{{ count($service->env_vars ?? []) }}</span>
                    </a>
                </div>
            </div>

            <!-- Info Card -->
            <div class="glass dark:bg-dark-card p-8 rounded-[2.5rem] border border-slate-200 dark:border-dark-border shadow-sm">
                <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-8 flex items-center">
                    <i data-lucide="activity" class="w-3.5 h-3.5 mr-2.5 text-brand-500"></i>
                    {{ __('panel.live_telemetry') }}
                </h3>
                
                <div class="space-y-8">
                    @if($service->getStatus() == 'running')
                    <div class="space-y-6">
                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-[9px] font-black uppercase text-slate-500 tracking-widest">{{ __('panel.cpu_utilisation') }}</span>
                                <span id="detail-cpu" class="text-xs font-black text-brand-500">...%</span>
                            </div>
                            <div class="h-20 w-full">
                                <canvas id="cpuChart"></canvas>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-[9px] font-black uppercase text-slate-500 tracking-widest">{{ __('panel.memory_footprint') }}</span>
                                <span id="detail-ram" class="text-xs font-black text-purple-500">...</span>
                            </div>
                            <div class="h-20 w-full">
                                <canvas id="ramChart"></canvas>
                            </div>
                        </div>

                        <button onclick="toggleAnalytics24h()" class="w-full flex items-center justify-center space-x-3 py-3 bg-slate-50 dark:bg-slate-800/50 rounded-2xl text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 hover:text-brand-500 transition-all border border-slate-100 dark:border-slate-800">
                            <i data-lucide="trending-up" class="w-4 h-4"></i>
                            <span>{{ __('panel.open_analytics') }}</span>
                        </button>
                    </div>
                    @else
                    <div class="py-10 text-center">
                        <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                            <i data-lucide="activity" class="w-6 h-6"></i>
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('panel.awaiting_data') }}</p>
                    </div>
                    @endif

                    <div class="pt-8 border-t border-slate-100 dark:border-slate-800 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest">{{ $service->type === 'docker' ? __('panel.container_id') : __('panel.pid') }}</span>
                            <span class="text-xs font-mono font-black text-slate-900 dark:text-white bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-md">
                                @if($service->type === 'docker')
                                    {{ $service->pid ? substr($service->pid, 0, 8) : 'N/A' }}
                                @else
                                    {{ $service->pid ?: '---' }}
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest">{{ __('panel.stability') }}</span>
                            <span class="text-[9px] font-black {{ $service->auto_restart ? 'text-green-500' : 'text-slate-400' }}">{{ $service->auto_restart ? 'OPTIMIZED' : 'LEGACY' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if(Auth::user()->role === 'admin')
            <form action="{{ route('services.destroy', $service->id) }}" method="POST" onsubmit="return confirm('CRITICAL: Permanent deletion?')" class="px-8">
                @csrf
                @method('DELETE')
                <button class="w-full flex items-center justify-center space-x-2 text-red-500/40 hover:text-red-500 text-[9px] font-black uppercase tracking-[0.2em] transition-all">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    <span>{{ __('panel.terminate_infrastructure') }}</span>
                </button>
            </form>
            @endif
        </div>

        <!-- Console / Terminal Area -->
        <div class="lg:col-span-3 order-1 lg:order-2">
            <div class="glass dark:bg-dark-card rounded-[2rem] md:rounded-[3rem] border border-slate-200 dark:border-dark-border shadow-2xl h-[500px] md:h-[780px] flex flex-col overflow-hidden group hover:border-brand-500/30 transition-all duration-500">
                <div class="bg-white/80 dark:bg-dark-card px-6 md:px-10 py-4 md:py-6 border-b border-slate-200 dark:border-dark-border flex justify-between items-center relative z-10">
                    <div class="flex items-center space-x-3 md:space-x-6">
                        <div class="hidden sm:flex space-x-2">
                            <div class="w-3.5 h-3.5 rounded-full bg-red-500 shadow-sm shadow-red-500/20"></div>
                            <div class="w-3.5 h-3.5 rounded-full bg-yellow-500 shadow-sm shadow-yellow-500/20"></div>
                            <div class="w-3.5 h-3.5 rounded-full bg-green-500 shadow-sm shadow-green-500/20"></div>
                        </div>
                        <div class="hidden sm:block h-4 w-px bg-slate-200 dark:bg-slate-800"></div>
                        <span class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] md:tracking-[0.3em] flex items-center">
                            <i data-lucide="terminal" class="w-3.5 h-3.5 mr-2 md:mr-2.5"></i>
                            {{ __('panel.infrastructure_stream') }}
                        </span>
                    </div>
                    <div class="flex items-center space-x-1 md:space-x-2">
                        <button onclick="toggleFullscreen()" class="p-2 md:p-2.5 rounded-xl text-slate-400 hover:text-brand-500 hover:bg-brand-500/10 transition-all" title="Toggle Fullscreen">
                            <i data-lucide="maximize" class="w-4 h-4 md:w-5 md:h-5" id="fullscreen-icon"></i>
                        </button>
                        <button onclick="clearConsole()" class="p-2 md:p-2.5 rounded-xl text-slate-400 hover:text-brand-500 hover:bg-brand-500/10 transition-all" title="Clear Terminal">
                            <i data-lucide="trash-2" class="w-4 h-4 md:w-5 md:h-5"></i>
                        </button>
                    </div>
                </div>
                
                <div id="console-output" class="flex-1 bg-[#020617] p-6 md:p-10 font-mono text-[10px] md:text-xs leading-relaxed overflow-y-auto text-slate-300 whitespace-pre-wrap selection:bg-brand-500/30 custom-scrollbar transition-all duration-500">
                    <div class="flex items-center space-x-3 text-brand-500/60 font-black italic mb-4 md:mb-6">
                        <span>system@filepanel:~$</span>
                        <span class="animate-pulse">_</span>
                    </div>
                    <div class="text-slate-500 italic opacity-50">{{ __('panel.syncing') }}</div>
                </div>
                
                <!-- Web Terminal Input -->
                <div class="px-4 md:px-8 py-4 md:py-8 border-t border-slate-200 dark:border-dark-border bg-white/50 dark:bg-dark-card/50">
                    <form id="terminal-form" class="flex items-center bg-white dark:bg-slate-950 border border-slate-200 dark:border-dark-border rounded-xl md:rounded-2xl px-4 md:px-6 py-3 md:py-4 shadow-sm focus-within:shadow-xl focus-within:shadow-brand-500/10 focus-within:border-brand-500/50 transition-all" onsubmit="sendCommand(event)">
                        <span class="text-brand-500 mr-3 md:mr-4 font-mono text-base md:text-lg font-black italic">λ</span>
                        <input type="text" id="terminal-input" class="flex-1 bg-transparent text-slate-700 dark:text-slate-200 font-mono text-xs md:text-sm outline-none placeholder:text-slate-400 dark:placeholder:text-slate-600" placeholder="{{ __('panel.forward_command') }}" autocomplete="off">
                        <div class="hidden sm:flex items-center space-x-3 ml-4 md:ml-6">
                            <span class="text-[8px] md:text-[9px] font-black text-slate-400 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 px-2 py-1 rounded-md uppercase tracking-widest shadow-sm">{{ __('panel.execute') }}</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 24h Analytics Modal -->
<div id="analytics-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 md:p-8 bg-slate-950/80 backdrop-blur-xl transition-all duration-500 opacity-0">
    <div class="glass dark:bg-dark-card w-full max-w-6xl rounded-[2.5rem] md:rounded-[4rem] border border-white/10 shadow-2xl overflow-hidden flex flex-col max-h-[95vh] md:max-h-[90vh] scale-95 transition-transform duration-500" id="analytics-content">
        <div class="px-6 md:px-12 py-6 md:py-10 border-b border-white/5 flex justify-between items-center bg-white/5 shrink-0">
            <div class="flex items-center space-x-4 md:space-x-6">
                <div class="w-12 h-12 md:w-16 md:h-16 rounded-2xl md:rounded-3xl bg-brand-500/20 flex items-center justify-center text-brand-500 shadow-xl border border-brand-500/20 shrink-0">
                    <i data-lucide="bar-chart-3" class="w-6 h-6 md:w-8 md:h-8"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-lg md:text-2xl font-black text-white uppercase tracking-tight truncate">{{ __('panel.telemetry_insight') }}</h3>
                    <p class="text-[10px] md:sm text-slate-400 font-medium truncate">{{ __('panel.performance_history', ['name' => $service->name]) }}</p>
                </div>
            </div>
            <button onclick="toggleAnalytics24h()" class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center text-slate-400 hover:text-white bg-white/5 rounded-xl md:rounded-2xl border border-white/10 transition-all hover:bg-white/10 shrink-0">
                <i data-lucide="x" class="w-5 h-5 md:w-6 md:h-6"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6 md:p-12 space-y-10 md:space-y-16 custom-scrollbar">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12">
                <div class="glass p-6 md:p-10 rounded-[2rem] md:rounded-[3rem] border-white/5 bg-white/5">
                    <div class="flex items-center justify-between mb-6 md:mb-8">
                        <div>
                            <h4 class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-1">{{ __('panel.cpu_load_trend') }}</h4>
                            <p class="text-[9px] font-bold text-white/40">{{ __('panel.sampled_5m') }}</p>
                        </div>
                        <div id="cpu-avg" class="text-[10px] font-black text-brand-400 bg-brand-500/20 px-3 py-1 rounded-lg border border-brand-500/20">Avg: ...%</div>
                    </div>
                    <div class="h-48 md:h-64 w-full">
                        <canvas id="cpuChart24h"></canvas>
                    </div>
                </div>
                <div class="glass p-6 md:p-10 rounded-[2rem] md:rounded-[3rem] border-white/5 bg-white/5">
                    <div class="flex items-center justify-between mb-6 md:mb-8">
                        <div>
                            <h4 class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-1">{{ __('panel.memory_allocation_24h') }}</h4>
                            <p class="text-[9px] font-bold text-white/40">{{ __('panel.sampled_5m') }}</p>
                        </div>
                        <div id="ram-avg" class="text-[10px] font-black text-purple-400 bg-purple-500/20 px-3 py-1 rounded-lg border border-purple-500/20">Avg: ... MB</div>
                    </div>
                    <div class="h-48 md:h-64 w-full">
                        <canvas id="ramChart24h"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const consoleOutput = document.getElementById('console-output');
    let lastLogContent = '';
    let logSource, statsSource;
    let cpuChart24h, ramChart24h;

    // Chart Configuration
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { 
                beginAtZero: true, 
                display: true, 
                grid: { color: 'rgba(255,255,255,0.03)' },
                ticks: { color: 'rgba(255,255,255,0.3)', font: { size: 10, weight: 'bold' } }
            },
            x: { 
                display: true, 
                grid: { display: false }, 
                ticks: { color: 'rgba(255,255,255,0.3)', font: { size: 10, weight: 'bold' }, maxRotation: 0, autoSkip: true, maxTicksLimit: 12 } 
            }
        },
        plugins: { legend: { display: false } },
        elements: {
            point: { radius: 0, hoverRadius: 6, backgroundColor: '#8b5cf6' },
            line: { tension: 0.4, borderWidth: 3 }
        }
    };

    const miniChartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true, display: false },
            x: { display: false }
        },
        plugins: { legend: { display: false } },
        elements: {
            point: { radius: 0 },
            line: { tension: 0.5, borderWidth: 3 }
        }
    };

    let cpuChart, ramChart;
    let logInterval, statsInterval;

    function initCharts() {
        const cpuCtx = document.getElementById('cpuChart')?.getContext('2d');
        const ramCtx = document.getElementById('ramChart')?.getContext('2d');

        if (cpuCtx) {
            const gradient = cpuCtx.createLinearGradient(0, 0, 0, 100);
            gradient.addColorStop(0, 'rgba(139, 92, 246, 0.3)');
            gradient.addColorStop(1, 'rgba(139, 92, 246, 0)');
            
            cpuChart = new Chart(cpuCtx, {
                type: 'line',
                data: { labels: [], datasets: [{ data: [], borderColor: '#8b5cf6', backgroundColor: gradient, fill: true }] },
                options: miniChartOptions
            });
        }

        if (ramCtx) {
            const gradient = ramCtx.createLinearGradient(0, 0, 0, 100);
            gradient.addColorStop(0, 'rgba(168, 85, 247, 0.3)');
            gradient.addColorStop(1, 'rgba(168, 85, 247, 0)');

            ramChart = new Chart(ramCtx, {
                type: 'line',
                data: { labels: [], datasets: [{ data: [], borderColor: '#a855f7', backgroundColor: gradient, fill: true }] },
                options: miniChartOptions
            });
        }
    }

    function fetchLogs() {
        fetch('{{ route('services.logs', $service->id) }}')
            .then(res => res.json())
            .then(data => {
                if (data.logs !== lastLogContent) {
                    if (!data.logs) {
                        consoleOutput.innerHTML = '<div class="text-slate-500 italic opacity-50">{{ __('panel.awaiting_data') }}</div>';
                    } else {
                        consoleOutput.textContent = data.logs;
                    }
                    consoleOutput.scrollTop = consoleOutput.scrollHeight;
                    lastLogContent = data.logs;
                }
            })
            .catch(() => {});
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
            })
            .catch(() => {});
        @endif
    }

    function toggleAnalytics24h() {
        const modal = document.getElementById('analytics-modal');
        const content = document.getElementById('analytics-content');
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            setTimeout(() => { 
                modal.classList.add('opacity-100'); 
                modal.classList.remove('opacity-0');
                content.classList.add('scale-100');
                content.classList.remove('scale-95');
            }, 10);
            loadAnalytics24h();
        } else {
            modal.classList.add('opacity-0');
            modal.classList.remove('opacity-100');
            content.classList.add('scale-95');
            content.classList.remove('scale-100');
            setTimeout(() => modal.classList.add('hidden'), 500);
        }
    }

    function loadAnalytics24h() {
        fetch('{{ route('metrics.history_24h', $service->id) }}')
            .then(res => res.json())
            .then(data => {
                if (!data || data.length === 0) {
                    document.getElementById('cpu-avg').textContent = '...';
                    document.getElementById('ram-avg').textContent = '...';
                    return;
                }

                const labels = data.map(p => p.time.split(' ')[1]); 
                const cpuData = data.map(p => p.cpu);
                const ramData = data.map(p => p.ram);

                const avgCpu = cpuData.length ? (cpuData.reduce((a, b) => a + b, 0) / cpuData.length).toFixed(1) : 0;
                const avgRam = ramData.length ? (ramData.reduce((a, b) => a + b, 0) / ramData.length).toFixed(1) : 0;

                document.getElementById('cpu-avg').textContent = `Avg: ${avgCpu}%`;
                document.getElementById('ram-avg').textContent = `Avg: ${avgRam} MB`;

                if (!cpuChart24h) {
                    const ctx = document.getElementById('cpuChart24h').getContext('2d');
                    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                    gradient.addColorStop(0, 'rgba(139, 92, 246, 0.1)');
                    gradient.addColorStop(1, 'rgba(139, 92, 246, 0)');
                    
                    cpuChart24h = new Chart(ctx, {
                        type: 'line',
                        data: { labels: labels, datasets: [{ data: cpuData, borderColor: '#8b5cf6', backgroundColor: gradient, fill: true }] },
                        options: chartOptions
                    });
                } else {
                    cpuChart24h.data.labels = labels;
                    cpuChart24h.data.datasets[0].data = cpuData;
                    cpuChart24h.update();
                }

                if (!ramChart24h) {
                    const ctx = document.getElementById('ramChart24h').getContext('2d');
                    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                    gradient.addColorStop(0, 'rgba(168, 85, 247, 0.1)');
                    gradient.addColorStop(1, 'rgba(168, 85, 247, 0)');

                    ramChart24h = new Chart(ctx, {
                        type: 'line',
                        data: { labels: labels, datasets: [{ data: ramData, borderColor: '#a855f7', backgroundColor: gradient, fill: true }] },
                        options: chartOptions
                    });
                } else {
                    ramChart24h.data.labels = labels;
                    ramChart24h.data.datasets[0].data = ramData;
                    ramChart24h.update();
                }
            });
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

    function toggleFullscreen() {
        const terminal = document.getElementById('console-output').parentElement;
        const icon = document.getElementById('fullscreen-icon');
        
        if (!document.fullscreenElement) {
            terminal.requestFullscreen().catch(err => {
                // Fallback if browser blocks requestFullscreen
                terminal.classList.toggle('terminal-fullscreen');
            });
        } else {
            document.exitFullscreen();
        }
    }

    document.addEventListener('fullscreenchange', () => {
        const icon = document.getElementById('fullscreen-icon');
        if (document.fullscreenElement) {
            icon.setAttribute('data-lucide', 'minimize');
        } else {
            icon.setAttribute('data-lucide', 'maximize');
        }
        if(typeof lucide !== 'undefined') lucide.createIcons();
    });

    function clearConsole() {
        consoleOutput.innerHTML = '<div class="text-slate-500 italic opacity-50">{{ __('panel.awaiting_data') }}</div>';
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
            cmdLine.className = 'text-brand-400 mt-4 font-bold';
            cmdLine.textContent = '> ' + command;
            consoleOutput.appendChild(cmdLine);
            consoleOutput.scrollTop = consoleOutput.scrollHeight;
        });
    }

    initCharts();
    fetchLogs();
    fetchStats();
    updateCharts();
    
    logInterval = setInterval(fetchLogs, 2000);
    statsInterval = setInterval(fetchStats, 5000);
    
    if(typeof lucide !== 'undefined') lucide.createIcons();
    
    window.addEventListener('beforeunload', () => {
        if (logInterval) clearInterval(logInterval);
        if (statsInterval) clearInterval(statsInterval);
    });
</script>
@endsection
