@extends('layouts.app')

@section('header_title', 'Schedules')

@section('content')
<div class="space-y-10">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
        <div>
            <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white leading-tight">Task Automation</h2>
            <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg font-medium">Synchronize routine maintenance and operational protocols.</p>
        </div>
        <a href="{{ route('services.show', $service->id) }}" class="flex items-center space-x-3 px-6 py-3 rounded-2xl glass dark:bg-dark-card border-slate-200 dark:border-dark-border text-slate-600 dark:text-slate-300 text-xs font-black uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-800 transition-all hover:-translate-x-1">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back</span>
        </a>
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
        <!-- List of current tasks -->
        <div class="lg:col-span-2 space-y-6">
            <div class="glass dark:bg-dark-card rounded-[3rem] border border-slate-200 dark:border-white/5 overflow-hidden shadow-2xl">
                <div class="px-10 py-6 bg-slate-50/50 dark:bg-white/5 border-b border-slate-100 dark:border-white/5">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Active Protocols</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-100 dark:border-white/5">
                                <th class="p-8">Automation Task</th>
                                <th class="p-8">Cron Policy</th>
                                <th class="p-8 text-right">Registry</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @forelse($service->schedules ?? [] as $task)
                                <tr class="group hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="p-8">
                                        <div class="flex items-center space-x-5">
                                            <div class="w-12 h-12 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-500 border border-brand-500/20 group-hover:scale-110 transition-transform shadow-lg shadow-brand-500/5">
                                                <i data-lucide="clock" class="w-6 h-6"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $task['name'] }}</p>
                                                <code class="text-[10px] font-mono text-slate-400 truncate block mt-1" title="{{ $task['command'] }}">$ {{ $task['command'] }}</code>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-8">
                                        <div class="flex flex-col space-y-2">
                                            <code class="px-3 py-1 bg-slate-100 dark:bg-white/5 rounded-xl font-mono text-xs text-brand-600 dark:text-brand-400 border border-slate-200 dark:border-white/10 w-fit">{{ $task['cron'] }}</code>
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Next: Analysis pending</p>
                                        </div>
                                    </td>
                                    <td class="p-8 text-right">
                                        <div class="flex items-center justify-end space-x-3">
                                            <a href="{{ route('services.schedules.edit', ['id' => $service->id, 'taskId' => $task['id']]) }}" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-400 hover:text-brand-500 transition-all hover:bg-brand-500/10 border border-slate-200 dark:border-white/10">
                                                <i data-lucide="edit-3" class="w-5 h-5"></i>
                                            </a>
                                            <form action="{{ route('services.schedules.destroy', ['id' => $service->id, 'taskId' => $task['id']]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-400 hover:text-red-500 transition-all hover:bg-red-500/10 border border-slate-200 dark:border-white/10" onclick="return confirm('CRITICAL: Terminate automation protocol?')">
                                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-24 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-6 opacity-40">
                                            <div class="relative">
                                                <div class="absolute inset-0 bg-brand-500/20 blur-3xl rounded-full"></div>
                                                <div class="w-24 h-24 bg-white dark:bg-slate-900 rounded-[2.5rem] flex items-center justify-center text-5xl shadow-2xl border border-slate-100 dark:border-white/5 relative z-10">⏰</div>
                                            </div>
                                            <div class="max-w-xs mx-auto">
                                                <p class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Manual Override</p>
                                                <p class="text-sm text-slate-500 font-medium mt-2 leading-relaxed">No automation scripts registered. Initialize a new protocol to enable autonomous maintenance.</p>
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

        <!-- Add new task form -->
        <div class="lg:col-span-1">
            <form action="{{ route('services.schedules.store', $service->id) }}" method="POST" class="glass dark:bg-dark-card p-10 rounded-[3rem] border border-slate-200 dark:border-white/5 shadow-2xl space-y-8 relative overflow-hidden group">
                <!-- Decoration -->
                <div class="absolute -top-24 -right-24 w-48 h-48 bg-brand-500/5 rounded-full blur-3xl group-hover:bg-brand-500/10 transition-colors duration-700"></div>
                
                @csrf
                <div class="flex items-center space-x-4 mb-4 relative z-10">
                    <div class="w-12 h-12 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-500 border border-brand-500/20">
                        <i data-lucide="plus-circle" class="w-7 h-7"></i>
                    </div>
                    <h3 class="font-black text-2xl text-slate-900 dark:text-white tracking-tight">New Protocol</h3>
                </div>
                
                <div class="space-y-3 relative z-10">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Task Identification</label>
                    <input type="text" name="name" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm placeholder:text-slate-400 dark:placeholder:text-slate-600 shadow-sm" required placeholder="Protocol Name">
                </div>
                
                <div class="space-y-3 relative z-10">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Cron Frequency</label>
                    <input type="text" name="cron" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm placeholder:text-slate-400 dark:placeholder:text-slate-600 shadow-sm" required placeholder="* * * * *" value="0 0 * * *">
                    <div class="flex items-center space-x-2 ml-1 text-slate-400">
                        <i data-lucide="info" class="w-3.5 h-3.5"></i>
                        <span class="text-[9px] font-black uppercase tracking-widest">Minute Hour Day Month Week</span>
                    </div>
                </div>

                <div class="space-y-3 relative z-10">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Shell Execution</label>
                    <input type="text" name="command" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm placeholder:text-slate-400 dark:placeholder:text-slate-600 shadow-sm" required placeholder="command --flag">
                </div>
                
                <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-5 rounded-2xl transition-all shadow-xl shadow-brand-500/25 active:scale-95 flex items-center justify-center space-x-3 group/submit relative z-10">
                    <i data-lucide="zap" class="w-5 h-5 text-white transition-transform group-hover/submit:scale-125"></i>
                    <span class="text-xs uppercase tracking-[0.2em]">INITIALISE PROTOCOL</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
