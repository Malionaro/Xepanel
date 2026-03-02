@extends('layouts.app')

@section('header_title', 'Activity Logs')

@section('content')
<div class="space-y-8">
    <div>
        <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Audit Trail</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Detailed history of all actions performed within the panel.</p>
    </div>

    <div class="card bg-white dark:bg-dark-card rounded-[2rem] border border-gray-200 dark:border-dark-border overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-dark-hover text-[10px] font-black uppercase tracking-widest text-gray-400 border-b border-gray-200 dark:border-dark-border">
                    <th class="p-6">Timestamp</th>
                    <th class="p-6">User</th>
                    <th class="p-6">Action</th>
                    <th class="p-6">Details</th>
                    <th class="p-6 text-right">Source IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-dark-border">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-[#1c2128] transition-colors">
                        <td class="p-6 whitespace-nowrap">
                            <div class="flex items-center space-x-2 text-xs font-mono text-gray-500 dark:text-gray-400">
                                <i data-lucide="clock" class="w-3 h-3"></i>
                                <span>{{ $log['timestamp'] }}</span>
                            </div>
                        </td>
                        <td class="p-6">
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-[8px] font-black uppercase text-gray-600 dark:text-gray-400">
                                    {{ substr($log['user'], 0, 2) }}
                                </div>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $log['user'] }}</span>
                            </div>
                        </td>
                        <td class="p-6">
                            @php
                                $action = strtolower($log['action']);
                                $colorClass = 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 border-blue-100 dark:border-blue-900/30';
                                if(str_contains($action, 'delete') || str_contains($action, 'stop') || str_contains($action, 'crash')) {
                                    $colorClass = 'bg-red-50 dark:bg-red-900/20 text-red-600 border-red-100 dark:border-red-900/30';
                                } elseif(str_contains($action, 'start') || str_contains($action, 'create')) {
                                    $colorClass = 'bg-green-50 dark:bg-green-900/20 text-green-600 border-green-100 dark:border-green-900/30';
                                }
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest border {{ $colorClass }}">
                                {{ $log['action'] }}
                            </span>
                        </td>
                        <td class="p-6">
                            <p class="text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate" title="{{ $log['details'] }}">{{ $log['details'] }}</p>
                        </td>
                        <td class="p-6 text-right">
                            <code class="text-[10px] font-mono text-gray-400 bg-gray-100 dark:bg-dark-bg px-2 py-1 rounded-lg border border-gray-200 dark:border-dark-border">{{ $log['ip'] }}</code>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-24 text-center">
                            <div class="flex flex-col items-center justify-center space-y-4">
                                <div class="w-16 h-16 bg-gray-100 dark:bg-dark-bg rounded-full flex items-center justify-center text-3xl">📜</div>
                                <div>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">Clean Slate</p>
                                    <p class="text-sm text-gray-500 italic">No activities have been recorded yet.</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
