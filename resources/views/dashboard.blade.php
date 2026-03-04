@extends('layouts.app')

@section('header_title', __('Dashboard Overview'))

@section('content')
<div class="space-y-10">
    <!-- Hero / Welcome -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-brand-500 rounded-[2.5rem] p-10 text-white relative overflow-hidden shadow-2xl shadow-brand-500/20">
        <div class="relative z-10">
            <h1 class="text-4xl font-black tracking-tight mb-2 uppercase">{{ __('Welcome, :name!', ['name' => auth()->user()->name]) }}</h1>
            <p class="text-brand-100 font-bold opacity-90 max-w-md">{{ __('Manage your services, monitor performance, and control your infrastructure from one central hub.') }}</p>
            <div class="mt-8 flex items-center space-x-4">
                <a href="{{ route('services.create') }}" class="bg-white text-brand-600 px-6 py-3 rounded-2xl font-black text-sm hover:bg-brand-50 transition-all active:scale-95 shadow-lg">{{ __('CREATE SERVICE') }}</a>
                <div class="h-10 w-px bg-white/20"></div>
                <div class="flex items-center space-x-2">
                    <span class="flex h-3 w-3 rounded-full bg-green-400 animate-pulse"></span>
                    <span class="text-xs font-black uppercase tracking-widest">{{ count($services) }} {{ __('Instances') }}</span>
                </div>
            </div>
        </div>
        <!-- Abstract Shape -->
        <div class="absolute right-0 bottom-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mb-32 blur-3xl"></div>
    </div>

    @if(auth()->user()->role === 'admin')
    <!-- Global Admin Overview (AJAX Loaded) -->
    <div id="admin-overview-container" class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-extrabold tracking-tight">{{ __('Global Resource Overview') }}</h2>
            <div class="flex items-center space-x-2">
                <span id="stats-status-dot" class="flex h-2 w-2 rounded-full bg-gray-400"></span>
                <span id="stats-status-text" class="text-[10px] font-black uppercase text-gray-400 tracking-widest animate-shimmer">{{ __('Loading Stats...') }}</span>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 opacity-50 grayscale transition-all duration-700" id="stats-grid">
            <!-- Services Status -->
            <div class="card bg-white dark:bg-dark-card p-6 rounded-3xl border border-gray-200 dark:border-dark-border shadow-sm flex items-center space-x-4 hover-lift">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600">
                    <i data-lucide="layers" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('Instance Status') }}</span>
                    <div class="flex items-baseline space-x-2">
                        <p id="stat-running" class="text-2xl font-black text-gray-900 dark:text-white">...</p>
                        <span id="stat-total" class="text-xs font-bold text-gray-400">/ ...</span>
                    </div>
                </div>
            </div>

            <!-- Total Services CPU -->
            <div class="card bg-white dark:bg-dark-card p-6 rounded-3xl border border-gray-200 dark:border-dark-border shadow-sm hover-lift">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('Services CPU') }}</span>
                    <span id="stat-cpu-text" class="text-xs font-mono font-black text-brand-500">...%</span>
                </div>
                <div class="h-2 w-full bg-gray-100 dark:bg-dark-bg rounded-full overflow-hidden">
                    <div id="stat-cpu-bar" class="bg-brand-500 h-full transition-all duration-1000" style="width: 0%"></div>
                </div>
                <p class="text-[9px] text-gray-400 mt-2 italic">Global load across all cores</p>
            </div>

            <!-- Total Services RAM -->
            <div class="card bg-white dark:bg-dark-card p-6 rounded-3xl border border-gray-200 dark:border-dark-border shadow-sm hover-lift">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('Services RAM') }}</span>
                    <span id="stat-ram-text" class="text-xs font-mono font-black text-purple-500">... GB</span>
                </div>
                <div class="h-2 w-full bg-gray-100 dark:bg-dark-bg rounded-full overflow-hidden">
                    <div id="stat-ram-bar" class="bg-purple-500 h-full transition-all duration-1000" style="width: 0%"></div>
                </div>
                <p id="stat-ram-cap" class="text-[9px] text-gray-400 mt-2 italic">... of host utilized</p>
            </div>

            <!-- Global Availability -->
            <div class="card bg-white dark:bg-dark-card p-6 rounded-3xl border border-gray-200 dark:border-dark-border shadow-sm flex items-center space-x-4 hover-lift">
                <div id="stat-health-icon-bg" class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-dark-bg flex items-center justify-center text-gray-400">
                    <i data-lucide="shield" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('System Health') }}</span>
                    <p id="stat-health-text" class="text-lg font-black text-gray-400 italic text-xs uppercase">{{ __('AWAITING DATA') }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Server Metrics Bar -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- CPU Card -->
        <div class="card bg-white dark:bg-dark-card p-6 rounded-3xl border border-gray-200 dark:border-dark-border shadow-sm hover-lift transition-all">
            <div class="flex justify-between items-center mb-4">
                <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-blue-600">
                    <i data-lucide="cpu" class="w-5 h-5"></i>
                </div>
                <span id="sys-cpu" class="text-xl font-black text-gray-900 dark:text-white">...%</span>
            </div>
            <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('Processor Load') }}</p>
        </div>

        <!-- RAM Card -->
        <div class="card bg-white dark:bg-dark-card p-6 rounded-3xl border border-gray-200 dark:border-dark-border shadow-sm hover-lift transition-all">
            <div class="flex justify-between items-center mb-4">
                <div class="p-2 bg-purple-50 dark:bg-purple-900/20 rounded-lg text-purple-600">
                    <i data-lucide="database" class="w-5 h-5"></i>
                </div>
                <span id="sys-ram" class="text-xl font-black text-gray-900 dark:text-white">...</span>
            </div>
            <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('Memory Usage') }}</p>
        </div>

        <!-- Storage Card -->
        <div class="card bg-white dark:bg-dark-card p-6 rounded-3xl border border-gray-200 dark:border-dark-border shadow-sm hover-lift transition-all">
            <div class="flex justify-between items-center mb-4">
                <div class="p-2 bg-orange-50 dark:bg-orange-900/20 rounded-lg text-orange-600">
                    <i data-lucide="hard-drive" class="w-5 h-5"></i>
                </div>
                <span id="sys-disk" class="text-xl font-black text-gray-900 dark:text-white">...</span>
            </div>
            <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('Disk Activity') }}</p>
        </div>
    </div>

    <!-- Active Services Grid -->
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-extrabold tracking-tight">{{ __('Active Services') }}</h2>
            <div class="flex items-center space-x-4">
                <form action="{{ route('services.start-all') }}" method="POST">
                    @csrf
                    <button class="text-[10px] font-black uppercase text-gray-400 hover:text-green-500 transition-colors tracking-widest active-press">{{ __('Start All') }}</button>
                </form>
                <span class="text-gray-300">/</span>
                <form action="{{ route('services.stop-all') }}" method="POST">
                    @csrf
                    <button class="text-[10px] font-black uppercase text-gray-400 hover:text-red-500 transition-colors tracking-widest active-press">{{ __('Stop All') }}</button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($services as $service)
                <a href="{{ route('services.show', $service->id) }}" 
                   class="group block relative bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-[2.5rem] p-8 shadow-sm hover-lift transition-all duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-900/10 flex items-center justify-center text-brand-500 group-hover:bg-brand-500 group-hover:text-white transition-all duration-500">
                            <i data-lucide="{{ $service->type === 'docker' ? 'container' : 'cpu' }}" class="w-6 h-6"></i>
                        </div>
                        <div class="flex items-center space-x-2">
                            @if($service->getStatus() === 'running')
                                <span class="h-2 w-2 rounded-full bg-green-500 pulse-online shadow-[0_0_8px_rgba(34,197,94,0.5)]"></span>
                                <span class="text-[10px] font-black uppercase text-green-600 tracking-widest">{{ __('Online') }}</span>
                            @else
                                <span class="h-2 w-2 rounded-full bg-gray-300"></span>
                                <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('Offline') }}</span>
                            @endif
                        </div>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white mb-1 group-hover:text-brand-500 transition-colors">{{ $service->name }}</h3>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-6">{{ $service->type }} instance</p>
                    
                    <div class="flex items-center space-x-2 opacity-60 group-hover:opacity-100 transition-opacity">
                        @foreach($service->tags ?? [] as $tag)
                            <span class="text-[9px] font-black bg-gray-50 dark:bg-dark-bg text-gray-500 px-2.5 py-1 rounded-lg border border-gray-100 dark:border-dark-border uppercase">{{ $tag }}</span>
                        @endforeach
                    </div>
                </a>
            @empty
                <div class="col-span-full py-20 text-center border-2 border-dashed border-gray-100 dark:border-dark-border rounded-[3rem]">
                    <i data-lucide="layout-grid" class="w-12 h-12 text-gray-200 mx-auto mb-4"></i>
                    <p class="text-gray-400 font-bold italic">{{ __('No services registered yet.') }}</p>
                    <a href="{{ route('services.create') }}" class="inline-block mt-4 text-brand-500 font-black text-xs uppercase tracking-widest hover:underline">Deploy your first instance</a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Feeds & Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Activity Log -->
        <div class="card bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-[2.5rem] shadow-sm overflow-hidden flex flex-col">
            <div class="px-8 py-6 border-b border-gray-50 dark:border-dark-border flex items-center justify-between bg-gray-50/50 dark:bg-dark-hover">
                <h2 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">{{ __('Audit Protocol') }}</h2>
                <a href="{{ route('logs.index') }}" class="text-[10px] font-black text-brand-500 uppercase tracking-widest hover:underline">{{ __('View All') }}</a>
            </div>
            <div class="p-4 space-y-4">
                @foreach($latestActivities as $log)
                    <div class="flex items-center space-x-4 p-4 rounded-2xl bg-gray-50 dark:bg-dark-bg border border-gray-100 dark:border-dark-border">
                        <div class="w-8 h-8 rounded-xl bg-white dark:bg-dark-card flex items-center justify-center text-gray-400">
                            <i data-lucide="activity" class="w-4 h-4"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-[11px] font-black text-gray-900 dark:text-white uppercase leading-none">{{ $log['action'] }}</p>
                            <p class="text-[10px] text-gray-500 mt-1 truncate">{{ $log['details'] }}</p>
                        </div>
                        <span class="text-[9px] font-bold text-gray-400">{{ \Carbon\Carbon::parse($log['timestamp'])->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Discord Feed -->
        <div class="card bg-[#5865F2] text-white rounded-[2.5rem] shadow-xl shadow-indigo-500/20 overflow-hidden flex flex-col">
            <div class="px-8 py-6 border-b border-white/10 flex items-center justify-between bg-white/5">
                <div class="flex items-center space-x-3">
                    <i data-lucide="message-square" class="w-5 h-5"></i>
                    <h2 class="text-sm font-black uppercase tracking-widest">{{ __('External Notifications') }}</h2>
                </div>
                <span class="text-[9px] font-black bg-white/20 px-2 py-1 rounded uppercase tracking-widest">{{ __('Live Feed') }}</span>
            </div>
            <div class="p-6 space-y-4 flex-1" id="discord-feed">
                <!-- Messages injected via JS -->
                <div class="py-10 text-center opacity-50 italic text-sm">Synchronizing external data...</div>
            </div>
        </div>
    </div>
</div>

<script>
    function fetchSystemMetrics() {
        fetch('{{ route('metrics.system') }}')
            .then(res => res.json())
            .then(data => {
                document.getElementById('sys-cpu').textContent = data.cpu.usage + '%';
                document.getElementById('sys-ram').textContent = data.ram.used;
                document.getElementById('sys-disk').textContent = data.disk.usage + '%';
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

