@extends('layouts.app')

@section('header_title', 'Permissions')

@section('content')
<div class="max-w-4xl mx-auto space-y-10">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white leading-tight">Access Control</h2>
            <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg font-medium">Delegate infrastructure authority to trusted collaborators.</p>
        </div>
        <a href="{{ route('services.show', $service->id) }}" class="flex items-center space-x-3 px-6 py-3 rounded-2xl glass dark:bg-dark-card border-slate-200 dark:border-dark-border text-slate-600 dark:text-slate-300 text-xs font-black uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-800 transition-all hover:-translate-x-1">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back</span>
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

    <div class="bg-brand-500/10 dark:bg-brand-500/5 border border-brand-500/20 p-8 rounded-[2.5rem] flex items-start space-x-6 relative overflow-hidden group transition-all duration-500">
        <div class="absolute -right-12 -top-12 w-32 h-32 bg-brand-500/10 rounded-full blur-3xl group-hover:bg-brand-500/20 transition-all duration-700"></div>
        <div class="w-14 h-14 rounded-[1.2rem] bg-brand-500 text-white flex items-center justify-center shrink-0 shadow-2xl shadow-brand-500/40 relative z-10">
            <i data-lucide="lock" class="w-7 h-7"></i>
        </div>
        <div class="relative z-10">
            <h3 class="font-black text-brand-900 dark:text-brand-400 uppercase tracking-[0.1em] text-sm">Policy Enforcement</h3>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 leading-relaxed font-medium">
                Authorized users below will inherit granular management rights over this instance. 
                <span class="text-brand-600 dark:text-brand-500 font-bold underline decoration-brand-500/30 underline-offset-4 cursor-help" title="Admins bypass all restricted policies">Superusers</span> are exempt from restriction policies.
            </p>
        </div>
    </div>

    <form action="{{ route('services.permissions.update', $service->id) }}" method="POST" class="glass dark:bg-dark-card p-10 rounded-[3rem] border border-slate-200 dark:border-white/5 shadow-2xl space-y-10 relative overflow-hidden">
        @csrf
        
        <div class="space-y-8 relative z-10">
            <div class="flex items-center space-x-4 border-b border-slate-100 dark:border-white/5 pb-6">
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-400">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
                <h3 class="font-black text-xl text-slate-900 dark:text-white tracking-tight uppercase tracking-widest">Collaborator Registry</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($users as $user)
                    <label class="group flex items-center space-x-5 p-5 rounded-3xl border border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-white/[0.02] hover:border-brand-500/50 hover:bg-white dark:hover:bg-white/5 transition-all cursor-pointer relative overflow-hidden">
                        <div class="absolute inset-0 bg-brand-500/[0.02] opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        
                        <div class="relative flex items-center justify-center shrink-0">
                            <input type="checkbox" name="users[]" value="{{ $user->id }}" {{ in_array($user->id, $service->allowed_users ?? []) ? 'checked' : '' }} class="peer sr-only">
                            <div class="w-7 h-7 border-2 border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 peer-checked:bg-brand-500 peer-checked:border-brand-500 transition-all flex items-center justify-center shadow-sm">
                                <i data-lucide="check" class="w-4 h-4 text-white opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                            </div>
                        </div>

                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 text-slate-600 dark:text-slate-400 flex items-center justify-center font-black text-xs shrink-0 border border-white dark:border-slate-700 shadow-sm uppercase">
                            {{ substr($user->name, 0, 2) }}
                        </div>

                        <div class="flex-1 min-w-0">
                            <span class="block text-sm font-black text-slate-900 dark:text-white truncate tracking-tight">{{ $user->name }}</span>
                            <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-0.5 truncate">{{ $user->email }}</span>
                        </div>
                    </label>
                @empty
                    <div class="col-span-full py-16 flex flex-col items-center justify-center text-center space-y-4 glass rounded-[2.5rem] border-dashed border-2 border-slate-200 dark:border-white/5 opacity-50">
                        <i data-lucide="user-minus" class="w-12 h-12 text-slate-300"></i>
                        <p class="text-slate-500 font-bold uppercase tracking-widest text-[10px]">No auxiliary users detected</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="pt-10 border-t border-slate-100 dark:border-white/5 flex justify-end relative z-10">
            <button type="submit" class="w-full md:w-auto flex items-center justify-center space-x-3 bg-brand-500 hover:bg-brand-600 text-white font-black px-12 py-5 rounded-[1.5rem] transition-all shadow-xl shadow-brand-500/25 active:scale-95 group/submit">
                <i data-lucide="shield-check" class="w-5 h-5 text-white transition-transform group-hover/submit:scale-110"></i>
                <span class="text-xs uppercase tracking-[0.2em]">ENFORCE ACCESS POLICY</span>
            </button>
        </div>
    </form>
</div>

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
