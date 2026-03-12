@extends('layouts.app')

@section('header_title', __('panel.create_new_role'))

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white uppercase tracking-[0.05em]">{{ __('panel.create_role') }}</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-2 font-medium">{{ __('panel.create_role_desc') }}</p>
        </div>
        <a href="{{ route('admin.roles.index') }}" class="text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all">
            <i data-lucide="x" class="w-8 h-8"></i>
        </a>
    </div>

    <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-8">
        @csrf
        
        <div class="glass dark:bg-dark-card border border-slate-200 dark:border-white/10 rounded-[2.5rem] p-10 shadow-sm">
            <div class="space-y-8">
                <div class="group">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-3 ml-1 group-focus-within:text-brand-500 transition-colors">{{ __('panel.role_name') }}</label>
                    <div class="relative">
                        <i data-lucide="shield" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-brand-500 transition-colors"></i>
                        <input type="text" name="name" required class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-5 pl-14 pr-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm" placeholder="{{ __('panel.role_name_placeholder') }}">
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 dark:border-white/5">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-6 ml-1">{{ __('panel.configure_permissions') }}</label>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-data="{ selected: [] }">
                        @foreach($permissions as $permission)
                        <label class="relative flex items-center p-5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 cursor-pointer hover:border-brand-500/50 transition-all group">
                            <div class="flex-1">
                                <span class="block text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white group-hover:text-brand-500 transition-colors">{{ str_replace('_', ' ', $permission) }}</span>
                                <span class="block text-[10px] text-slate-500 mt-1 font-medium">{{ __('panel.permission_desc', ['permission' => $permission]) }}</span>
                            </div>
                            <div class="relative flex items-center">
                                <input type="checkbox" name="permissions[]" value="{{ $permission }}" class="peer sr-only">
                                <div class="w-12 h-6 bg-slate-200 dark:bg-white/10 rounded-full peer-checked:bg-brand-500 transition-all"></div>
                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-all peer-checked:translate-x-6"></div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4 pt-4">
            <a href="{{ route('admin.roles.index') }}" class="px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest text-slate-500 hover:bg-slate-100 dark:hover:bg-white/5 transition-all">{{ __('panel.cancel') }}</a>
            <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-10 py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-xl shadow-brand-500/25 active:scale-95">{{ __('panel.create_role_btn') }}</button>
        </div>
    </form>
</div>
@endsection
