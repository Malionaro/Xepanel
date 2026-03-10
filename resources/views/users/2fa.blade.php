@extends('layouts.app')

@section('header_title', 'Security (2FA)')

@section('content')
<div class="max-w-4xl mx-auto space-y-10">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
        <div>
            <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white uppercase italic tracking-[0.05em]">Identity Protection</h2>
            <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg font-medium">Fortify your account with multi-factor authentication protocols.</p>
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
    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 p-5 rounded-3xl flex items-center space-x-4 animate-in shake-in duration-500">
            <div class="w-10 h-10 rounded-xl bg-red-500/20 flex items-center justify-center">
                <i data-lucide="alert-octagon" class="w-6 h-6"></i>
            </div>
            <span class="text-sm font-bold">{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="glass dark:bg-dark-card border border-slate-200 dark:border-white/5 p-10 md:p-12 rounded-[3.5rem] shadow-2xl relative overflow-hidden group">
        <!-- Decoration -->
        <div class="absolute -right-24 -top-24 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl group-hover:bg-brand-500/10 transition-colors duration-700"></div>

        @if($user->two_factor_enabled)
            <div class="flex flex-col items-center text-center space-y-10 relative z-10">
                <div class="relative">
                    <div class="absolute inset-0 bg-green-500/20 blur-2xl rounded-full"></div>
                    <div class="w-24 h-24 bg-green-500/10 dark:bg-green-500/20 text-green-500 rounded-[2.5rem] flex items-center justify-center border border-green-500/30 shadow-2xl relative z-10">
                        <i data-lucide="shield-check" class="w-12 h-12"></i>
                    </div>
                </div>
                <div>
                    <h3 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase tracking-[0.1em]">Protocol Active</h3>
                    <p class="text-slate-500 dark:text-slate-400 mt-4 max-w-md mx-auto leading-relaxed font-medium">
                        Your account is currently protected by a secondary cryptographic layer. A valid verification token is required for every login attempt.
                    </p>
                </div>

                <div class="w-full pt-10 border-t border-slate-100 dark:border-white/5">
                    <form action="{{ route('user.two-factor.disable') }}" method="POST" class="max-w-sm mx-auto space-y-8">
                        @csrf
                        <div class="space-y-3 text-left">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Deactivation Token</label>
                            <input type="text" name="code" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-5 px-6 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all dark:text-white font-mono text-center tracking-[0.5em] text-3xl font-black shadow-sm" required placeholder="000000">
                        </div>
                        <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-black py-5 rounded-[1.5rem] transition-all shadow-xl shadow-red-500/25 active:scale-95 text-xs uppercase tracking-[0.2em]">DISABLE PROTECTION</button>
                    </form>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start relative z-10">
                <div class="space-y-8 text-center lg:text-left">
                    <div class="flex items-center justify-center lg:justify-start space-x-5">
                        <div class="w-14 h-14 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-500 border border-brand-500/20 shadow-lg shadow-brand-500/5">
                            <i data-lucide="smartphone" class="w-7 h-7"></i>
                        </div>
                        <h3 class="font-black text-2xl text-slate-900 dark:text-white tracking-tight uppercase tracking-widest italic">Phase 1: Sync</h3>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed font-medium">Capture this unique QR signature using your preferred mobile authenticator to generate your first token.</p>
                    
                    <div class="p-6 bg-white rounded-[2.5rem] inline-block shadow-2xl border border-slate-100 dark:border-white/5 relative group/qr overflow-hidden">
                        <div class="absolute inset-0 bg-brand-500/5 opacity-0 group-hover/qr:opacity-100 transition-opacity rounded-[2.5rem]"></div>
                        {!! $qrCodeSvg !!}
                    </div>
                    
                    <div class="p-6 bg-slate-50 dark:bg-white/5 rounded-[2rem] border border-slate-200 dark:border-white/10 space-y-3 max-w-sm mx-auto lg:mx-0">
                        <span class="text-[9px] font-black uppercase text-slate-400 tracking-[0.2em] block ml-1">Manual Cipher Key</span>
                        <div class="flex items-center justify-between">
                            <code class="text-brand-500 dark:text-brand-400 font-mono text-sm break-all font-black select-all">{{ $user->two_factor_secret }}</code>
                            <button onclick="navigator.clipboard.writeText('{{ $user->two_factor_secret }}')" class="p-2 text-slate-400 hover:text-brand-500 transition-colors ml-4 shrink-0">
                                <i data-lucide="copy" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="flex items-center justify-center lg:justify-start space-x-5">
                        <div class="w-14 h-14 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-500 border border-brand-500/20 shadow-lg shadow-brand-500/5">
                            <i data-lucide="key-round" class="w-7 h-7"></i>
                        </div>
                        <h3 class="font-black text-2xl text-slate-900 dark:text-white tracking-tight uppercase tracking-widest italic">Phase 2: Verify</h3>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed font-medium">Initialize the security policy by submitting the current 6-digit sequence from your device.</p>

                    <form action="{{ route('user.two-factor.enable') }}" method="POST" class="space-y-10">
                        @csrf
                        <div class="space-y-3">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Verification Token</label>
                            <input type="text" name="code" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-[1.5rem] py-6 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-center tracking-[0.5em] text-4xl font-black shadow-sm" required placeholder="000000">
                        </div>
                        
                        <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-6 rounded-[1.5rem] transition-all shadow-2xl shadow-brand-500/25 active:scale-[0.98] group/submit text-xs uppercase tracking-[0.2em]">
                            <i data-lucide="shield-check" class="w-5 h-5 inline-block mr-2 transition-transform group-hover/submit:scale-125"></i>
                            ACTIVATE SECURITY PROTOCOL
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
