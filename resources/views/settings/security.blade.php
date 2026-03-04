@extends('layouts.app')

@section('header_title', 'Security Audit')

@section('content')
<div class="space-y-10 pb-20">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Security & Sessions</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Monitor access attempts and manage active user sessions.</p>
        </div>
        <div class="flex items-center space-x-3">
            <div class="px-4 py-2 bg-brand-500/10 text-brand-500 rounded-2xl text-xs font-black uppercase tracking-widest border border-brand-500/20">
                Audit Mode Active
            </div>
        </div>
    </div>

    @if(session('status'))
        <div class="bg-green-100 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 text-green-700 dark:text-green-400 p-4 rounded-2xl flex items-center space-x-3">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span class="text-sm font-medium">{{ session('status') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Session Manager -->
        <div class="lg:col-span-1 space-y-6">
            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-400 flex items-center ml-2">
                <i data-lucide="key" class="w-3 h-3 mr-2 text-brand-500"></i>
                Active Sessions
            </h3>
            
            <div class="space-y-4">
                @foreach($sessions as $session)
                    <div class="card bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border p-6 rounded-[2rem] shadow-sm relative overflow-hidden transition-all {{ $session['current'] ? 'ring-2 ring-brand-500/50 border-brand-500/30' : '' }}">
                        @if($session['current'])
                            <div class="absolute top-0 right-0 px-3 py-1 bg-brand-500 text-white text-[8px] font-black uppercase tracking-widest rounded-bl-xl">Current</div>
                        @endif
                        
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 rounded-full bg-gray-50 dark:bg-dark-bg flex items-center justify-center text-gray-400">
                                <i data-lucide="monitor" class="w-5 h-5"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-black text-gray-900 dark:text-white truncate">{{ $session['user'] }}</p>
                                <p class="text-[10px] font-mono text-gray-400">{{ $session['ip'] }}</p>
                            </div>
                            @if(!$session['current'])
                                <form action="{{ route('settings.security.sessions.destroy', $session['id']) }}" method="POST" onsubmit="return confirm('Terminate this session?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition-all">
                                        <i data-lucide="power" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-50 dark:border-dark-border flex justify-between items-center">
                            <span class="text-[9px] font-bold text-gray-400 uppercase">Last Activity</span>
                            <span class="text-[9px] font-mono text-gray-500">{{ \Carbon\Carbon::createFromTimestamp($session['last_activity'])->diffForHumans() }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Audit Log -->
        <div class="lg:col-span-2 space-y-6">
            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-400 flex items-center ml-2">
                <i data-lucide="shield-check" class="w-3 h-3 mr-2 text-indigo-500"></i>
                Login History & Audit Log
            </h3>

            <div class="card bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-[2rem] shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-dark-hover text-[10px] font-black uppercase tracking-widest text-gray-400 border-b border-gray-200 dark:border-dark-border">
                                <th class="p-6">Event / User</th>
                                <th class="p-6">Origin (IP)</th>
                                <th class="p-6">Device Info</th>
                                <th class="p-6 text-right">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-dark-border">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-50 dark:hover:bg-[#1c2128] transition-colors group">
                                    <td class="p-6">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-2 h-2 rounded-full {{ str_contains(strtolower($log['action']), 'failed') ? 'bg-red-500 animate-pulse' : 'bg-green-500' }}"></div>
                                            <div>
                                                <p class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ $log['action'] }}</p>
                                                <p class="text-[10px] text-gray-500 dark:text-gray-400 italic">{{ $log['user'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-6">
                                        <code class="text-[10px] font-mono text-brand-500 bg-brand-50 dark:bg-brand-900/20 px-2 py-1 rounded-lg">{{ $log['ip'] }}</code>
                                    </td>
                                    <td class="p-6 max-w-[200px]">
                                        <p class="text-[10px] text-gray-400 truncate" title="{{ $log['user_agent'] ?? 'N/A' }}">
                                            {{ $log['user_agent'] ?? 'N/A' }}
                                        </p>
                                    </td>
                                    <td class="p-6 text-right">
                                        <span class="text-[10px] font-bold text-gray-400">{{ \Carbon\Carbon::parse($log['timestamp'])->diffForHumans() }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-12 text-center text-gray-500 italic">No security events recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
