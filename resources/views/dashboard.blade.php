@extends('layouts.app')

@section('header_title', __('Dashboard Overview'))

@section('content')
<div class="space-y-20 pt-10">
    <!-- Breadcrumbs -->
    <div class="flex items-center p-1.5 glass dark:bg-white/5 border-slate-200 dark:border-white/10 rounded-2xl shadow-sm w-fit">
        <a href="{{ route('services.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 text-slate-500 dark:text-slate-400 hover:text-brand-500 transition-all group">
            <i data-lucide="server" class="w-4 h-4"></i>
            <span class="text-[10px] font-black uppercase tracking-widest">My Services</span>
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 dark:text-slate-600 mx-1"></i>
        <div class="flex items-center space-x-2 px-4 py-2 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-600 dark:text-brand-400">
            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
            <span class="text-[10px] font-black uppercase tracking-widest">Analytics Dashboard</span>
        </div>
    </div>

    <!-- Hero / Welcome -->
    <div class="relative overflow-hidden bg-gradient-to-br from-brand-600 to-brand-900 rounded-[3rem] p-12 text-white shadow-2xl shadow-brand-500/20">
        <!-- Abstract Decoration -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full -mr-32 -mt-32 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-brand-400/20 rounded-full -ml-32 -mb-32 blur-2xl"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-10">
            <div class="max-w-xl">
                <span class="inline-block px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/10 text-[10px] font-black uppercase tracking-[0.2em] mb-6 text-white">{{ __('System Overview') }}</span>
                <h1 class="text-5xl font-extrabold tracking-tight mb-4 leading-[1.1] text-white">{{ __('Welcome back, :name', ['name' => auth()->user()->name]) }}</h1>
                <p class="text-white/80 text-lg font-medium leading-relaxed">{{ __('Your infrastructure is performing optimally. All systems are currently operational and monitored in real-time.') }}</p>
                
                <div class="mt-10 flex items-center space-x-6">
                    <a href="{{ route('services.create') }}" class="bg-white text-brand-700 px-8 py-4 rounded-2xl font-bold text-sm hover:shadow-xl hover:-translate-y-1 transition-all active:scale-95 shadow-lg flex items-center space-x-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>{{ __('DEPLOY INSTANCE') }}</span>
                    </a>
                    <div class="flex items-center space-x-3 bg-white/10 backdrop-blur-md px-6 py-3.5 rounded-2xl border border-white/10">
                        <div class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-widest text-white">{{ count($services) }} {{ __('Active Services') }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="grid grid-cols-2 gap-4 w-full md:w-auto">
                <div class="glass p-6 rounded-[2rem] border-white/10 bg-white/5">
                    <p class="text-[10px] font-black uppercase tracking-widest text-white/60 mb-2">{{ __('Uptime') }}</p>
                    <p class="text-2xl font-black text-white">99.9%</p>
                </div>
                <div class="glass p-6 rounded-[2rem] border-white/10 bg-white/5">
                    <p class="text-[10px] font-black uppercase tracking-widest text-white/60 mb-2">{{ __('Region') }}</p>
                    <p class="text-2xl font-black text-white">EU-1</p>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->role === 'admin')
    <!-- Global Admin Overview -->
    <div id="admin-overview-container" class="space-y-8">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-black tracking-tight flex items-center space-x-3 text-slate-900 dark:text-white">
                <i data-lucide="bar-chart-3" class="w-6 h-6 text-brand-500"></i>
                <span>{{ __('Infrastructure Analytics') }}</span>
            </h2>
            <div class="flex items-center space-x-3 bg-slate-100 dark:bg-dark-card px-4 py-2 rounded-xl border border-slate-200 dark:border-dark-border">
                <span id="stats-status-dot" class="flex h-2 w-2 rounded-full bg-slate-400"></span>
                <span id="stats-status-text" class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-widest">{{ __('Loading real-time stats...') }}</span>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 opacity-50 grayscale transition-all duration-700" id="stats-grid">
            <!-- Services Status -->
            <div class="glass bg-white dark:bg-dark-card p-8 rounded-[2.5rem] border border-slate-200 dark:border-dark-border shadow-sm flex items-center space-x-6 hover-lift glow-purple">
                <div class="w-16 h-16 rounded-3xl bg-indigo-500/10 flex items-center justify-center text-indigo-500">
                    <i data-lucide="layers" class="w-8 h-8"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-[0.1em]">{{ __('Instances') }}</span>
                    <div class="flex items-baseline space-x-2">
                        <p id="stat-running" class="text-3xl font-black text-slate-900 dark:text-white">...</p>
                        <span id="stat-total" class="text-sm font-bold text-slate-400">/ ...</span>
                    </div>
                </div>
            </div>

            <!-- Total Services CPU -->
            <div class="glass bg-white dark:bg-dark-card p-8 rounded-[2.5rem] border border-slate-200 dark:border-dark-border shadow-sm hover-lift glow-purple">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-[0.1em]">{{ __('Aggregate CPU') }}</span>
                    <span id="stat-cpu-text" class="text-sm font-black text-brand-500">...%</span>
                </div>
                <div class="h-2.5 w-full bg-slate-100 dark:bg-slate-800/50 rounded-full overflow-hidden">
                    <div id="stat-cpu-bar" class="bg-gradient-to-r from-brand-500 to-brand-300 h-full transition-all duration-1000 rounded-full" style="width: 0%"></div>
                </div>
                <p class="text-[10px] text-slate-400 mt-4 font-medium">{{ __('Real-time workload') }}</p>
            </div>

            <!-- Total Services RAM -->
            <div class="glass bg-white dark:bg-dark-card p-8 rounded-[2.5rem] border border-slate-200 dark:border-dark-border shadow-sm hover-lift glow-purple">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-[0.1em]">{{ __('Global RAM') }}</span>
                    <span id="stat-ram-text" class="text-sm font-black text-purple-500">... GB</span>
                </div>
                <div class="h-2.5 w-full bg-slate-100 dark:bg-slate-800/50 rounded-full overflow-hidden">
                    <div id="stat-ram-bar" class="bg-gradient-to-r from-purple-500 to-indigo-400 h-full transition-all duration-1000 rounded-full" style="width: 0%"></div>
                </div>
                <p id="stat-ram-cap" class="text-[10px] text-slate-400 mt-4 font-medium">... utilized</p>
            </div>

            <!-- Global Availability -->
            <div class="glass bg-white dark:bg-dark-card p-8 rounded-[2.5rem] border border-slate-200 dark:border-dark-border shadow-sm flex items-center space-x-6 hover-lift">
                <div id="stat-health-icon-bg" class="w-16 h-16 rounded-3xl bg-slate-100 dark:bg-slate-800/50 flex items-center justify-center text-slate-400">
                    <i data-lucide="shield-check" class="w-8 h-8"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-[0.1em]">{{ __('Security') }}</span>
                    <p id="stat-health-text" class="text-lg font-black text-slate-400 uppercase tracking-tight">{{ __('SCANNING...') }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Host Resource Gauges -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- CPU Gauge -->
        <div class="glass dark:bg-dark-card p-10 rounded-[3rem] border border-slate-200 dark:border-dark-border shadow-sm hover-lift group">
            <div class="flex items-center justify-between mb-8">
                <div class="w-14 h-14 bg-blue-500/10 rounded-2xl flex items-center justify-center text-blue-500 group-hover:scale-110 transition-transform">
                    <i data-lucide="cpu" class="w-7 h-7"></i>
                </div>
                <div class="text-right">
                    <span id="sys-cpu" class="text-4xl font-black text-slate-900 dark:text-white">...%</span>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">{{ __('CPU LOAD') }}</p>
                </div>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div id="sys-cpu-bar" class="bg-blue-500 h-full transition-all duration-1000" style="width: 0%"></div>
            </div>
        </div>

        <!-- RAM Gauge -->
        <div class="glass dark:bg-dark-card p-10 rounded-[3rem] border border-slate-200 dark:border-dark-border shadow-sm hover-lift group">
            <div class="flex items-center justify-between mb-8">
                <div class="w-14 h-14 bg-purple-500/10 rounded-2xl flex items-center justify-center text-purple-500 group-hover:scale-110 transition-transform">
                    <i data-lucide="database" class="w-7 h-7"></i>
                </div>
                <div class="text-right">
                    <span id="sys-ram" class="text-4xl font-black text-slate-900 dark:text-white">...</span>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">{{ __('MEM USAGE') }}</p>
                </div>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div id="sys-ram-bar" class="bg-purple-500 h-full transition-all duration-1000" style="width: 0%"></div>
            </div>
        </div>

        <!-- Storage Gauge -->
        <div class="glass dark:bg-dark-card p-10 rounded-[3rem] border border-slate-200 dark:border-dark-border shadow-sm hover-lift group">
            <div class="flex items-center justify-between mb-8">
                <div class="w-14 h-14 bg-orange-500/10 rounded-2xl flex items-center justify-center text-orange-500 group-hover:scale-110 transition-transform">
                    <i data-lucide="hard-drive" class="w-7 h-7"></i>
                </div>
                <div class="text-right">
                    <span id="sys-disk" class="text-4xl font-black text-slate-900 dark:text-white">...</span>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">{{ __('DISK CAP') }}</p>
                </div>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div id="sys-disk-bar" class="bg-orange-500 h-full transition-all duration-1000" style="width: 0%"></div>
            </div>
        </div>
    </div>

    <!-- Active Services Grid -->
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">{{ __('Deployment Status') }}</h2>
                <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mt-1">{{ __('Current running instances') }}</p>
            </div>
            <div class="flex items-center space-x-3 bg-slate-100 dark:bg-dark-card p-1 rounded-xl border border-slate-200 dark:border-dark-border">
                <form action="{{ route('services.start-all') }}" method="POST">
                    @csrf
                    <button class="px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-green-500 transition-colors">{{ __('Start All') }}</button>
                </form>
                <div class="w-px h-3 bg-slate-300 dark:bg-slate-700"></div>
                <form action="{{ route('services.stop-all') }}" method="POST">
                    @csrf
                    <button class="px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-red-500 transition-colors">{{ __('Stop All') }}</button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            @forelse($services as $service)
                <a href="{{ route('services.show', $service->id) }}" 
                   class="group block relative glass dark:bg-dark-card border border-slate-200 dark:border-dark-border rounded-[2.5rem] p-10 shadow-sm hover-lift transition-all duration-500 glow-purple">
                    <div class="flex items-center justify-between mb-8">
                        <div class="w-14 h-14 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-500 group-hover:bg-brand-500 group-hover:text-white transition-all duration-500 group-hover:shadow-lg group-hover:shadow-brand-500/30">
                            <i data-lucide="{{ $service->type === 'docker' ? 'container' : 'terminal' }}" class="w-7 h-7"></i>
                        </div>
                        <div class="flex items-center space-x-3 px-3 py-1.5 rounded-full bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                            @if($service->getStatus() === 'running')
                                <span class="h-2 w-2 rounded-full bg-green-500 pulse-online"></span>
                                <span class="text-[9px] font-black uppercase text-green-600 tracking-widest">{{ __('Online') }}</span>
                            @else
                                <span class="h-2 w-2 rounded-full bg-slate-300 dark:bg-slate-700"></span>
                                <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest">{{ __('Offline') }}</span>
                            @endif
                        </div>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white mb-2 group-hover:text-brand-500 transition-colors">{{ $service->name }}</h3>
                    <p class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest mb-8">{{ $service->type }} environment</p>
                    
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($service->tags ?? [] as $tag)
                            <span class="text-[8px] font-black bg-slate-100 dark:bg-slate-900 text-slate-500 dark:text-slate-400 px-3 py-1 rounded-lg border border-slate-200 dark:border-slate-800 uppercase tracking-tighter">{{ $tag }}</span>
                        @endforeach
                    </div>
                </a>
            @empty
                <div class="col-span-full py-24 text-center glass border-2 border-dashed border-slate-200 dark:border-dark-border rounded-[3rem]">
                    <div class="w-20 h-20 bg-slate-100 dark:bg-dark-card rounded-3xl flex items-center justify-center text-slate-300 mx-auto mb-6">
                        <i data-lucide="layout-grid" class="w-10 h-10"></i>
                    </div>
                    <p class="text-slate-400 font-bold italic">{{ __('No services deployed yet.') }}</p>
                    <a href="{{ route('services.create') }}" class="inline-block mt-6 text-brand-500 font-black text-[10px] uppercase tracking-[0.2em] hover:text-brand-600 transition-colors underline underline-offset-8">Deploy your first instance</a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Feeds & Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 pb-10">
        <!-- Activity Log -->
        <div class="glass dark:bg-dark-card border border-slate-200 dark:border-dark-border rounded-[3rem] shadow-sm overflow-hidden flex flex-col">
            <div class="px-10 py-8 border-b border-slate-100 dark:border-slate-800/50 flex items-center justify-between bg-white/50 dark:bg-white/5">
                <div>
                    <h2 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-[0.2em]">{{ __('Audit Protocol') }}</h2>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ __('Recent system events') }}</p>
                </div>
                <a href="{{ route('logs.index') }}" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-brand-500 transition-colors border border-slate-200 dark:border-slate-700">
                    <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </a>
            </div>
            <div class="p-6 space-y-4">
                @foreach($latestActivities as $log)
                    <div class="flex items-center space-x-5 p-5 rounded-[1.5rem] bg-slate-50/50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 transition-all hover:border-brand-500/30">
                        <div class="w-12 h-12 rounded-2xl bg-white dark:bg-dark-card flex items-center justify-center text-slate-400 shadow-sm border border-slate-200 dark:border-slate-800">
                            <i data-lucide="activity" class="w-5 h-5"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-black text-slate-900 dark:text-white uppercase leading-none tracking-tight">{{ $log['action'] }}</p>
                            <p class="text-[10px] text-slate-500 mt-2 truncate font-medium">{{ $log['details'] }}</p>
                        </div>
                        <span class="text-[9px] font-extrabold text-slate-400 bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-full uppercase">{{ \Carbon\Carbon::parse($log['timestamp'])->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Discord Feed -->
        <div class="bg-[#5865F2] rounded-[3rem] shadow-2xl shadow-indigo-500/30 overflow-hidden flex flex-col relative group">
            <!-- Decorative Glow -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-32 -mt-32"></div>
            
            <div class="px-10 py-8 border-b border-white/10 flex items-center justify-between bg-white/5 relative z-10">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <i data-lucide="message-square" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-white uppercase tracking-[0.2em]">{{ __('Network Feed') }}</h2>
                        <p class="text-[9px] font-bold text-white/60 uppercase tracking-widest mt-1">{{ __('Real-time communications') }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2 bg-green-400/20 px-3 py-1.5 rounded-full border border-green-400/30">
                    <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                    <span class="text-[8px] font-black text-white uppercase tracking-widest">{{ __('Live Sync') }}</span>
                </div>
            </div>
            <div class="p-8 space-y-5 flex-1 relative z-10 overflow-y-auto max-h-[480px] scrollbar-hide" id="discord-feed">
                <!-- Messages injected via JS -->
                <div class="flex flex-col items-center justify-center py-20 opacity-60">
                    <div class="w-12 h-12 border-4 border-white/20 border-t-white rounded-full animate-spin mb-6"></div>
                    <p class="text-xs font-black uppercase tracking-[0.2em]">{{ __('Awaiting Feed...') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function fetchSystemMetrics() {
        fetch('{{ route('metrics.system') }}')
            .then(res => res.json())
            .then(data => {
                document.getElementById('sys-cpu').textContent = data.cpu.percent + '%';
                document.getElementById('sys-cpu-bar').style.width = data.cpu.percent + '%';
                
                document.getElementById('sys-ram').textContent = data.ram.used;
                const ramPercent = parseInt(data.ram.percent) || 0;
                document.getElementById('sys-ram-bar').style.width = ramPercent + '%';
                
                document.getElementById('sys-disk').textContent = data.disk.percent + '%';
                document.getElementById('sys-disk-bar').style.width = data.disk.percent + '%';
            });
    }

    function fetchAdminStats() {
        const container = document.getElementById('admin-overview-container');
        if (!container) return;

        fetch('{{ route('dashboard.admin-stats') }}')
            .then(res => res.json())
            .then(data => {
                // Update elements
                document.getElementById('stat-running').textContent = data.running_services;
                document.getElementById('stat-total').textContent = '/ ' + data.total_services;
                document.getElementById('stat-cpu-text').textContent = data.services_cpu + '%';
                document.getElementById('stat-cpu-bar').style.width = Math.min(100, data.services_cpu) + '%';
                document.getElementById('stat-ram-text').textContent = (data.services_ram_mb / 1024).toFixed(1) + ' GB';
                
                const hostRam = parseFloat(data.host.ram.total) * 1024;
                const ramPerc = hostRam > 0 ? (data.services_ram_mb / hostRam) * 100 : 0;
                document.getElementById('stat-ram-bar').style.width = Math.min(100, ramPerc) + '%';
                document.getElementById('stat-ram-cap').textContent = ramPerc.toFixed(1) + '% of host capacity utilized';

                // Health Status
                const healthIconBg = document.getElementById('stat-health-icon-bg');
                const healthText = document.getElementById('stat-health-text');
                healthIconBg.className = 'w-12 h-12 rounded-2xl bg-green-50 dark:bg-green-900/20 flex items-center justify-center text-green-600';
                healthText.className = 'text-lg font-black text-green-600';
                healthText.textContent = '{{ __('HEALTHY') }}';

                // Grid Status
                document.getElementById('stats-grid').classList.remove('opacity-50', 'grayscale');
                document.getElementById('stats-status-dot').className = 'flex h-2 w-2 rounded-full bg-green-500 animate-pulse';
                document.getElementById('stats-status-text').textContent = '{{ __('Global Live View') }}';
                
                if(typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(err => {
                console.error('Failed to load admin stats', err);
                document.getElementById('stats-status-text').textContent = 'Stats temporarily unavailable';
            });
    }

    setInterval(fetchSystemMetrics, 5000);
    fetchSystemMetrics();
    fetchAdminStats();

    // Discord Feed JS
    setInterval(() => {
        fetch('/discord/messages')
            .then(res => res.json())
            .then(data => {
                const feed = document.getElementById('discord-feed');
                feed.innerHTML = data.slice(0, 4).map(msg => `
                    <div class="flex items-start space-x-4 p-4 bg-white/10 rounded-3xl border border-white/5 group transition-all hover:bg-white/15">
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-xs font-black shrink-0">
                            ${msg.author.charAt(0)}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-widest opacity-70">${msg.author}</span>
                                <span class="text-[8px] font-bold opacity-40 uppercase">${new Date(msg.timestamp).toLocaleTimeString()}</span>
                            </div>
                            <p class="text-sm text-white mt-1 leading-relaxed truncate">${msg.content}</p>
                        </div>
                    </div>
                `).join('');
            });
    }, 5000);
    
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection

