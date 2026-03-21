@extends('layouts.app')

@section('header_title', __('panel.backups'))

@section('content')
<div class="space-y-10">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white leading-tight uppercase tracking-[0.05em]">{{ __('panel.data_sovereignty') }}</h2>
            <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg font-medium">{{ __('panel.data_sovereignty_desc') }}</p>
        </div>
        <div class="flex items-center space-x-4">
            <a href="{{ route('services.show', $service->id) }}" class="flex items-center space-x-3 px-6 py-3 rounded-2xl glass dark:bg-dark-card border-slate-200 dark:border-dark-border text-slate-600 dark:text-slate-300 text-xs font-black uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>{{ __('panel.back') }}</span>
            </a>
            <form action="{{ route('services.backups.store', $service->id) }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center space-x-3 px-8 py-3.5 rounded-2xl bg-brand-500 text-white text-xs font-black uppercase tracking-[0.2em] shadow-xl shadow-brand-500/25 hover:bg-brand-600 transition-all hover:-translate-y-1 active:scale-95">
                    <i data-lucide="plus" class="w-4 h-4 text-white"></i>
                    <span>{{ __('panel.create_archive') }}</span>
                </button>
            </form>
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

    <div class="glass dark:bg-dark-card rounded-[3rem] border border-slate-200 dark:border-white/5 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-white/5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-100 dark:border-white/5">
                        <th class="p-8">{{ __('panel.snapshot_metadata') }}</th>
                        <th class="p-8">{{ __('panel.file_size') }}</th>
                        <th class="p-8 text-center">{{ __('panel.timestamp') }}</th>
                        <th class="p-8 text-right">{{ __('panel.protocol') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse($backups as $backup)
                        <tr class="group hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="p-8">
                                <div class="flex items-center space-x-5">
                                    <div class="w-12 h-12 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-500 border border-brand-500/20 group-hover:scale-110 transition-transform shadow-lg shadow-brand-500/5">
                                        <i data-lucide="file-archive" class="w-6 h-6"></i>
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-mono text-sm font-bold text-slate-900 dark:text-white truncate max-w-xs">{{ $backup['name'] }}</span>
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">{{ __('panel.gzip_compression') }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-8">
                                <span class="px-3 py-1.5 bg-slate-100 dark:bg-white/5 rounded-xl text-[10px] font-black text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-white/10">{{ $backup['size'] }}</span>
                            </td>
                            <td class="p-8 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($backup['time'])->format('M d, Y') }}</span>
                                    <span class="text-[10px] text-slate-400 font-medium">{{ \Carbon\Carbon::parse($backup['time'])->format('H:i') }}</span>
                                </div>
                            </td>
                            <td class="p-8 text-right">
                                <div class="flex items-center justify-end space-x-3">
                                    <a href="{{ route('services.backups.download', ['id' => $service->id, 'filename' => $backup['name']]) }}" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-400 hover:text-brand-500 transition-all hover:bg-brand-500/10 border border-slate-200 dark:border-white/10" title="Download">
                                        <i data-lucide="download" class="w-5 h-5"></i>
                                    </a>
                                    <form action="{{ route('services.backups.destroy', ['id' => $service->id, 'filename' => $backup['name']]) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-400 hover:text-red-500 transition-all hover:bg-red-500/10 border border-slate-200 dark:border-white/10" onclick="return confirm('{{ __('panel.confirm_delete_backup') }}')">
                                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-24 text-center">
                                <div class="flex flex-col items-center justify-center space-y-6 opacity-40">
                                    <div class="relative">
                                        <div class="absolute inset-0 bg-brand-500/20 blur-3xl rounded-full"></div>
                                        <div class="w-24 h-24 bg-white dark:bg-slate-900 rounded-[2.5rem] flex items-center justify-center text-5xl shadow-2xl border border-slate-100 dark:border-white/5 relative z-10">📦</div>
                                    </div>
                                    <div class="max-w-xs mx-auto">
                                        <p class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ __('panel.archives_offline') }}</p>
                                        <p class="text-sm text-slate-500 font-medium mt-2 leading-relaxed">{{ __('panel.no_archives_desc') }}</p>
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
