@extends('layouts.app')

@section('header_title', 'Network Monitor')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Active Listeners</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Monitor all ports currently listening on this server.</p>
        </div>
        <div class="flex items-center space-x-3">
            <div class="flex items-center space-x-2 bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border px-4 py-2 rounded-2xl">
                <span class="flex h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Network Live</span>
            </div>
        </div>
    </div>

    <div class="card bg-white dark:bg-dark-card rounded-[2rem] border border-gray-200 dark:border-dark-border overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-gray-50 dark:bg-dark-hover text-[10px] font-black uppercase tracking-widest text-gray-400 border-b border-gray-200 dark:border-dark-border">
                        <th class="p-6">Protocol</th>
                        <th class="p-6">Port</th>
                        <th class="p-6">Local Address</th>
                        <th class="p-6">Process / PID</th>
                        <th class="p-6">Assigned Service</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-dark-border">
                    @forelse($ports as $port)
                        <tr class="hover:bg-gray-50 dark:hover:bg-[#1c2128] transition-colors group">
                            <td class="p-6">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest {{ $port['protocol'] == 'TCP' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 border border-blue-100 dark:border-blue-900/30' : 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 border border-yellow-100 dark:border-yellow-900/30' }}">
                                    {{ $port['protocol'] }}
                                </span>
                            </td>
                            <td class="p-6">
                                <span class="text-sm font-black text-gray-900 dark:text-white">{{ $port['port'] }}</span>
                            </td>
                            <td class="p-6">
                                <code class="text-[11px] font-mono text-gray-500 dark:text-gray-400">{{ $port['address'] }}</code>
                            </td>
                            <td class="p-6">
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $port['process'] }}</span>
                                    <span class="text-[10px] font-mono text-gray-400">PID: {{ $port['pid'] }}</span>
                                </div>
                            </td>
                            <td class="p-6">
                                @if($port['service'])
                                    <a href="{{ route('services.show', $port['service']->id) }}" class="inline-flex items-center space-x-2 bg-brand-50 hover:bg-brand-100 dark:bg-brand-900/20 dark:hover:bg-brand-900/30 text-brand-500 px-3 py-1.5 rounded-xl border border-brand-100 dark:border-brand-900/30 transition-all">
                                        <i data-lucide="terminal" class="w-3 h-3"></i>
                                        <span class="text-[11px] font-black uppercase tracking-tight">{{ $port['service']->name }}</span>
                                    </a>
                                @else
                                    <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest italic">External System Process</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center text-gray-500 dark:text-gray-500 italic">No active listeners found.</td>
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
