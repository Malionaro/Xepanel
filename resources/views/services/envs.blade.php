@extends('layouts.app')

@section('header_title', 'Environment Variables')

@section('content')
<div class="space-y-8">
    <div class="flex items-center space-x-2 text-gray-500 dark:text-gray-400 mb-2">
        <a href="{{ route('dashboard') }}" class="hover:text-brand-500 transition-colors">Dashboard</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <a href="{{ route('services.show', $service->id) }}" class="hover:text-brand-500 transition-colors">{{ $service->name }}</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-900 dark:text-white font-bold">Environment Variables</span>
    </div>

    <div class="flex items-center justify-between">
        <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Runtime Environment</h2>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- List of current ENVs -->
        <div class="lg:col-span-2 space-y-4">
            <div class="card bg-white dark:bg-dark-card rounded-[2rem] border border-gray-200 dark:border-dark-border overflow-hidden shadow-sm transition-all duration-300">
                <div class="px-6 py-4 bg-gray-50 dark:bg-dark-hover border-b border-gray-200 dark:border-dark-border">
                    <h3 class="text-xs font-black uppercase tracking-widest text-gray-400">Current Variables</h3>
                </div>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] font-black uppercase tracking-widest text-gray-400 border-b border-gray-100 dark:border-dark-border">
                            <th class="p-6">Key</th>
                            <th class="p-6">Value</th>
                            <th class="p-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-dark-border">
                        @forelse($service->env_vars ?? [] as $key => $value)
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#1c2128] transition-colors group">
                                <td class="p-6">
                                    <div class="flex items-center space-x-2">
                                        <i data-lucide="variable" class="w-3.5 h-3.5 text-brand-500"></i>
                                        <span class="font-mono text-sm font-bold text-gray-900 dark:text-white">{{ $key }}</span>
                                    </div>
                                </td>
                                <td class="p-6">
                                    <code class="px-2 py-1 bg-gray-100 dark:bg-dark-bg rounded-lg font-mono text-xs text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-dark-border">{{ $value }}</code>
                                </td>
                                <td class="p-6 text-right">
                                    <form action="{{ route('services.envs.destroy', $service->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="key" value="{{ $key }}">
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition-colors" onclick="return confirm('Remove this variable?')">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="p-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <div class="w-12 h-12 bg-gray-100 dark:bg-dark-bg rounded-full flex items-center justify-center text-xl">Empty</div>
                                        <p class="text-sm text-gray-500 italic">No environment variables defined.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add new ENV form -->
        <div class="lg:col-span-1">
            <form action="{{ route('services.envs.store', $service->id) }}" method="POST" class="card bg-white dark:bg-dark-card p-8 rounded-[2.5rem] border border-gray-200 dark:border-dark-border shadow-xl space-y-6">
                @csrf
                <div class="flex items-center space-x-3 mb-2">
                    <div class="w-10 h-10 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-500">
                        <i data-lucide="plus-circle" class="w-6 h-6"></i>
                    </div>
                    <h3 class="font-black text-xl text-gray-900 dark:text-white tracking-tight">Add Variable</h3>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Key Name</label>
                    <input type="text" name="key" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm uppercase" required placeholder="e.g. PORT">
                </div>
                
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Value</label>
                    <input type="text" name="value" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm" required placeholder="e.g. 8080">
                </div>
                
                <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-4 rounded-2xl transition-all shadow-lg shadow-brand-500/25 active:scale-95 flex items-center justify-center space-x-2">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    <span>ADD VARIABLE</span>
                </button>

                <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/20 rounded-2xl">
                    <p class="text-[10px] text-blue-700 dark:text-blue-400 font-bold leading-relaxed">
                        Variables are passed to the process environment during startup.
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
