@extends('layouts.app')

@section('header_title', 'Network Monitor')

@section('content')
<div class="space-y-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-8">
        <div>
            <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white uppercase italic tracking-[0.05em]">Netzwerk</h2>
            <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg font-medium">Real-time monitoring of all active listeners and communication ports on this node.</p>
        </div>
        <div class="flex items-center space-x-3 shrink-0">
            <div class="flex items-center space-x-3 bg-brand-500/10 border border-brand-500/20 px-6 py-3 rounded-2xl shadow-sm">
                <span class="flex h-2.5 w-2.5 rounded-full bg-green-500 animate-pulse glow-green"></span>
                <span class="text-[10px] font-black uppercase text-brand-500 tracking-[0.2em]">Protocol Live</span>
            </div>
        </div>
    </div>

    <div class="glass dark:bg-dark-card rounded-[3rem] border border-slate-200 dark:border-white/5 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-white/5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-100 dark:border-white/5">
                        <th class="px-10 py-6">Protocol Type</th>
                        <th class="px-10 py-6">Port Assignment</th>
                        <th class="px-10 py-6">Local Interface</th>
                        <th class="px-10 py-6">Identity (PID)</th>
                        <th class="px-10 py-6 text-right">Infrastructure Link</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse($ports as $port)
                        <tr class="group hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-10 py-8">
                                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-[0.2em] {{ $port['protocol'] == 'TCP' ? 'bg-blue-500/10 text-blue-500 border border-blue-500/20 shadow-[0_0_15px_rgba(59,130,246,0.1)]' : 'bg-yellow-500/10 text-yellow-500 border border-yellow-500/20 shadow-[0_0_15px_rgba(234,179,8,0.1)]' }}">
                                    {{ $port['protocol'] }}
                                </span>
                            </td>
                            <td class="px-10 py-8">
                                <span class="text-xl font-black text-slate-900 dark:text-white tracking-tighter">{{ $port['port'] }}</span>
                            </td>
                            <td class="px-10 py-8">
                                <code class="text-xs font-mono font-bold text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-white/5 px-3 py-1.5 rounded-xl border border-slate-100 dark:border-white/5">
                                    {{ $port['address'] }}
                                </code>
                            </td>
                            <td class="px-10 py-8">
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ $port['process'] }}</span>
                                    <span class="text-[9px] font-mono font-bold text-slate-400 uppercase mt-1 tracking-widest">PID: {{ $port['pid'] }}</span>
                                </div>
                            </td>
                            <td class="px-10 py-8 text-right">
                                @if($port['service'])
                                    <a href="{{ route('services.show', $port['service']->id) }}" class="inline-flex items-center space-x-3 bg-brand-500/10 hover:bg-brand-500 text-brand-500 hover:text-white px-6 py-2.5 rounded-2xl border border-brand-500/20 transition-all group/link shadow-sm">
                                        <i data-lucide="terminal" class="w-4 h-4 transition-transform group-hover/link:scale-110"></i>
                                        <span class="text-[10px] font-black uppercase tracking-[0.15em]">{{ $port['service']->name }}</span>
                                    </a>
                                @else
                                    <div class="inline-flex items-center space-x-2 text-[9px] font-black uppercase text-slate-400 tracking-[0.2em] italic opacity-60">
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                        <span>System Protocol</span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-10 py-32 text-center">
                                <div class="flex flex-col items-center justify-center space-y-6 opacity-40">
                                    <div class="relative">
                                        <div class="absolute inset-0 bg-brand-500/20 blur-3xl rounded-full"></div>
                                        <div class="w-24 h-24 bg-white dark:bg-slate-900 rounded-[2.5rem] flex items-center justify-center text-5xl shadow-2xl border border-slate-100 dark:border-white/5 relative z-10">🌐</div>
                                    </div>
                                    <div class="max-w-xs mx-auto">
                                        <p class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">No Listeners Detected</p>
                                        <p class="text-sm text-slate-500 font-medium mt-2 leading-relaxed">External communication channels are currently isolated or inactive.</p>
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

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
