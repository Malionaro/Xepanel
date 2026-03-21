@extends('layouts.app')

@section('header_title', __('panel.activity_logs'))

@section('content')
<div class="space-y-10">
    <div>
        <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white uppercase tracking-[0.05em]">{{ __('panel.audit_trail') }}</h2>
        <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg font-medium">{{ __('panel.audit_trail_desc') }}</p>
    </div>

    <div class="glass dark:bg-dark-card rounded-[3rem] border border-slate-200 dark:border-white/5 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[1000px]">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-white/5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-100 dark:border-white/5">
                        <th class="px-10 py-6">{{ __('panel.operation_timestamp') }}</th>
                        <th class="px-10 py-6">{{ __('panel.identity') }}</th>
                        <th class="px-10 py-6">{{ __('panel.protocol_action') }}</th>
                        <th class="px-10 py-6">{{ __('panel.diagnostic_details') }}</th>
                        <th class="px-10 py-6 text-right">{{ __('panel.source_origin') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse($logs as $log)
                        <tr class="group hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-10 py-8 whitespace-nowrap">
                                <div class="flex items-center space-x-3 text-xs font-mono font-bold text-slate-500 dark:text-slate-400">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-400">
                                        <i data-lucide="clock" class="w-4 h-4"></i>
                                    </div>
                                    <span>{{ $log['timestamp'] }}</span>
                                </div>
                            </td>
                            <td class="px-10 py-8">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 rounded-full bg-brand-500 flex items-center justify-center text-[10px] font-black uppercase text-white shadow-lg shadow-brand-500/20">
                                        {{ substr($log['user'], 0, 2) }}
                                    </div>
                                    <span class="text-sm font-black text-slate-900 dark:text-white tracking-tight">{{ $log['user'] }}</span>
                                </div>
                            </td>
                            <td class="px-10 py-8">
                                @php
                                    $action = strtolower($log['action']);
                                    $colorClass = 'bg-blue-500/10 text-blue-500 border-blue-500/20 shadow-[0_0_15px_rgba(59,130,246,0.1)]';
                                    if(str_contains($action, 'delete') || str_contains($action, 'stop') || str_contains($action, 'crash')) {
                                        $colorClass = 'bg-red-500/10 text-red-500 border-red-500/20 shadow-[0_0_15px_rgba(239,68,68,0.1)]';
                                    } elseif(str_contains($action, 'start') || str_contains($action, 'create')) {
                                        $colorClass = 'bg-green-500/10 text-green-500 border-green-500/20 shadow-[0_0_15px_rgba(34,197,94,0.1)]';
                                    }
                                @endphp
                                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-[0.2em] border {{ $colorClass }}">
                                    {{ $log['action'] }}
                                </span>
                            </td>
                            <td class="px-10 py-8">
                                <p class="text-xs font-bold text-slate-600 dark:text-slate-400 max-w-xs truncate leading-relaxed" title="{{ $log['details'] }}">{{ $log['details'] }}</p>
                            </td>
                            <td class="px-10 py-8 text-right">
                                <code class="text-[10px] font-mono font-bold text-slate-400 bg-slate-100 dark:bg-white/5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm">
                                    {{ $log['ip'] }}
                                </code>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-10 py-32 text-center">
                                <div class="flex flex-col items-center justify-center space-y-6 opacity-40">
                                    <div class="relative">
                                        <div class="absolute inset-0 bg-brand-500/20 blur-3xl rounded-full"></div>
                                        <div class="w-24 h-24 bg-white dark:bg-slate-900 rounded-[2.5rem] flex items-center justify-center text-5xl shadow-2xl border border-slate-100 dark:border-white/5 relative z-10">📄</div>
                                    </div>
                                    <div class="max-w-xs mx-auto">
                                        <p class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ __('panel.system_slate_clean') }}</p>
                                        <p class="text-sm text-slate-500 font-medium mt-2 leading-relaxed">{{ __('panel.no_logs_desc') }}</p>
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
