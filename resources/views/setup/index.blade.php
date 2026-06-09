@extends('layouts.app')

@section('header_title', 'Initial Setup')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center relative overflow-hidden">
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-brand-500/10 blur-[120px] rounded-full pointer-events-none"></div>

    <div class="w-full max-w-xl relative z-10">
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-[2rem] bg-brand-500 text-white shadow-2xl shadow-brand-500/30 mb-6 border border-white/20">
                <i data-lucide="shield-check" class="w-10 h-10"></i>
            </div>
            <h1 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white uppercase">Xepanel Setup</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-3 font-bold uppercase text-[10px] tracking-[0.3em] opacity-70">Create the first administrator</p>
        </div>

        <div class="glass dark:bg-dark-card border border-slate-200 dark:border-white/10 p-10 md:p-12 rounded-[3rem] shadow-2xl">
            <form action="{{ route('setup.store') }}" method="POST" class="space-y-7">
                @csrf

                @if($errors->any())
                    <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-2xl flex items-center space-x-3">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>
                        <span class="text-xs font-bold text-red-600 dark:text-red-400">{{ $errors->first() }}</span>
                    </div>
                @endif

                <div class="space-y-3">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Name</label>
                    <input type="text" name="name" value="{{ old('name', 'Administrator') }}" required autofocus class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm">
                </div>

                <div class="space-y-3">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', 'admin@xepanel.local') }}" required class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Password</label>
                        <input type="password" name="password" required minlength="10" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm">
                    </div>
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Confirm</label>
                        <input type="password" name="password_confirmation" required minlength="10" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm">
                    </div>
                </div>

                <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-5 rounded-2xl transition-all shadow-xl shadow-brand-500/25 active:scale-[0.98] flex items-center justify-center space-x-3">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                    <span class="text-xs uppercase tracking-[0.3em]">Create Admin</span>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    aside,
    header {
        display: none !important;
    }

    main {
        padding: 0 !important;
    }
</style>

<script>
    if (typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
