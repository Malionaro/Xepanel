@extends('layouts.app')

@section('header_title', __('panel.authentication'))

@section('content')
<div class="min-h-[80vh] flex flex-col items-center justify-center relative overflow-hidden">
    <!-- Background Accents specific for Login -->
    <div
        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-brand-500/10 blur-[120px] rounded-full pointer-events-none">
    </div>

    <div class="w-full max-w-lg relative z-10">
        <!-- Logo / Brand Header -->
        <div class="text-center mb-12 animate-in fade-in slide-in-from-top-8 duration-700">
            <div
                class="inline-flex items-center justify-center w-20 h-20 rounded-[2rem] bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-2xl shadow-brand-500/40 mb-6 border border-white/20">
                <i data-lucide="{{ \App\Models\Setting::get('panel_icon', 'layers') }}" class="w-10 h-10"></i>
            </div>
            <h1 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white uppercase tracking-[0.1em]">{{
                \App\Models\Setting::get('panel_name', 'Xepanel') }}</h1>
            <p
                class="text-slate-500 dark:text-slate-400 mt-3 font-bold uppercase text-[10px] tracking-[0.3em] opacity-60">
                {{ __('panel.access_protocol') }}</p>
        </div>

        <!-- Login Card -->
        <div
            class="glass dark:bg-dark-card border border-slate-200 dark:border-white/10 p-12 rounded-[3.5rem] shadow-2xl animate-in fade-in zoom-in duration-700 delay-150">
            <form action="{{ route('login') }}" method="POST" class="space-y-8">
                @csrf

                @if($errors->any())
                <div
                    class="p-4 bg-red-500/10 border border-red-500/20 rounded-2xl flex items-center space-x-3 animate-shake">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>
                    <span class="text-xs font-bold text-red-600 dark:text-red-400">{{ $errors->first() }}</span>
                </div>
                @endif

                <div class="space-y-6">
                    <!-- Email Field -->
                    <div class="group">
                        <label
                            class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-3 ml-1 group-focus-within:text-brand-500 transition-colors">{{ __('panel.credential_identity') }}</label>
                        <div class="relative">
                            <i data-lucide="mail"
                                class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-brand-500 transition-colors"></i>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-5 pl-14 pr-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm"
                                placeholder="email@example.com">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="group">
                        <label
                            class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-3 ml-1 group-focus-within:text-brand-500 transition-colors">{{ __('panel.access_cipher') }}</label>
                        <div class="relative">
                            <i data-lucide="lock"
                                class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-brand-500 transition-colors"></i>
                            <input type="password" name="password" required
                                class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-5 pl-14 pr-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm"
                                placeholder="••••••••">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between px-1">
                    <label class="flex items-center space-x-3 cursor-pointer group">
                        <div class="relative">
                            <input type="checkbox" name="remember" class="peer sr-only">
                            <div
                                class="w-10 h-5 bg-slate-200 dark:bg-white/10 rounded-full peer-checked:bg-brand-500 transition-all">
                            </div>
                            <div
                                class="absolute left-1 top-1 w-3 h-3 bg-white rounded-full transition-all peer-checked:translate-x-5">
                            </div>
                        </div>
                        <span
                            class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ __('panel.persistence') }}</span>
                    </label>
                    <a href="#"
                        class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-brand-500 transition-colors">{{ __('panel.recovery') }}</a>
                </div>

                <button type="submit"
                    class="w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-5 rounded-2xl transition-all shadow-xl shadow-brand-500/25 active:scale-[0.98] group/btn flex items-center justify-center space-x-3">
                    <span class="text-xs uppercase tracking-[0.3em]">{{ __('panel.initialise_session') }}</span>
                    <i data-lucide="arrow-right" class="w-5 h-5 transition-transform group-hover/btn:translate-x-1"></i>
                </button>
            </form>
        </div>

        <!-- Footer Info -->
        <p class="text-center mt-10 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 opacity-40">
            {{ __('panel.secure_node') }} &copy; {{ date('Y') }} {{ __('panel.protocol_version') }} 2.0
        </p>
    </div>
</div>

<style>
    /* Prevent sidebar and header from showing on login page */
    aside,
    header {
        display: none !important;
    }

    main {
        padding: 0 !important;
    }

    .animate-shake {
        animation: shake 0.5s cubic-bezier(.36, .07, .19, .97) both;
    }

    @keyframes shake {

        10%,
        90% {
            transform: translate3d(-1px, 0, 0);
        }

        20%,
        80% {
            transform: translate3d(2px, 0, 0);
        }

        30%,
        50%,
        70% {
            transform: translate3d(-4px, 0, 0);
        }

        40%,
        60% {
            transform: translate3d(4px, 0, 0);
        }
    }
</style>

<script>
    if (typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
