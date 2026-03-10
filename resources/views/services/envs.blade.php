@extends('layouts.app')

@section('header_title', 'Environment Variables')

@section('content')
<div class="space-y-8">
    <!-- Breadcrumbs -->
    <div class="flex items-center p-1.5 glass dark:bg-white/5 border-slate-200 dark:border-white/10 rounded-2xl shadow-sm w-fit mb-6">
        <a href="{{ route('services.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 text-slate-500 dark:text-slate-400 hover:text-brand-500 transition-all group">
            <i data-lucide="server" class="w-4 h-4 transition-transform group-hover:scale-110"></i>
            <span class="text-[10px] font-black uppercase tracking-widest">{{ __('panel.my_services') }}</span>
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 dark:text-slate-600 mx-1"></i>
        <a href="{{ route('services.show', $service->id) }}" class="flex items-center space-x-2 px-4 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 text-slate-500 dark:text-slate-400 hover:text-brand-500 transition-all group">
            <i data-lucide="terminal" class="w-4 h-4 transition-transform group-hover:scale-110"></i>
            <span class="text-[10px] font-black uppercase tracking-widest">{{ $service->name }}</span>
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 dark:text-slate-600 mx-1"></i>
        <div class="flex items-center space-x-2 px-4 py-2 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-600 dark:text-brand-400">
            <i data-lucide="list" class="w-4 h-4"></i>
            <span class="text-[10px] font-black uppercase tracking-widest">Environment Variables</span>
        </div>
    </div>

    <div class="flex items-center justify-between gap-4">
        <div class="flex flex-col">
            <h2 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Runtime Environment</h2>
            <p class="text-xs font-bold text-slate-500 mt-1">Manage environment variables for {{ $service->name }}</p>
        </div>
        <div class="flex items-center space-x-4">
            <a href="{{ route('services.show', $service->id) }}" class="flex items-center space-x-3 px-6 py-3 rounded-2xl glass dark:bg-dark-card border-slate-200 dark:border-dark-border text-slate-600 dark:text-slate-300 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-800 transition-all hover:-translate-x-1 shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back</span>
            </a>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- List of current ENVs -->
        <div class="lg:col-span-2 space-y-4">
            <div class="glass dark:bg-dark-card rounded-[2.5rem] border border-slate-200 dark:border-dark-border overflow-hidden shadow-sm transition-all duration-300">
                <div class="px-8 py-6 bg-slate-50/50 dark:bg-white/5 border-b border-slate-100 dark:border-white/5 flex items-center">
                    <i data-lucide="list" class="w-4 h-4 text-brand-500 mr-3"></i>
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Current Variables</h3>
                </div>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-100 dark:border-white/5">
                            <th class="px-8 py-4">Key</th>
                            <th class="px-8 py-4">Value</th>
                            <th class="px-8 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @forelse($service->env_vars ?? [] as $key => $value)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors group">
                                <td class="px-8 py-5">
                                    <span class="font-mono text-sm font-bold text-slate-900 dark:text-white">{{ $key }}</span>
                                </td>
                                <td class="px-8 py-5">
                                    <code class="px-3 py-1.5 bg-slate-100 dark:bg-slate-900/50 rounded-lg font-mono text-xs text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 shadow-sm">{{ $value }}</code>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <form action="{{ route('services.envs.destroy', $service->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="key" value="{{ $key }}">
                                        <button type="submit" class="p-2 text-slate-400 hover:text-red-500 transition-all rounded-lg hover:bg-red-500/10 opacity-0 group-hover:opacity-100" onclick="return confirm('Remove this variable?')">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="p-16 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-4">
                                        <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800/50 rounded-2xl flex items-center justify-center text-slate-400 border border-slate-200 dark:border-slate-700/50">
                                            <i data-lucide="package-open" class="w-8 h-8"></i>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-sm font-bold text-slate-900 dark:text-white">No Variables Configured</p>
                                            <p class="text-[10px] font-black tracking-widest uppercase text-slate-400 mt-1">Add one to the right</p>
                                        </div>
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
            <form action="{{ route('services.envs.store', $service->id) }}" method="POST" class="glass dark:bg-dark-card p-8 md:p-10 rounded-[2.5rem] border border-slate-200 dark:border-dark-border shadow-sm space-y-8">
                @csrf
                <div class="flex items-center space-x-4 mb-2">
                    <div class="w-12 h-12 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-500 border border-brand-500/20">
                        <i data-lucide="plus-circle" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-xl text-slate-900 dark:text-white tracking-tight">Add Variable</h3>
                        <p class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 mt-1">Configure Environment</p>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Key Name</label>
                    <input type="text" name="key" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-5 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all text-slate-900 dark:text-white font-mono text-sm uppercase shadow-sm" required placeholder="e.g. PORT">
                </div>
                
                <div class="space-y-3">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Value</label>
                    <input type="text" name="value" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-5 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all text-slate-900 dark:text-white font-mono text-sm shadow-sm" required placeholder="e.g. 8080">
                </div>
                
                <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-4 md:py-5 rounded-2xl transition-all shadow-xl shadow-brand-500/25 active:scale-95 flex items-center justify-center space-x-3 text-xs group">
                    <i data-lucide="save" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                    <span class="tracking-widest uppercase">Add Variable</span>
                </button>

                <div class="mt-8 p-5 bg-blue-500/10 border border-blue-500/20 rounded-2xl flex items-start space-x-3">
                    <i data-lucide="info" class="w-5 h-5 text-blue-500 shrink-0 mt-0.5"></i>
                    <p class="text-[10px] text-blue-600 dark:text-blue-400 font-bold leading-relaxed uppercase tracking-wider">
                        Variables are passed to the process environment during startup. A restart may be required.
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
