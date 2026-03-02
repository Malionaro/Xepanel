@extends('layouts.app')

@section('header_title', 'Network Infrastructure')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Active Sockets</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Real-time overview of all listening network ports on the host system.</p>
        </div>
        <button onclick="location.reload()" class="flex items-center space-x-2 bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border text-gray-700 dark:text-gray-300 px-6 py-2.5 rounded-2xl text-sm font-bold transition-all hover:shadow-md active:scale-95">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            <span>Refresh Scan</span>
        </button>
    </div>

    <div class="bg-indigo-50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-900/20 p-6 rounded-[2rem] flex items-start space-x-4 transition-colors duration-300">
        <div class="w-12 h-12 rounded-2xl bg-indigo-500 text-white flex items-center justify-center shrink-0 shadow-lg shadow-indigo-500/20">
            <i data-lucide="network" class="w-6 h-6"></i>
        </div>
        <div>
            <h3 class="font-black text-indigo-900 dark:text-indigo-400 uppercase tracking-tight text-sm">Traffic Analysis</h3>
            <p class="text-sm text-indigo-800 dark:text-indigo-500 mt-1 leading-relaxed font-medium">
                The list below shows all applications currently bound to a port. Use this to verify that your services are reachable and to prevent port conflicts.
            </p>
        </div>
    </div>

    <div class="card bg-white dark:bg-dark-card rounded-[2.5rem] border border-gray-200 dark:border-dark-border overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-dark-hover text-[10px] font-black uppercase tracking-widest text-gray-400 border-b border-gray-200 dark:border-dark-border">
                    <th class="p-6">Port / Protocol</th>
                    <th class="p-6">Binding Address</th>
                    <th class="p-6">Owner Process</th>
                    <th class="p-6 text-right">System PID</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-dark-border">
                @forelse($ports as $port)
                    <tr class="hover:bg-gray-50 dark:hover:bg-[#1c2128] transition-colors group">
                        <td class="p-6">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 rounded-xl {{ $port['protocol'] == 'TCP' ? 'bg-purple-50 dark:bg-purple-900/20 text-purple-600' : 'bg-orange-50 dark:bg-orange-900/20 text-orange-600' }} flex items-center justify-center font-black text-xs shrink-0 border border-current border-opacity-10">
                                    {{ $port['protocol'] }}
                                </div>
                                <span class="text-xl font-black text-brand-500 tracking-tight">{{ $port['port'] }}</span>
                            </div>
                        </td>
                        <td class="p-6">
                            <div class="flex items-center space-x-2 text-gray-600 dark:text-gray-400">
                                <i data-lucide="map-pin" class="w-3 h-3"></i>
                                <code class="text-xs font-mono font-bold">{{ $port['address'] }}</code>
                            </div>
                        </td>
                        <td class="p-6">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 rounded-full bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.5)]"></div>
                                <span class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ str_replace('"', '', $port['process']) }}</span>
                            </div>
                        </td>
                        <td class="p-6 text-right">
                            <span class="px-3 py-1 bg-gray-100 dark:bg-dark-bg rounded-lg font-mono text-[10px] font-black text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-dark-border shadow-inner">
                                #{{ $port['pid'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-24 text-center">
                            <div class="flex flex-col items-center justify-center space-y-4">
                                <div class="w-16 h-16 bg-gray-100 dark:bg-dark-bg rounded-full flex items-center justify-center text-3xl">📡</div>
                                <div>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">Quiet Network</p>
                                    <p class="text-sm text-gray-500 italic">No listening sockets detected or permission denied.</p>
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
