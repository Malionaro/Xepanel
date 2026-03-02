@extends('layouts.app')

@section('header_title', 'Egg Management')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Service Templates (Eggs)</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Create and manage pre-configured deployment templates.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('eggs.create') }}" class="flex items-center space-x-2 bg-brand-500 hover:bg-brand-600 text-white px-6 py-2.5 rounded-2xl text-sm font-bold shadow-lg shadow-brand-500/25 transition-all hover:-translate-y-0.5 active:translate-y-0">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Create New Egg</span>
            </a>
        </div>
    </div>

    @if(session('status'))
        <div class="bg-green-100 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 text-green-700 dark:text-green-400 p-4 rounded-2xl flex items-center space-x-3">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span class="text-sm font-medium">{{ session('status') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
        @foreach($eggs as $egg)
            <div class="group bg-white dark:bg-dark-card rounded-[2rem] border border-gray-200 dark:border-dark-border p-8 hover:shadow-2xl hover:border-brand-500/30 transition-all duration-500 relative overflow-hidden flex flex-col h-full">
                <div class="flex items-start justify-between relative z-10 mb-4">
                    <div class="flex-1">
                        <h3 class="font-extrabold text-xl text-gray-900 dark:text-white group-hover:text-brand-500 transition-colors">{{ $egg->name }}</h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-gray-100 dark:bg-dark-hover text-gray-500 dark:text-gray-400 mt-1">
                            <i data-lucide="{{ $egg->type === 'docker' ? 'container' : 'cpu' }}" class="w-3 h-3 mr-1"></i>
                            {{ strtoupper($egg->type) }}
                        </span>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('eggs.edit', $egg->id) }}" class="p-2 bg-gray-50 dark:bg-dark-bg text-gray-400 hover:text-brand-500 rounded-xl transition-colors">
                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                        </a>
                        <form action="{{ route('eggs.destroy', $egg->id) }}" method="POST" onsubmit="return confirm('Delete this template?')">
                            @csrf
                            @method('DELETE')
                            <button class="p-2 bg-gray-50 dark:bg-dark-bg text-gray-400 hover:text-red-500 rounded-xl transition-colors">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-6">
                    {{ $egg->description ?: 'No description provided.' }}
                </p>

                <div class="mt-auto pt-6 border-t border-gray-50 dark:border-dark-border space-y-3">
                    @if($egg->type === 'docker')
                        <div class="flex items-center text-xs font-mono text-gray-400">
                            <i data-lucide="layers" class="w-3.5 h-3.5 mr-2"></i>
                            <span class="truncate">{{ $egg->docker_image }}</span>
                        </div>
                    @else
                        <div class="flex items-center text-xs font-mono text-gray-400">
                            <i data-lucide="play" class="w-3.5 h-3.5 mr-2"></i>
                            <span class="truncate">{{ $egg->start_command }}</span>
                        </div>
                    @endif
                    
                    @if(!empty($egg->tags))
                        <div class="flex flex-wrap gap-1.5 pt-2">
                            @foreach(explode(',', $egg->tags) as $tag)
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-brand-50 dark:bg-brand-900/10 text-brand-500 border border-brand-100 dark:border-brand-900/20">{{ trim($tag) }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
