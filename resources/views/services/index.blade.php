@extends('layouts.app')

@section('header_title', 'My Services')

@section('content')
<div class="space-y-20">
    <!-- Header Section -->
    <div class="flex flex-col items-center text-center space-y-8">
        <h2 class="text-5xl font-black tracking-tight text-slate-900 dark:text-white leading-tight italic uppercase tracking-[0.05em]">Infrastructure</h2>
        
        @if(Auth::user()->role === 'admin')
        <button onclick="navigateWithAnimation('{{ route('dashboard') }}')" class="flex items-center space-x-3 px-10 py-4 rounded-[2rem] bg-slate-900 dark:bg-brand-500 text-white text-[10px] font-black uppercase tracking-[0.3em] shadow-2xl shadow-brand-500/40 hover:scale-105 active:scale-95 transition-all group">
            <i data-lucide="shield-check" class="w-4 h-4 transition-transform group-hover:rotate-12"></i>
            <span>Initialise Admin Panel</span>
        </button>
        @endif
    </div>

    <!-- Navigation Pill -->
    <div class="flex flex-col items-center space-y-6">
        <div class="relative flex items-center p-1.5 glass dark:bg-[#0f172a]/80 border-slate-200 dark:border-white/10 rounded-full shadow-2xl">
            <div id="customer-nav-indicator" class="absolute left-1.5 top-1.5 bottom-1.5 w-[calc(50%-4px)] bg-brand-500 rounded-full shadow-lg shadow-brand-500/20 transition-transform duration-500 z-0"></div>
            <button onclick="moveIndicator(0); hideSearch();" class="nav-tab-cust relative z-10 w-44 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-white transition-colors duration-300">
                <div class="flex items-center justify-center space-x-2">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                    <span>All Servers</span>
                </div>
            </button>
            <button onclick="moveIndicator(1); showSearch();" class="nav-tab-cust relative z-10 w-44 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors duration-300">
                <div class="flex items-center justify-center space-x-2">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    <span>Filter</span>
                </div>
            </button>
        </div>

        <!-- Hidden Search Bar -->
        <div id="search-container" class="max-w-md w-full opacity-0 -translate-y-4 pointer-events-none transition-all duration-500 ease-out h-0 overflow-hidden">
            <div class="relative">
                <i data-lucide="search" class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="text" id="service-search" onkeyup="filterServices()" placeholder="Search infrastructure by name..." class="w-full bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-14 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all text-white font-bold text-sm shadow-sm glass">
            </div>
        </div>
    </div>

    <!-- Customer Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-10" id="customer-grid">
        @forelse($services as $service)
            @php $isRunning = $service->getStatus() == 'running'; @endphp
            <div class="group relative service-card-wrapper" data-name="{{ strtolower($service->name) }}">
                <!-- Status Glow Effect -->
                <div class="absolute -inset-0.5 bg-gradient-to-r {{ $isRunning ? 'from-green-500/50 to-emerald-600/50' : 'from-slate-400/20 to-slate-500/20' }} rounded-[3.2rem] opacity-0 group-hover:opacity-100 blur-xl transition duration-500"></div>
                
                <div class="service-card-cust relative glass dark:bg-[#0f172a]/95 rounded-[3rem] border {{ $isRunning ? 'border-green-500/30' : 'border-slate-200 dark:border-white/10' }} p-10 hover:shadow-2xl transition-all duration-500 flex flex-col h-full overflow-hidden">
                    
                    <!-- Status Decoration -->
                    <div class="absolute -top-24 -right-24 w-64 h-64 rounded-full blur-[80px] {{ $isRunning ? 'bg-green-500/10' : 'bg-slate-500/5' }}"></div>

                    <div class="flex items-start justify-between mb-10 relative z-10">
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <div class="relative flex h-2 w-2">
                                    @if($isRunning)
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.8)]"></span>
                                    @else
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-slate-400"></span>
                                    @endif
                                </div>
                                <span class="text-[9px] font-black uppercase tracking-[0.3em] {{ $isRunning ? 'text-green-500' : 'text-slate-400' }}">{{ $service->getStatus() }}</span>
                            </div>
                            <h3 class="font-black text-3xl text-slate-900 dark:text-white truncate tracking-tight group-hover:text-brand-500 dark:group-hover:text-brand-400 transition-colors">{{ $service->name }}</h3>
                        </div>
                        <div class="px-3 py-1.5 rounded-xl bg-white/50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-[8px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em]">
                            {{ $service->type }}
                        </div>
                    </div>
                    
                    <!-- Metrics Section -->
                    <div class="space-y-6 mb-10 relative z-10">
                        <!-- CPU -->
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-[9px] font-black uppercase tracking-widest text-slate-400">
                                <div class="flex items-center space-x-2">
                                    <i data-lucide="cpu" class="w-3 h-3 text-brand-500"></i>
                                    <span>Processor Load</span>
                                </div>
                                <span id="cpu-text-{{ $service->id }}" class="text-slate-600 dark:text-slate-300">...%</span>
                            </div>
                            <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800/50 rounded-full overflow-hidden p-[1px]">
                                <div id="cpu-bar-{{ $service->id }}" class="h-full bg-gradient-to-r from-brand-500 via-brand-400 to-brand-600 rounded-full shadow-[0_0_10px_rgba(139,92,246,0.3)] transition-all duration-1000 liquid-bar" style="width: 0%"></div>
                            </div>
                        </div>
                        <!-- RAM -->
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-[9px] font-black uppercase tracking-widest text-slate-400">
                                <div class="flex items-center space-x-2">
                                    <i data-lucide="database" class="w-3 h-3 text-purple-500"></i>
                                    <span>Memory Usage</span>
                                </div>
                                <span id="ram-text-{{ $service->id }}" class="text-slate-600 dark:text-slate-300">...</span>
                            </div>
                            <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800/50 rounded-full overflow-hidden p-[1px]">
                                <div id="ram-bar-{{ $service->id }}" class="h-full bg-gradient-to-r from-purple-500 via-indigo-400 to-purple-600 rounded-full shadow-[0_0_10px_rgba(168,85,247,0.3)] transition-all duration-1000 liquid-bar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto pt-8 border-t border-slate-100 dark:border-white/5 flex items-center justify-between relative z-10">
                        <div class="flex flex-col">
                            <span class="text-[8px] font-black uppercase text-slate-400 tracking-widest mb-1">Hardware ID</span>
                            <span class="text-[10px] font-mono font-bold text-slate-600 dark:text-slate-400 tracking-tighter">{{ strtoupper(substr($service->id, 0, 8)) }}</span>
                        </div>
                        <a href="{{ route('services.show', $service->id) }}" class="flex items-center space-x-3 px-6 py-3 rounded-2xl bg-brand-500/10 dark:bg-brand-500/5 border border-brand-500/20 text-brand-600 dark:text-brand-400 text-[10px] font-black uppercase tracking-[0.2em] transition-all hover:bg-brand-500 hover:text-white hover:shadow-xl hover:shadow-brand-500/30 active:scale-95 group/btn">
                            <span>Manage</span>
                            <i data-lucide="chevron-right" class="w-4 h-4 transition-transform group-hover/btn:translate-x-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-32 text-center glass rounded-[4rem] border-dashed border-2 border-slate-200 dark:border-white/10" id="no-services-placeholder">
                <div class="w-20 h-20 bg-slate-100 dark:bg-white/5 rounded-3xl flex items-center justify-center mx-auto mb-6 text-slate-300 dark:text-slate-700">
                    <i data-lucide="server-off" class="w-10 h-10"></i>
                </div>
                <p class="text-slate-400 font-black uppercase tracking-[0.2em] text-xs">Infrastructure Offline</p>
                <p class="text-slate-500 text-sm mt-2">No active instances found in your cluster.</p>
            </div>
        @endforelse
    </div>
</div>

<script>
    function updateServiceMetrics() {
        @foreach($services as $service)
            @if($service->getStatus() == 'running')
                fetch('{{ route('metrics.service', $service->id) }}')
                    .then(res => res.json())
                    .then(data => {
                        const cpuText = document.getElementById('cpu-text-{{ $service->id }}');
                        const cpuBar = document.getElementById('cpu-bar-{{ $service->id }}');
                        const ramText = document.getElementById('ram-text-{{ $service->id }}');
                        const ramBar = document.getElementById('ram-bar-{{ $service->id }}');
                        
                        if (cpuText && cpuBar) {
                            cpuText.textContent = data.cpu + '%';
                            cpuBar.style.width = Math.min(100, parseFloat(data.cpu) || 0) + '%';
                        }
                        if (ramText && ramBar) {
                            ramText.textContent = data.ram;
                            const ramVal = parseFloat(data.ram) || 0;
                            const maxRam = 4096; 
                            const ramPercent = Math.min(100, (ramVal / maxRam) * 100);
                            ramBar.style.width = ramPercent + '%';
                        }

                        // Alarm State Trigger (> 90%)
                        const card = cpuText.closest('.service-card-cust');
                        if (parseFloat(data.cpu) > 90 || (parseFloat(data.ram) / 4096 * 100) > 90) {
                            card.classList.add('alarm-state');
                        } else {
                            card.classList.remove('alarm-state');
                        }
                    });
            @endif
        @endforeach
    }

    function moveIndicator(index) {
        const indicator = document.getElementById('customer-nav-indicator');
        const tabs = document.querySelectorAll('.nav-tab-cust');
        indicator.style.transform = `translateX(${index * 100}%)`;
        tabs.forEach((tab, i) => {
            tab.className = `nav-tab-cust relative z-10 w-44 py-3 text-[10px] font-black uppercase tracking-[0.2em] transition-colors duration-300 ${i === index ? 'text-white' : 'text-slate-500 dark:text-slate-400'}`;
        });
    }

    function showSearch() {
        const container = document.getElementById('search-container');
        container.classList.remove('opacity-0', '-translate-y-4', 'pointer-events-none', 'h-0');
        container.classList.add('opacity-100', 'translate-y-0', 'h-auto', 'mb-10');
        document.getElementById('service-search').focus();
    }

    function hideSearch() {
        const container = document.getElementById('search-container');
        container.classList.add('opacity-0', '-translate-y-4', 'pointer-events-none', 'h-0');
        container.classList.remove('opacity-100', 'translate-y-0', 'h-auto', 'mb-10');
        document.getElementById('service-search').value = '';
        filterServices();
    }

    function filterServices() {
        const query = document.getElementById('service-search').value.toLowerCase();
        const cards = document.querySelectorAll('.service-card-wrapper');
        let visibleCount = 0;

        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            if (name.includes(query)) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const emptyState = document.getElementById('no-services-placeholder');
        if (emptyState) {
            emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    setInterval(updateServiceMetrics, 5000);
    updateServiceMetrics();

    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
