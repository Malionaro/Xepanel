@extends('layouts.app')

@section('header_title', 'Dashboard Overview')

@section('content')
<div class="space-y-10">
    <!-- Hero / Welcome -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-brand-500 rounded-3xl p-8 text-white shadow-xl shadow-brand-500/20 relative overflow-hidden">
        <div class="relative z-10">
            <h2 class="text-3xl font-black tracking-tight">Welcome back, {{ Auth::user()->name }}!</h2>
            <p class="text-brand-100 mt-2 font-medium">Your system is currently <span class="bg-white/20 px-2 py-0.5 rounded text-white text-xs uppercase font-bold">{{ \App\Models\Setting::get('maintenance_mode', false) ? 'in maintenance' : 'running stable' }}</span></p>
        </div>
        <div class="flex items-center space-x-4 relative z-10">
            <a href="{{ route('services.index') }}" class="bg-white text-brand-600 px-6 py-3 rounded-2xl font-bold hover:shadow-lg transition-all active:scale-95">Manage My Services</a>
        </div>
        <!-- Abstract Shape -->
        <div class="absolute right-0 bottom-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mb-32 blur-3xl"></div>
    </div>

    <!-- Server Metrics Bar -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card bg-white dark:bg-dark-card p-6 rounded-2xl border border-gray-200 dark:border-dark-border shadow-sm hover:shadow-md transition-all">
            <div class="flex justify-between items-center mb-4">
                <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                </div>
                <span id="cpu-percent" class="text-2xl font-black text-gray-900 dark:text-white tracking-tighter">0%</span>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[0.1em] text-gray-400 mb-2">CPU Load</p>
            <div class="w-full bg-gray-100 dark:bg-dark-bg h-2 rounded-full overflow-hidden">
                <div id="cpu-bar" class="bg-brand-500 h-full transition-all duration-1000" style="width: 0%"></div>
            </div>
        </div>

        <div class="card bg-white dark:bg-dark-card p-6 rounded-2xl border border-gray-200 dark:border-dark-border shadow-sm hover:shadow-md transition-all">
            <div class="flex justify-between items-center mb-4">
                <div class="p-2 bg-purple-50 dark:bg-purple-900/20 rounded-lg text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <span id="ram-percent" class="text-2xl font-black text-gray-900 dark:text-white tracking-tighter">0%</span>
            </div>
            <div class="flex justify-between items-baseline mb-2">
                <p class="text-[10px] font-black uppercase tracking-[0.1em] text-gray-400">Memory Usage</p>
                <span id="ram-info" class="text-[10px] font-mono text-gray-500">0 / 0 GB</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-dark-bg h-2 rounded-full overflow-hidden">
                <div id="ram-bar" class="bg-purple-500 h-full transition-all duration-1000" style="width: 0%"></div>
            </div>
        </div>

        <div class="card bg-white dark:bg-dark-card p-6 rounded-2xl border border-gray-200 dark:border-dark-border shadow-sm hover:shadow-md transition-all">
            <div class="flex justify-between items-center mb-4">
                <div class="p-2 bg-green-50 dark:bg-green-900/20 rounded-lg text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                </div>
                <span id="disk-percent" class="text-2xl font-black text-gray-900 dark:text-white tracking-tighter">0%</span>
            </div>
            <div class="flex justify-between items-baseline mb-2">
                <p class="text-[10px] font-black uppercase tracking-[0.1em] text-gray-400">Disk Space</p>
                <span id="disk-info" class="text-[10px] font-mono text-gray-500">0 / 0 GB</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-dark-bg h-2 rounded-full overflow-hidden">
                <div id="disk-bar" class="bg-green-500 h-full transition-all duration-1000" style="width: 0%"></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Activity Widget -->
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-extrabold tracking-tight">System Events</h2>
                @if(Auth::user()->role === 'admin')
                <a href="{{ route('logs.index') }}" class="text-xs font-bold text-brand-500 uppercase tracking-widest hover:text-brand-600 transition-colors">History</a>
                @endif
            </div>
            <div class="card bg-white dark:bg-dark-card rounded-2xl border border-gray-200 dark:border-dark-border overflow-hidden transition-all duration-300">
                <div class="p-6 space-y-6">
                    @forelse($latestActivities as $activity)
                        <div class="flex items-start space-x-4">
                            <div class="mt-1 flex-shrink-0 w-8 h-8 rounded-xl flex items-center justify-center border {{ str_contains(strtolower($activity['action']), 'delete') || str_contains(strtolower($activity['action']), 'stop') || str_contains(strtolower($activity['action']), 'crash') ? 'bg-red-50 text-red-500 border-red-100 dark:bg-red-900/20 dark:border-red-900/30' : (str_contains(strtolower($activity['action']), 'start') || str_contains(strtolower($activity['action']), 'create') ? 'bg-green-50 text-green-500 border-green-100 dark:bg-green-900/20 dark:border-green-900/30' : 'bg-blue-50 text-blue-500 border-blue-100 dark:bg-blue-900/20 dark:border-blue-900/30') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $activity['action'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">{{ $activity['details'] }}</p>
                                <div class="flex items-center mt-2 space-x-3">
                                    <span class="text-[10px] font-black uppercase text-gray-400">{{ $activity['user'] }}</span>
                                    <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                                    <span class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($activity['timestamp'])->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 dark:text-gray-500 text-sm py-4 italic">No recent activity detected.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Discord Feed Section -->
        <div class="space-y-6">
            <div class="flex items-center space-x-3">
                <h2 class="text-2xl font-extrabold tracking-tight">Discord Bridge</h2>
                <span class="bg-blue-500 text-[10px] font-black uppercase px-2 py-0.5 rounded text-white tracking-widest">Live</span>
            </div>
            <div id="discord-feed" class="card bg-white dark:bg-dark-card rounded-2xl border border-gray-200 dark:border-dark-border overflow-hidden flex flex-col h-[400px] transition-all duration-300">
                <div id="discord-messages" class="flex-1 p-6 overflow-y-auto space-y-6">
                    @forelse($discordMessages as $msg)
                        <div class="flex space-x-4">
                            <img src="{{ $msg['avatar'] ?? 'https://cdn.discordapp.com/embed/avatars/0.png' }}" class="w-10 h-10 rounded-xl shadow-sm" alt="">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-sm text-gray-900 dark:text-white">{{ $msg['user'] }}</span>
                                    <span class="text-[10px] text-gray-400 uppercase font-medium">{{ \Carbon\Carbon::parse($msg['timestamp'])->format('H:i') }}</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 leading-relaxed">{{ $msg['content'] }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full space-y-3">
                            <div class="w-12 h-12 bg-gray-100 dark:bg-dark-bg rounded-full flex items-center justify-center text-2xl">💬</div>
                            <p class="text-gray-500 dark:text-gray-500 text-sm italic">Waiting for communications...</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateMetrics() {
        fetch('{{ route('metrics.system') }}')
            .then(res => res.json())
            .then(data => {
                document.getElementById('cpu-percent').textContent = data.cpu.percent + '%';
                document.getElementById('cpu-bar').style.width = data.cpu.percent + '%';
                
                document.getElementById('ram-percent').textContent = data.ram.percent + '%';
                document.getElementById('ram-bar').style.width = data.ram.percent + '%';
                document.getElementById('ram-info').textContent = data.ram.used + ' / ' + data.ram.total;

                document.getElementById('disk-percent').textContent = data.disk.percent + '%';
                document.getElementById('disk-bar').style.width = data.disk.percent + '%';
                document.getElementById('disk-info').textContent = data.disk.used + ' / ' + data.disk.total;
            });
    }

    setInterval(updateMetrics, 5000);
    updateMetrics();

    // Auto-refresh discord feed
    setInterval(() => {
        fetch('/discord/messages')
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('discord-messages');
                if (data.length === 0) return;
                container.innerHTML = data.map(msg => `
                    <div class="flex space-x-4">
                        <img src="${msg.avatar || 'https://cdn.discordapp.com/embed/avatars/0.png'}" class="w-10 h-10 rounded-xl shadow-sm" alt="">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-sm text-gray-900 dark:text-white">${msg.user}</span>
                                <span class="text-[10px] text-gray-400 uppercase font-medium">${new Date(msg.timestamp).getHours()}:${String(new Date(msg.timestamp).getMinutes()).padStart(2, '0')}</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 leading-relaxed">${msg.content}</p>
                        </div>
                    </div>
                `).join('');
            });
    }, 5000);
</script>
@endsection
