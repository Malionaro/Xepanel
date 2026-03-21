@extends('layouts.app')

@section('header_title', __('panel.egg_management'))

@section('content')
<div class="space-y-10">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
        <div>
            <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white uppercase tracking-[0.05em]">{{ __('panel.egg_templates') }}</h2>
            <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg font-medium">{{ __('panel.egg_templates_desc') }}</p>
        </div>
        <div class="flex items-center space-x-4 shrink-0">
            <button onclick="document.getElementById('import-file-input').click()" class="flex items-center space-x-3 glass dark:bg-dark-card border-slate-200 dark:border-dark-border text-slate-600 dark:text-slate-300 px-8 py-4 rounded-[2rem] text-xs font-black uppercase tracking-[0.2em] hover:bg-slate-50 dark:hover:bg-slate-800 transition-all shadow-sm">
                <i data-lucide="upload-cloud" class="w-5 h-5 text-brand-500"></i>
                <span>{{ __('panel.import_protocol') }}</span>
            </button>
            <form id="import-form" action="{{ route('eggs.import') }}" method="POST" enctype="multipart/form-data" class="hidden">
                @csrf
                <input type="file" id="import-file-input" name="import_file" accept=".json" onchange="this.form.submit()">
            </form>
            <a href="{{ route('eggs.create') }}" class="flex items-center space-x-3 bg-brand-500 hover:bg-brand-600 text-white px-8 py-4 rounded-[2rem] text-xs font-black uppercase tracking-[0.2em] shadow-2xl shadow-brand-500/25 transition-all hover:-translate-y-1 active:scale-95">
                <i data-lucide="plus" class="w-5 h-5"></i>
                <span>{{ __('panel.initialize_egg') }}</span>
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 p-6 rounded-3xl flex items-center space-x-4 animate-in shake-in duration-500">
            <i data-lucide="alert-octagon" class="w-6 h-6"></i>
            <span class="text-sm font-bold">{{ $errors->first() }}</span>
        </div>
    @endif

    @if(session('status'))
        <div class="bg-green-500/10 border border-green-500/20 text-green-600 dark:text-green-400 p-5 rounded-3xl flex items-center space-x-4 animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="w-10 h-10 rounded-xl bg-green-500/20 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
            <span class="text-sm font-bold">{{ session('status') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
        @foreach($eggs as $egg)
            <div class="group glass dark:bg-dark-card rounded-[3.5rem] border border-slate-200 dark:border-white/5 p-10 hover:shadow-2xl hover:border-brand-500/30 transition-all duration-500 relative overflow-hidden flex flex-col h-full">
                <!-- Decoration -->
                <div class="absolute -right-24 -top-24 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl group-hover:bg-brand-500/10 transition-colors duration-700"></div>

                <div class="flex items-start justify-between relative z-10 mb-8">
                    <div class="flex items-center space-x-5 flex-1">
                        <div class="w-16 h-16 rounded-3xl bg-brand-500/10 flex items-center justify-center text-brand-500 border border-brand-500/20 group-hover:scale-110 group-hover:bg-brand-500 group-hover:text-white transition-all duration-500 shadow-xl shadow-brand-500/5">
                            <i data-lucide="{{ $egg->icon ?? 'box' }}" class="w-8 h-8"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-black text-2xl text-slate-900 dark:text-white truncate tracking-tight uppercase group-hover:text-brand-500 transition-colors">{{ $egg->name }}</h3>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-[0.2em] bg-slate-500/10 text-slate-500 dark:text-slate-400 border border-slate-500/20 mt-2">
                                <i data-lucide="{{ $egg->type === 'docker' ? 'container' : 'cpu' }}" class="w-3.5 h-3.5 mr-2"></i>
                                {{ strtoupper($egg->type) }}
                            </span>
                        </div>
                    </div>
                </div>

                <p class="text-sm text-slate-500 dark:text-slate-400 font-medium leading-relaxed line-clamp-3 mb-8 relative z-10">
                    {{ $egg->description ?: __('panel.no_egg_desc') }}
                </p>

                <div class="mt-auto space-y-6 relative z-10">
                    <div class="pt-6 border-t border-slate-100 dark:border-white/5 space-y-4">
                        @if($egg->type === 'docker')
                            <div class="flex items-center text-[10px] font-mono font-bold text-slate-400 bg-slate-50 dark:bg-white/5 px-4 py-2.5 rounded-xl border border-slate-100 dark:border-white/5">
                                <i data-lucide="layers" class="w-4 h-4 mr-3 text-brand-500"></i>
                                <span class="truncate">{{ $egg->docker_image }}</span>
                            </div>
                        @else
                            <div class="flex items-center text-[10px] font-mono font-bold text-slate-400 bg-slate-50 dark:bg-white/5 px-4 py-2.5 rounded-xl border border-slate-100 dark:border-white/5">
                                <i data-lucide="terminal" class="w-4 h-4 mr-3 text-brand-500"></i>
                                <span class="truncate">{{ $egg->start_command }}</span>
                            </div>
                        @endif
                        
                        @if(!empty($egg->tags))
                            <div class="flex flex-wrap gap-2 pt-2">
                                @foreach(explode(',', $egg->tags) as $tag)
                                    <span class="px-3 py-1 rounded-lg text-[8px] font-black uppercase tracking-[0.2em] bg-brand-500/10 text-brand-500 border border-brand-500/20">{{ trim($tag) }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <div class="flex space-x-2">
                            <form action="{{ route('eggs.clone', $egg->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-400 hover:text-emerald-500 transition-all hover:bg-emerald-500/10 border border-slate-200 dark:border-white/10" title="{{ __('panel.clone_protocol') }}">
                                    <i data-lucide="copy" class="w-4 h-4"></i>
                                </button>
                            </form>
                            <a href="{{ route('eggs.export', $egg->id) }}" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-400 hover:text-indigo-500 transition-all hover:bg-indigo-500/10 border border-slate-200 dark:border-white/10" title="{{ __('panel.export_json') }}">
                                <i data-lucide="download" class="w-4 h-4"></i>
                            </a>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('eggs.edit', $egg->id) }}" class="flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-brand-500/10 text-brand-500 border border-brand-500/20 hover:bg-brand-500 hover:text-white transition-all text-[9px] font-black uppercase tracking-widest">
                                <i data-lucide="settings-2" class="w-3.5 h-3.5"></i>
                                <span>{{ __('panel.modify') }}</span>
                            </a>
                            <form action="{{ route('eggs.destroy', $egg->id) }}" method="POST" onsubmit="return confirm('{{ __('panel.confirm_delete_egg') }}')">
                                @csrf
                                @method('DELETE')
                                <button class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-400 hover:text-red-500 transition-all hover:bg-red-500/10 border border-slate-200 dark:border-white/10">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
