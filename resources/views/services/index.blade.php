@extends('layouts.app')

@section('header_title', 'My Services')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Active Instances</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Monitor, control and manage your deployed applications.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if(count($services) > 0)
                <div class="flex items-center bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border p-1 rounded-2xl shadow-sm">
                    <form action="{{ route('services.start-all') }}" method="POST" class="inline">
                        @csrf
                        <button class="flex items-center space-x-2 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition-all">
                            <i data-lucide="play" class="w-3.5 h-3.5"></i>
                            <span>Start All</span>
                        </button>
                    </form>
                    <div class="w-px h-4 bg-gray-200 dark:bg-dark-border mx-1"></div>
                    <form action="{{ route('services.stop-all') }}" method="POST" class="inline">
                        @csrf
                        <button class="flex items-center space-x-2 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                            <i data-lucide="square" class="w-3.5 h-3.5"></i>
                            <span>Stop All</span>
                        </button>
                    </form>
                </div>
            @endif
            @if(Auth::user()->role === 'admin')
            <div class="flex items-center space-x-3">
                <a href="{{ route('services.import') }}" class="flex items-center space-x-2 bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border text-gray-700 dark:text-gray-300 px-5 py-2.5 rounded-2xl text-sm font-bold transition-all hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0">
                    <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                    <span>Import</span>
                </a>
                <a href="{{ route('services.create') }}" class="flex items-center space-x-2 bg-brand-500 hover:bg-brand-600 text-white px-6 py-2.5 rounded-2xl text-sm font-bold shadow-lg shadow-brand-500/25 transition-all hover:-translate-y-0.5 active:translate-y-0">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Add Service</span>
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Tag Filter Bar -->
    @php
        $allTags = [];
        foreach($services as $s) {
            if(!empty($s->tags)) $allTags = array_merge($allTags, $s->tags);
        }
        $uniqueTags = array_unique($allTags);
        asort($uniqueTags);
    @endphp
    
    @if(count($uniqueTags) > 0)
    <div class="flex items-center space-x-2 overflow-x-auto pb-2 scrollbar-hide">
        <button onclick="filterByTag('all')" class="tag-filter-btn whitespace-nowrap px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest bg-brand-500 text-white shadow-md transition-all border border-transparent" data-tag="all">All</button>
        @foreach($uniqueTags as $tag)
            <button onclick="filterByTag('{{ $tag }}')" class="tag-filter-btn whitespace-nowrap px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest bg-white dark:bg-dark-card text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-dark-border hover:border-brand-500 hover:text-brand-500 transition-all" data-tag="{{ $tag }}">{{ $tag }}</button>
        @endforeach
    </div>
    @endif

    <div id="services-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
        @forelse($services as $service)
            @php $isRunning = $service->getStatus() == 'running'; @endphp
            <div class="service-card group bg-white dark:bg-dark-card rounded-[2rem] border border-gray-200 dark:border-dark-border p-8 hover:shadow-2xl hover:border-brand-500/30 transition-all duration-500 relative overflow-hidden flex flex-col h-full" data-tags="{{ !empty($service->tags) ? implode(',', $service->tags) : '' }}">
                
                <!-- Status Background Glow -->
                <div class="absolute -top-24 -right-24 w-64 h-64 rounded-full blur-[80px] opacity-10 transition-colors duration-700 {{ $isRunning ? 'bg-green-500' : 'bg-red-500' }}"></div>

                <div class="flex items-start justify-between relative z-10">
                    <div class="flex-1 min-w-0 pr-4">
                        <div class="flex items-center space-x-2 mb-1">
                            <h3 class="font-extrabold text-2xl text-gray-900 dark:text-white truncate tracking-tight group-hover:text-brand-500 transition-colors">{{ $service->name }}</h3>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $isRunning ? 'bg-green-50 dark:bg-green-900/20 text-green-600 border border-green-100 dark:border-green-900/30' : 'bg-red-50 dark:bg-red-900/20 text-red-600 border border-red-100 dark:border-red-900/30' }}">
                                <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $isRunning ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}"></span>
                                {{ $service->getStatus() }}
                            </span>
                            
                            @php
                                $displayIp = request()->getHost();
                                $displayPort = '';
                                if ($service->type === 'docker' && !empty($service->docker_ports)) {
                                    // Extract the first host port (e.g. "25565:25565" -> "25565")
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
                    
                    <form action="{{ $isRunning ? route('services.stop', $service->id) : route('services.start', $service->id) }}" method="POST" class="shrink-0">
                        @csrf
                        <button class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-300 shadow-lg {{ $isRunning ? 'bg-red-50 text-red-500 hover:bg-red-500 hover:text-white shadow-red-500/10' : 'bg-green-50 text-green-500 hover:bg-green-500 hover:text-white shadow-green-500/10' }}">
                            <i data-lucide="{{ $isRunning ? 'square' : 'play' }}" class="w-5 h-5 fill-current"></i>
                        </button>
                    </form>
                </div>

                @if(!empty($service->tags))
                <div class="flex flex-wrap gap-1.5 mt-5 relative z-10">
                    @foreach($service->tags as $tag)
                        <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-gray-50 dark:bg-[#1c2128] text-gray-400 dark:text-gray-500 border border-gray-100 dark:border-dark-border">{{ $tag }}</span>
                    @endforeach
                </div>
                @endif

                <div class="mt-8 grid grid-cols-2 gap-4 relative z-10">
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-[#1c2128] border border-gray-100 dark:border-dark-border">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">CPU Load</p>
                        <div class="flex items-baseline space-x-1">
                            <span id="cpu-{{ $service->id }}" class="text-lg font-black text-brand-500">...</span>
                            <span class="text-[10px] text-gray-400 font-bold">%</span>
                        </div>
                    </div>
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-[#1c2128] border border-gray-100 dark:border-dark-border">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Memory</p>
                        <div class="flex items-baseline">
                            <span id="ram-{{ $service->id }}" class="text-lg font-black text-purple-500 truncate">...</span>
                        </div>
                    </div>
                </div>

                <div class="mt-auto pt-8 flex items-center justify-between relative z-10">
                    <div class="flex items-center text-gray-400 dark:text-gray-500">
                        <i data-lucide="folder" class="w-3.5 h-3.5 mr-1.5"></i>
                        <span class="text-[10px] font-mono truncate max-w-[120px]">{{ basename($service->working_dir) }}</span>
                    </div>
                    <a href="{{ route('services.show', $service->id) }}" class="flex items-center space-x-2 text-xs font-black text-brand-500 hover:text-brand-600 uppercase tracking-[0.1em] transition-all group/link">
                        <span>Details</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover/link:translate-x-1"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-24 flex flex-col items-center justify-center text-center space-y-6">
                <div class="w-24 h-24 bg-white dark:bg-dark-card rounded-3xl flex items-center justify-center text-5xl shadow-xl border border-gray-100 dark:border-dark-border">📂</div>
                <div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white">Empty Stack</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto mt-2">No services are currently configured. Deploy a new instance to get started.</p>
                </div>
                @if(Auth::user()->role === 'admin')
                <a href="{{ route('services.create') }}" class="bg-brand-500 text-white px-8 py-3 rounded-2xl font-bold shadow-xl shadow-brand-500/30 hover:scale-105 active:scale-95 transition-all">Add Your First Service</a>
                @endif
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
                        const cpuEl = document.getElementById('cpu-{{ $service->id }}');
                        const ramEl = document.getElementById('ram-{{ $service->id }}');
                        if (cpuEl && ramEl) {
                            cpuEl.textContent = data.cpu;
                            ramEl.textContent = data.ram;
                        }
                    });
            @endif
        @endforeach
    }

    function filterByTag(tag) {
        const cards = document.querySelectorAll('.service-card');
        const buttons = document.querySelectorAll('.tag-filter-btn');
        
        buttons.forEach(btn => {
            if (btn.getAttribute('data-tag') === tag) {
                btn.classList.add('bg-brand-500', 'text-white', 'shadow-md');
                btn.classList.remove('bg-white', 'dark:bg-dark-card', 'text-gray-500', 'dark:text-gray-400');
            } else {
                btn.classList.remove('bg-brand-500', 'text-white', 'shadow-md');
                btn.classList.add('bg-white', 'dark:bg-dark-card', 'text-gray-500', 'dark:text-gray-400');
            }
        });

        cards.forEach(card => {
            if (tag === 'all') {
                card.classList.remove('hidden');
            } else {
                const cardTags = card.getAttribute('data-tags').split(',');
                if (cardTags.includes(tag)) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            }
        });
    }

    setInterval(updateServiceMetrics, 5000);
    updateServiceMetrics();
    
    // Initializing icons again just in case for dynamic content
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
