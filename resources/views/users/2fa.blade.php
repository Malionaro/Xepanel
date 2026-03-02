@extends('layouts.app')

@section('header_title', 'Two-Factor Authentication')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex items-center space-x-2 text-gray-500 dark:text-gray-400 mb-2">
        <a href="{{ route('dashboard') }}" class="hover:text-brand-500 transition-colors">Dashboard</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-900 dark:text-white font-bold">Security (2FA)</span>
    </div>

    <div>
        <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Account Security</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Protect your account with an additional layer of verification.</p>
    </div>

    @if(session('status'))
        <div class="bg-green-100 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 text-green-700 dark:text-green-400 p-4 rounded-2xl flex items-center space-x-3">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span class="text-sm font-medium">{{ session('status') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 text-red-700 dark:text-red-400 p-4 rounded-2xl flex items-center space-x-3">
            <i data-lucide="alert-octagon" class="w-5 h-5"></i>
            <span class="text-sm font-bold">{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="card bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border p-10 rounded-[2.5rem] shadow-xl space-y-8 transition-all duration-300">
        @if($user->two_factor_enabled)
            <div class="flex flex-col items-center text-center space-y-6">
                <div class="w-20 h-20 bg-green-50 dark:bg-green-900/20 text-green-500 rounded-full flex items-center justify-center border border-green-100 dark:border-green-900/30 shadow-lg shadow-green-500/10">
                    <i data-lucide="shield-check" class="w-10 h-10"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight uppercase">2FA is Protected</h3>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">Your identity is secured. An authentication code is required upon every sign-in attempt.</p>
                </div>

                <div class="w-full pt-8 border-t border-gray-100 dark:border-dark-border">
                    <form action="{{ route('user.two-factor.disable') }}" method="POST" class="max-w-md mx-auto space-y-4">
                        @csrf
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Enter current code to disable</p>
                        <div class="flex space-x-2">
                            <input type="text" name="code" class="flex-1 bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all dark:text-white font-mono text-center tracking-[0.5em] text-lg" required placeholder="000000">
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-black px-6 rounded-2xl transition-all shadow-lg shadow-red-500/25 active:scale-95">Disable</button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="space-y-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-500">
                            <i data-lucide="smartphone" class="w-6 h-6"></i>
                        </div>
                        <h3 class="font-black text-xl text-gray-900 dark:text-white tracking-tight uppercase">Step 1: Scan QR</h3>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 leading-relaxed">Scan this unique code using your preferred authentication app (Google Authenticator, Bitwarden, Authy).</p>
                    
                    <div class="p-4 bg-white rounded-[2rem] inline-block shadow-inner border border-gray-100">
                        {!! $qrCodeSvg !!}
                    </div>
                    
                    <div class="p-4 bg-gray-50 dark:bg-[#1c2128] rounded-2xl border border-gray-100 dark:border-dark-border">
                        <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-1">Manual Setup Key</span>
                        <code class="text-brand-500 font-mono text-sm break-all font-bold">{{ $user->two_factor_secret }}</code>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-500">
                            <i data-lucide="key-round" class="w-6 h-6"></i>
                        </div>
                        <h3 class="font-black text-xl text-gray-900 dark:text-white tracking-tight uppercase">Step 2: Verify</h3>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 leading-relaxed">Enter the 6-digit verification code from your device to finalize the setup.</p>

                    <form action="{{ route('user.two-factor.enable') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="text" name="code" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-4 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-center tracking-[0.5em] text-2xl" required placeholder="000000">
                        <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-4 rounded-2xl transition-all shadow-xl shadow-brand-500/25 active:scale-95">
                            ENABLE PROTECTION
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
