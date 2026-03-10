@extends('layouts.app')

@section('header_title', 'Programmable Access')

@section('content')
<div class="space-y-10">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
        <div>
            <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white uppercase italic tracking-[0.05em]">API Schlüssel</h2>
            <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg font-medium">Generate and manage cryptographic tokens for automated infrastructure management.</p>
        </div>
    </div>

    @if(session('status'))
        <div class="bg-green-500/10 border border-green-500/20 text-green-600 dark:text-green-400 p-6 rounded-3xl flex items-center space-x-4 animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="w-12 h-12 rounded-2xl bg-green-500/20 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-7 h-7"></i>
            </div>
            <span class="text-sm font-bold">{{ session('status') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- List of current keys -->
        <div class="lg:col-span-2 space-y-8">
            <div class="glass dark:bg-dark-card rounded-[3.5rem] border border-slate-200 dark:border-white/5 overflow-hidden shadow-2xl">
                <div class="px-10 py-8 bg-slate-50/50 dark:bg-white/5 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
                    <h3 class="text-xs font-black uppercase tracking-[0.3em] text-slate-400">Active Access Tokens</h3>
                    <div class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse glow-green"></div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-100 dark:border-white/5">
                                <th class="px-10 py-6">Token Identity</th>
                                <th class="px-10 py-6">Cryptographic Hint</th>
                                <th class="px-10 py-6 text-right">Revocation</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @forelse($user->api_keys ?? [] as $key)
                                <tr class="group hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-10 py-8">
                                        <div class="flex items-center space-x-6">
                                            <div class="w-14 h-14 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-500 border border-brand-500/20 group-hover:scale-110 group-hover:bg-brand-500 group-hover:text-white transition-all duration-500 shadow-xl shadow-brand-500/5">
                                                <i data-lucide="shield-check" class="w-7 h-7"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-base font-black text-slate-900 dark:text-white truncate uppercase tracking-tight">{{ $key['name'] }}</p>
                                                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em] mt-1.5 block">Issued: {{ \Carbon\Carbon::parse($key['created_at'])->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-10 py-8">
                                        <code class="px-4 py-2 bg-slate-100 dark:bg-white/5 rounded-xl font-mono text-xs text-brand-600 dark:text-brand-400 border border-slate-200 dark:border-white/10 shadow-inner">
                                            {{ substr($key['token'], 0, 12) }}••••••••
                                        </code>
                                    </td>
                                    <td class="px-10 py-8 text-right">
                                        <form action="{{ route('user.api-keys.destroy', $key['id']) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 hover:text-red-500 transition-all hover:bg-red-500/10 border border-slate-200 dark:border-white/10 shadow-sm active:scale-90" onclick="return confirm('CRITICAL: Permanently revoke this access token?')">
                                                <i data-lucide="trash-2" class="w-6 h-6"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-10 py-32 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-8 opacity-40">
                                            <div class="relative">
                                                <div class="absolute inset-0 bg-brand-500/20 blur-3xl rounded-full"></div>
                                                <div class="w-28 h-28 bg-white dark:bg-slate-900 rounded-[3rem] flex items-center justify-center text-6xl shadow-2xl border border-slate-100 dark:border-white/5 relative z-10 animate-bounce">🔑</div>
                                            </div>
                                            <div class="max-w-sm mx-auto">
                                                <p class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight italic">No Tokens Detected</p>
                                                <p class="text-sm text-slate-500 font-medium mt-3 leading-relaxed uppercase tracking-widest">External authentication protocols are currently inactive for this account.</p>
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

        <!-- Add new key form -->
        <div class="lg:col-span-1 space-y-10">
            <form action="{{ route('user.api-keys.store') }}" method="POST" class="glass dark:bg-dark-card p-10 md:p-12 rounded-[3.5rem] border border-slate-200 dark:border-white/5 shadow-2xl space-y-10 relative overflow-hidden group">
                <div class="absolute -right-24 -top-24 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl group-hover:bg-brand-500/10 transition-colors duration-700"></div>
                
                @csrf
                <div class="flex items-center space-x-5 mb-4 relative z-10">
                    <div class="w-14 h-14 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-500 border border-brand-500/20 shadow-lg shadow-brand-500/5 group-hover:scale-110 transition-transform">
                        <i data-lucide="plus-circle" class="w-8 h-8"></i>
                    </div>
                    <h3 class="font-black text-2xl text-slate-900 dark:text-white tracking-tight uppercase italic">Issue Key</h3>
                </div>
                
                <div class="space-y-4 relative z-10">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Token Identifier</label>
                    <input type="text" name="name" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-5 px-8 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-black text-sm placeholder:text-slate-400 dark:placeholder:text-slate-600 shadow-sm" required placeholder="e.g. CI/CD Pipeline">
                </div>
                
                <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-6 rounded-[2rem] transition-all shadow-2xl shadow-brand-500/25 active:scale-95 flex items-center justify-center space-x-4 group/submit relative z-10">
                    <i data-lucide="zap" class="w-6 h-6 text-white transition-transform group-hover/submit:scale-125"></i>
                    <span class="text-xs uppercase tracking-[0.3em]">GENERATE CREDENTIALS</span>
                </button>
            </form>

            @php
                $lastAdded = null;
                if(session('status') && isset($user->api_keys) && count($user->api_keys) > 0) {
                    $lastAdded = end($user->api_keys);
                }
            @endphp
            @if($lastAdded)
                <div class="p-10 bg-amber-500/10 border border-amber-500/20 rounded-[3.5rem] space-y-6 animate-in zoom-in duration-500 relative overflow-hidden shadow-xl ring-2 ring-amber-500/30">
                    <div class="flex items-center space-x-4 text-amber-600 dark:text-amber-400 relative z-10">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center">
                            <i data-lucide="alert-octagon" class="w-6 h-6"></i>
                        </div>
                        <span class="text-xs font-black uppercase tracking-[0.2em]">Sensitive Resource</span>
                    </div>
                    <div class="relative z-10">
                        <input type="text" value="{{ $lastAdded['token'] }}" class="w-full bg-white dark:bg-slate-950 border border-amber-500/30 rounded-2xl py-5 px-8 text-slate-900 dark:text-white font-mono text-sm select-all outline-none shadow-inner font-bold" readonly id="new-token-input">
                    </div>
                    <p class="text-[10px] text-amber-600 dark:text-amber-500 font-black uppercase tracking-[0.1em] leading-relaxed relative z-10 italic">
                        This credential will be encrypted and hidden permanently after page navigation. Secure it now.
                    </p>
                </div>
            @endif

            <div class="p-8 glass dark:bg-dark-card border border-slate-200 dark:border-white/5 rounded-[2.5rem] shadow-lg group">
                <h4 class="text-[10px] font-black uppercase text-slate-400 tracking-[0.3em] mb-6 flex items-center">
                    <i data-lucide="terminal" class="w-4 h-4 mr-3 text-brand-500"></i>
                    Integration Protocol
                </h4>
                <div class="p-6 bg-slate-950 rounded-2xl border border-white/5 shadow-inner group-hover:border-brand-500/20 transition-colors">
                    <code class="text-[10px] text-emerald-400 font-mono break-all leading-relaxed font-bold">
                        curl -H "Auth: Bearer fp_••••" {{ url('/api/services') }}
                    </code>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
