@extends('layouts.app')

@section('header_title', __('panel.create_account'))

@section('content')
<div class="max-w-4xl mx-auto space-y-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-8">
        <div>
            <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white uppercase italic tracking-[0.05em]">{{ __('panel.provision_identity') }}</h2>
            <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg font-medium">{{ __('panel.provision_identity_desc') }}</p>
        </div>
        <a href="{{ route('users.index') }}" class="flex items-center space-x-3 glass dark:bg-dark-card border-slate-200 dark:border-dark-border text-slate-600 dark:text-slate-300 px-8 py-4 rounded-[2rem] text-xs font-black uppercase tracking-[0.2em] hover:bg-slate-50 dark:hover:bg-slate-800 transition-all hover:-translate-x-1 shrink-0 shadow-sm">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
            <span>{{ __('panel.cancel') }}</span>
        </a>
    </div>

    <form action="{{ route('users.store') }}" method="POST" class="glass dark:bg-dark-card border border-slate-200 dark:border-white/5 p-10 md:p-12 rounded-[3.5rem] shadow-2xl space-y-10 relative overflow-hidden group">
        <div class="absolute -right-24 -top-24 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl group-hover:bg-brand-500/10 transition-colors duration-700"></div>
        
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 relative z-10">
            <div class="space-y-3">
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('panel.full_name') }}</label>
                <div class="relative">
                    <i data-lucide="user" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 pl-14 pr-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm" required placeholder="John Doe">
                </div>
            </div>
            <div class="space-y-3">
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('panel.email_address') }}</label>
                <div class="relative">
                    <i data-lucide="mail" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 pl-14 pr-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm" required placeholder="john@example.com">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 relative z-10">
            <div class="space-y-3">
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('panel.secure_password') }}</label>
                <div class="relative">
                    <i data-lucide="lock" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                    <input type="password" name="password" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 pl-14 pr-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm" required minlength="6" placeholder="••••••••">
                </div>
            </div>
            <div class="space-y-3">
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('panel.access_tier') }}</label>
                <div class="relative">
                    <i data-lucide="shield" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                    <select name="role" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 pl-14 pr-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm appearance-none cursor-pointer">
                        <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>{{ __('panel.regular_user') }}</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>{{ __('panel.administrator') }}</option>
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 pointer-events-none"></i>
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 p-6 rounded-3xl flex items-center space-x-4 animate-in shake-in duration-500 relative z-10">
                <i data-lucide="alert-octagon" class="w-6 h-6"></i>
                <span class="text-sm font-bold">{{ $errors->first() }}</span>
            </div>
        @endif

        <div class="pt-6 relative z-10">
            <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-5 rounded-[2rem] transition-all shadow-2xl shadow-brand-500/25 active:scale-95 flex items-center justify-center space-x-3 group/submit">
                <i data-lucide="zap" class="w-6 h-6 transition-transform group-hover/submit:scale-125"></i>
                <span class="text-xs uppercase tracking-[0.3em]">{{ __('panel.initialize_account') }}</span>
            </button>
        </div>
    </form>

    <div class="p-8 bg-brand-500/5 dark:bg-brand-500/10 border border-brand-500/20 rounded-[2.5rem] flex items-start space-x-5 shadow-sm">
        <div class="w-12 h-12 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-500 shrink-0">
            <i data-lucide="info" class="w-6 h-6"></i>
        </div>
        <p class="text-sm text-slate-600 dark:text-slate-400 font-medium leading-relaxed">
            {{ __('panel.provision_info') }}
        </p>
    </div>
</div>

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
