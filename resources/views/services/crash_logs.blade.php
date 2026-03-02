@extends('layouts.app')

@section('header_title', 'Crash Logs')

@section('content')
<div class="space-y-8">
    <div class="flex items-center space-x-2 text-gray-500 dark:text-gray-400 mb-2">
        <a href="{{ route('dashboard') }}" class="hover:text-brand-500 transition-colors">Dashboard</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <a href="{{ route('services.show', $service->id) }}" class="hover:text-brand-500 transition-colors">{{ $service->name }}</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-900 dark:text-white font-bold">Crash History</span>
    </div>

    <div class="flex items-center justify-between">
        <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Incident Reports</h2>
        <a href="{{ route('services.show', $service->id) }}" class="flex items-center space-x-2 bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border text-gray-700 dark:text-gray-300 px-5 py-2.5 rounded-2xl text-sm font-bold transition-all hover:shadow-md">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Service</span>
        </a>
    </div>

    @if(session('status'))
        <div class="bg-green-100 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 text-green-700 dark:text-green-400 p-4 rounded-2xl flex items-center space-x-3">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span class="text-sm font-medium">{{ session('status') }}</span>
        </div>
    @endif

    <div class="space-y-6">
        @forelse($service->crash_logs ?? [] as $log)
            <div class="card bg-white dark:bg-dark-card rounded-[2rem] border border-gray-200 dark:border-dark-border overflow-hidden shadow-sm hover:shadow-md transition-all">
                <div class="bg-gray-50 dark:bg-dark-hover px-8 py-4 border-b border-gray-200 dark:border-dark-border flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-500 border border-red-100 dark:border-red-900/30">
                            <i data-lucide="skull" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="font-black text-sm text-gray-900 dark:text-white uppercase tracking-tight">CRASH DETECTED</span>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $log['timestamp'] }}</p>
                        </div>
                    </div>
                    <form action="{{ route('services.crash_logs.destroy', ['id' => $service->id, 'logId' => $log['id']]) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition-colors" onclick="return confirm('Purge this incident report?')">
                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                        </button>
                    </form>
                </div>
                <div class="bg-gray-900 dark:bg-black p-8 font-mono text-[11px] leading-relaxed overflow-x-auto text-red-400/90 whitespace-pre-wrap selection:bg-red-500 selection:text-white">
                    <p class="mb-4 text-[10px] text-gray-600 dark:text-gray-500 uppercase font-black tracking-widest border-b border-gray-800 pb-2">Last Console Output (Tail)</p>
                    {{ $log['log_snippet'] }}
                </div>
            </div>
        @empty
            <div class="card p-24 text-center bg-white dark:bg-dark-card rounded-[3rem] border border-gray-200 dark:border-dark-border shadow-inner">
                <div class="flex flex-col items-center justify-center space-y-4">
                    <div class="w-20 h-20 bg-green-50 dark:bg-green-900/10 rounded-full flex items-center justify-center text-green-500 text-4xl shadow-sm border border-green-100 dark:border-green-900/20">💎</div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white">Zero Incidents</h3>
                        <p class="text-gray-500 dark:text-gray-400 mt-1 max-w-xs mx-auto">This instance is currently running stable without any recorded crashes.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
