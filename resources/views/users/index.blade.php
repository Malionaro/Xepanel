@extends('layouts.app')

@section('header_title', __('panel.user_management'))

@section('content')
<div class="space-y-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-8">
        <div>
            <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white uppercase tracking-[0.05em]">{{ __('panel.system_accounts') }}</h2>
            <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg font-medium">{{ __('panel.manage_user_access') }}</p>
        </div>
        <a href="{{ route('users.create') }}" class="flex items-center space-x-3 bg-brand-500 hover:bg-brand-600 text-white px-8 py-4 rounded-[2rem] text-xs font-black uppercase tracking-[0.2em] shadow-2xl shadow-brand-500/25 transition-all hover:-translate-y-1 active:scale-95 shrink-0">
            <i data-lucide="user-plus" class="w-5 h-5"></i>
            <span>{{ __('panel.register_identity') }}</span>
        </a>
    </div>

    @if(session('status'))
        <div class="bg-green-500/10 border border-green-500/20 text-green-600 dark:text-green-400 p-5 rounded-3xl flex items-center space-x-4 animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="w-10 h-10 rounded-xl bg-green-500/20 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
            <span class="text-sm font-bold">{{ session('status') }}</span>
        </div>
    @endif

    <div class="glass dark:bg-dark-card rounded-[3rem] border border-slate-200 dark:border-white/5 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-white/5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-100 dark:border-white/5">
                        <th class="px-10 py-6">{{ __('panel.core_identity') }}</th>
                        <th class="px-10 py-6">{{ __('panel.privilege_level') }}</th>
                        <th class="px-10 py-6 text-right">{{ __('panel.operations') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @foreach($users as $user)
                        <tr class="group hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-10 py-8">
                                <div class="flex items-center space-x-5">
                                    <div class="w-14 h-14 rounded-2xl bg-brand-500 flex items-center justify-center text-xs font-black text-white shadow-xl shadow-brand-500/20 uppercase shrink-0 group-hover:scale-110 transition-transform">
                                        {{ substr($user->name, 0, 2) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-base font-black text-slate-900 dark:text-white truncate tracking-tight">{{ $user->name }}</p>
                                        <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest truncate mt-1">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-10 py-8 whitespace-nowrap">
                                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-[0.2em] {{ $user->role == 'admin' ? 'bg-purple-500/10 text-purple-500 border border-purple-500/20 shadow-[0_0_15px_rgba(168,85,247,0.1)]' : 'bg-blue-500/10 text-blue-500 border border-blue-500/20 shadow-[0_0_15px_rgba(59,130,246,0.1)]' }}">
                                    <i data-lucide="{{ $user->role == 'admin' ? 'shield-check' : 'user' }}" class="w-3.5 h-3.5 mr-2 shrink-0"></i>
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-10 py-8 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end space-x-3">
                                    <a href="{{ route('users.edit', $user->id) }}" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-400 hover:text-brand-500 transition-all hover:bg-brand-500/10 border border-slate-200 dark:border-white/10" title="{{ __('panel.modify_access') }}">
                                        <i data-lucide="settings-2" class="w-5 h-5"></i>
                                    </a>
                                    @if($user->id != Auth::id())
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('panel.confirm_delete_user') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-400 hover:text-red-500 transition-all hover:bg-red-500/10 border border-slate-200 dark:border-white/10">
                                            <i data-lucide="user-minus" class="w-5 h-5"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
