@extends('layouts.app')

@section('header_title', 'Security Audit')

@section('content')
<div class="space-y-10 pb-20">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-8">
        <div>
            <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white uppercase tracking-[0.05em]">Security & Sessions</h2>
            <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg font-medium">Monitor real-time access attempts and terminate unauthorized communication sessions.</p>
        </div>
        <div class="flex items-center space-x-3 shrink-0">
            <div class="px-6 py-3 bg-brand-500/10 text-brand-500 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] border border-brand-500/20 shadow-sm">
                Audit Mode: ACTIVE
            </div>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Session Manager -->
        <div class="lg:col-span-1 space-y-8">
            <h3 class="text-xs font-black uppercase tracking-[0.3em] text-slate-400 flex items-center ml-2">
                <i data-lucide="key" class="w-4 h-4 mr-3 text-brand-500"></i>
                Active Communication Channels
            </h3>
            
            <div class="space-y-6">
                @foreach($sessions as $session)
                    <div class="glass dark:bg-dark-card border border-slate-200 dark:border-white/5 p-8 rounded-[2.5rem] shadow-xl relative overflow-hidden transition-all group {{ $session['current'] ? 'ring-2 ring-brand-500/50 border-brand-500/30' : '' }}">
                        <!-- Decoration -->
                        <div class="absolute -right-12 -top-12 w-32 h-32 bg-brand-500/5 rounded-full blur-2xl group-hover:bg-brand-500/10 transition-colors"></div>

                        @if($session['current'])
                            <div class="absolute top-0 right-0 px-4 py-1.5 bg-brand-500 text-white text-[9px] font-black uppercase tracking-widest rounded-bl-2xl shadow-lg">Local Uplink</div>
                        @endif
                        
                        <div class="flex items-center space-x-5 relative z-10">
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 border border-slate-100 dark:border-white/5 group-hover:scale-110 transition-transform">
                                <i data-lucide="monitor" class="w-6 h-6"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-black text-slate-900 dark:text-white truncate uppercase tracking-tight">{{ $session['user'] }}</p>
                                <p class="text-[10px] font-mono font-bold text-slate-400 mt-1 tracking-wider">{{ $session['ip'] }}</p>
                            </div>
                            @if(!$session['current'])
                                <form action="{{ route('settings.security.sessions.destroy', $session['id']) }}" method="POST" onsubmit="return confirm('CRITICAL: Terminate this session immediately?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-10 h-10 bg-red-500/10 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition-all flex items-center justify-center border border-red-500/20 active:scale-90">
                                        <i data-lucide="power" class="w-5 h-5"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div class="mt-6 pt-6 border-t border-slate-100 dark:border-white/5 flex justify-between items-center relative z-10">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Persistence</span>
                            <span class="text-[9px] font-mono font-black text-brand-500">{{ \Carbon\Carbon::createFromTimestamp($session['last_activity'])->diffForHumans() }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Audit Log -->
        <div class="lg:col-span-2 space-y-8">
            <h3 class="text-xs font-black uppercase tracking-[0.3em] text-slate-400 flex items-center ml-2">
                <i data-lucide="shield-check" class="w-4 h-4 mr-3 text-indigo-500"></i>
                Authentication History & Log
            </h3>

            <div class="glass dark:bg-dark-card rounded-[3rem] border border-slate-200 dark:border-white/5 shadow-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-white/5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-100 dark:border-white/5">
                                <th class="px-8 py-6">Protocol Event</th>
                                <th class="px-8 py-6">Source Origin</th>
                                <th class="px-8 py-6">Identity Agent</th>
                                <th class="px-8 py-6 text-right">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @forelse($logs as $log)
                                <tr class="group hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-8 py-8">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-2.5 h-2.5 rounded-full {{ str_contains(strtolower($log['action']), 'failed') ? 'bg-red-500 animate-pulse glow-red' : 'bg-green-500 glow-green' }}"></div>
                                            <div>
                                                <p class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ $log['action'] }}</p>
                                                <p class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mt-1">{{ $log['user'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-8">
                                        <code class="text-[10px] font-mono font-bold text-brand-500 bg-brand-500/10 px-3 py-1.5 rounded-xl border border-brand-500/20">{{ $log['ip'] }}</code>
                                    </td>
                                    <td class="px-8 py-8 max-w-[200px]">
                                        <p class="text-[9px] font-bold text-slate-400 truncate uppercase tracking-tighter" title="{{ $log['user_agent'] ?? 'N/A' }}">
                                            {{ $log['user_agent'] ?? 'N/A' }}
                                        </p>
                                    </td>
                                    <td class="px-8 py-8 text-right whitespace-nowrap">
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ \Carbon\Carbon::parse($log['timestamp'])->diffForHumans() }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-8 py-32 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-6 opacity-40">
                                            <div class="relative">
                                                <div class="absolute inset-0 bg-brand-500/20 blur-3xl rounded-full"></div>
                                                <div class="w-24 h-24 bg-white dark:bg-slate-900 rounded-[2.5rem] flex items-center justify-center text-5xl shadow-2xl border border-slate-100 dark:border-white/5 relative z-10">🛡️</div>
                                            </div>
                                            <div class="max-w-xs mx-auto">
                                                <p class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Perimeter Secure</p>
                                                <p class="text-sm text-slate-500 font-medium mt-2 leading-relaxed">No critical security events have been logged in this cycle.</p>
                                            </div>
                                        </div>
                                    </td>
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
