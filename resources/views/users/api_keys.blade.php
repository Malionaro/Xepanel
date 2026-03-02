@extends('layouts.app')

@section('header_title', 'API Management')

@section('content')
<div class="max-w-5xl mx-auto space-y-8">
    <div class="flex items-center space-x-2 text-gray-500 dark:text-gray-400 mb-2">
        <a href="{{ route('dashboard') }}" class="hover:text-brand-500 transition-colors">Dashboard</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-900 dark:text-white font-bold">API Keys</span>
    </div>

    <div>
        <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Programmable Access</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Generate tokens to interact with the panel via scripts or external tools.</p>
    </div>

    @if(session('status'))
        <div class="bg-green-100 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 text-green-700 dark:text-green-400 p-4 rounded-2xl flex items-center space-x-3">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span class="text-sm font-medium">{{ session('status') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- List of current keys -->
        <div class="lg:col-span-2 space-y-4">
            <div class="card bg-white dark:bg-dark-card rounded-[2rem] border border-gray-200 dark:border-dark-border overflow-hidden shadow-sm">
                <div class="px-6 py-4 bg-gray-50 dark:bg-dark-hover border-b border-gray-200 dark:border-dark-border">
                    <h3 class="text-xs font-black uppercase tracking-widest text-gray-400">Your Active Tokens</h3>
                </div>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] font-black uppercase tracking-widest text-gray-400 border-b border-gray-100 dark:border-dark-border">
                            <th class="p-6">Label</th>
                            <th class="p-6">Token Hint</th>
                            <th class="p-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-dark-border">
                        @forelse($user->api_keys ?? [] as $key)
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#1c2128] transition-colors group">
                                <td class="p-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-lg bg-pink-50 dark:bg-pink-900/20 flex items-center justify-center text-pink-500">
                                            <i data-lucide="key" class="w-4 h-4"></i>
                                        </div>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $key['name'] }}</span>
                                    </div>
                                    <p class="text-[9px] text-gray-400 mt-1 uppercase tracking-tighter">Issued: {{ $key['created_at'] }}</p>
                                </td>
                                <td class="p-6">
                                    <code class="px-2 py-1 bg-gray-100 dark:bg-dark-bg rounded-lg font-mono text-xs text-brand-600 dark:text-brand-400 border border-gray-200 dark:border-dark-border">
                                        {{ substr($key['token'], 0, 10) }}...
                                    </code>
                                </td>
                                <td class="p-6 text-right">
                                    <form action="{{ route('user.api-keys.destroy', $key['id']) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition-colors" onclick="return confirm('Revoke this API Key permanently?')">
                                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="p-20 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-4">
                                        <div class="w-16 h-16 bg-gray-100 dark:bg-dark-bg rounded-full flex items-center justify-center text-3xl opacity-50">🔑</div>
                                        <p class="text-sm text-gray-500 italic">No API keys created yet.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add new key form -->
        <div class="lg:col-span-1 space-y-6">
            <form action="{{ route('user.api-keys.store') }}" method="POST" class="card bg-white dark:bg-dark-card p-8 rounded-[2.5rem] border border-gray-200 dark:border-dark-border shadow-xl space-y-6">
                @csrf
                <div class="flex items-center space-x-3 mb-2">
                    <div class="w-10 h-10 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-500">
                        <i data-lucide="plus-circle" class="w-6 h-6"></i>
                    </div>
                    <h3 class="font-black text-xl text-gray-900 dark:text-white tracking-tight">Issue Key</h3>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Key Label</label>
                    <input type="text" name="name" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium text-sm" required placeholder="e.g. Discord Bot">
                </div>
                
                <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-4 rounded-2xl transition-all shadow-lg shadow-brand-500/25 active:scale-95 flex items-center justify-center space-x-2">
                    <i data-lucide="zap" class="w-5 h-5"></i>
                    <span>GENERATE TOKEN</span>
                </button>
            </form>

            @php
                $lastAdded = null;
                if(session('status') && isset($user->api_keys) && count($user->api_keys) > 0) {
                    $lastAdded = end($user->api_keys);
                }
            @endphp
            @if($lastAdded)
                <div class="p-6 bg-yellow-50 dark:bg-yellow-900/10 border border-yellow-100 dark:border-yellow-900/20 rounded-[2rem] space-y-4 animate-bounce">
                    <div class="flex items-center space-x-2 text-yellow-700 dark:text-yellow-400">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        <span class="text-xs font-black uppercase tracking-widest">Secret Key - Copy Now!</span>
                    </div>
                    <div class="relative">
                        <input type="text" value="{{ $lastAdded['token'] }}" class="w-full bg-white dark:bg-dark-bg border border-yellow-300 dark:border-yellow-900/50 rounded-xl py-3 px-4 text-gray-900 dark:text-white font-mono text-xs select-all outline-none" readonly id="new-token-input">
                    </div>
                    <p class="text-[10px] text-yellow-600 dark:text-yellow-500 italic leading-relaxed">
                        For security reasons, this key will never be shown again once you refresh or leave this page.
                    </p>
                </div>
            @endif

            <div class="p-6 bg-gray-50 dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-2xl">
                <h4 class="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-3">Quick Integration</h4>
                <code class="block p-3 bg-gray-900 dark:bg-black rounded-xl text-[10px] text-green-400 font-mono break-all border border-gray-800 shadow-inner">
                    curl -H "Authorization: Bearer fp_..." {{ url('/api/services') }}
                </code>
            </div>
        </div>
    </div>
</div>

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
