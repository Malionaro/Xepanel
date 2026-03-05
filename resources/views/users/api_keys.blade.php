@extends('layouts.app')

@section('header_title', 'API Management')

@section('content')
<div class="space-y-10">
    <!-- Breadcrumbs -->
    <div class="flex items-center p-1.5 glass dark:bg-white/5 border-slate-200 dark:border-white/10 rounded-2xl shadow-sm w-fit">
        <a href="{{ route('services.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 text-slate-500 dark:text-slate-400 hover:text-brand-500 transition-all group">
            <i data-lucide="server" class="w-4 h-4 transition-transform group-hover:scale-110"></i>
            <span class="text-[10px] font-black uppercase tracking-widest">My Services</span>
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 dark:text-slate-600 mx-1"></i>
        <div class="flex items-center space-x-2 px-4 py-2 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-600 dark:text-brand-400">
            <i data-lucide="key" class="w-4 h-4"></i>
            <span class="text-[10px] font-black uppercase tracking-widest">API Keys</span>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
        <div>
            <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white leading-tight">Programmable Access</h2>
            <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg font-medium">Generate cryptographic tokens for automated infrastructure management.</p>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- List of current keys -->
        <div class="lg:col-span-2 space-y-6">
            <div class="glass dark:bg-dark-card rounded-[3rem] border border-slate-200 dark:border-white/5 overflow-hidden shadow-2xl">
                <div class="px-10 py-6 bg-slate-50/50 dark:bg-white/5 border-b border-slate-100 dark:border-white/5">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Active Access Tokens</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-100 dark:border-white/5">
                                <th class="p-8">Token Identity</th>
                                <th class="p-8">Cryptographic Hint</th>
                                <th class="p-8 text-right">Revocation</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @forelse($user->api_keys ?? [] as $key)
                                <tr class="group hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="p-8">
                                        <div class="flex items-center space-x-5">
                                            <div class="w-12 h-12 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-500 border border-brand-500/20 group-hover:scale-110 transition-transform shadow-lg shadow-brand-500/5">
                                                <i data-lucide="shield-check" class="w-6 h-6"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $key['name'] }}</p>
                                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1 block">Issued: {{ \Carbon\Carbon::parse($key['created_at'])->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-8">
                                        <code class="px-3 py-1.5 bg-slate-100 dark:bg-white/5 rounded-xl font-mono text-xs text-brand-600 dark:text-brand-400 border border-slate-200 dark:border-white/10">
                                            {{ substr($key['token'], 0, 12) }}...
                                        </code>
                                    </td>
                                    <td class="p-8 text-right">
                                        <form action="{{ route('user.api-keys.destroy', $key['id']) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-400 hover:text-red-500 transition-all hover:bg-red-500/10 border border-slate-200 dark:border-white/10" onclick="return confirm('CRITICAL: Permanently revoke this access token?')">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-24 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-6 opacity-40">
                                            <div class="relative">
                                                <div class="absolute inset-0 bg-brand-500/20 blur-3xl rounded-full"></div>
                                                <div class="w-24 h-24 bg-white dark:bg-slate-900 rounded-[2.5rem] flex items-center justify-center text-5xl shadow-2xl border border-slate-100 dark:border-white/5 relative z-10">🔑</div>
                                            </div>
                                            <div class="max-w-xs mx-auto">
                                                <p class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">No Tokens Detected</p>
                                                <p class="text-sm text-slate-500 font-medium mt-2 leading-relaxed">External authentication protocols are currently inactive for this account.</p>
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
        <div class="lg:col-span-1 space-y-8">
            <form action="{{ route('user.api-keys.store') }}" method="POST" class="glass dark:bg-dark-card p-10 rounded-[3rem] border border-slate-200 dark:border-white/5 shadow-2xl space-y-8 relative overflow-hidden group">
                <div class="absolute -right-24 -top-24 w-48 h-48 bg-brand-500/5 rounded-full blur-3xl group-hover:bg-brand-500/10 transition-colors duration-700"></div>
                
                @csrf
                <div class="flex items-center space-x-4 mb-4 relative z-10">
                    <div class="w-12 h-12 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-500 border border-brand-500/20">
                        <i data-lucide="plus-circle" class="w-7 h-7"></i>
                    </div>
                    <h3 class="font-black text-2xl text-slate-900 dark:text-white tracking-tight">Issue Key</h3>
                </div>
                
                <div class="space-y-3 relative z-10">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Token Label</label>
                    <input type="text" name="name" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm placeholder:text-slate-400 dark:placeholder:text-slate-600 shadow-sm" required placeholder="e.g. CI/CD Pipeline">
                </div>
                
                <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-5 rounded-[1.5rem] transition-all shadow-xl shadow-brand-500/25 active:scale-95 flex items-center justify-center space-x-3 group/submit relative z-10">
                    <i data-lucide="zap" class="w-5 h-5 text-white transition-transform group-hover/submit:scale-125"></i>
                    <span class="text-xs uppercase tracking-[0.2em]">GENERATE CREDENTIALS</span>
                </button>
            </form>

            @php
                $lastAdded = null;
                if(session('status') && isset($user->api_keys) && count($user->api_keys) > 0) {
                    $lastAdded = end($user->api_keys);
                }
            @endphp
            @if($lastAdded)
                <div class="p-8 bg-amber-500/10 border border-amber-500/20 rounded-[2.5rem] space-y-5 animate-pulse relative overflow-hidden">
                    <div class="flex items-center space-x-3 text-amber-600 dark:text-amber-400 relative z-10">
                        <i data-lucide="alert-octagon" class="w-6 h-6"></i>
                        <span class="text-xs font-black uppercase tracking-widest">Immediate Action Required</span>
                    </div>
                    <div class="relative z-10">
                        <input type="text" value="{{ $lastAdded['token'] }}" class="w-full bg-white dark:bg-slate-950 border border-amber-500/30 rounded-2xl py-4 px-6 text-slate-900 dark:text-white font-mono text-sm select-all outline-none shadow-inner" readonly id="new-token-input">
                    </div>
                    <p class="text-[10px] text-amber-600 dark:text-amber-500 font-bold uppercase tracking-tight leading-relaxed relative z-10">
                        This credential will be encrypted and hidden permanently after page navigation. Secure it now.
                    </p>
                </div>
            @endif

            <div class="p-8 glass dark:bg-dark-card border border-slate-200 dark:border-white/5 rounded-[2rem] shadow-lg">
                <h4 class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-4 flex items-center">
                    <i data-lucide="terminal" class="w-3.5 h-3.5 mr-2"></i>
                    Integration Protocol
                </h4>
                <div class="p-4 bg-slate-900 dark:bg-black rounded-2xl border border-slate-800 shadow-inner">
                    <code class="text-[10px] text-emerald-400 font-mono break-all leading-relaxed">
                        curl -H "Auth: Bearer fp_..." {{ url('/api/services') }}
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
