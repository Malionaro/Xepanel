@extends('layouts.app')

@section('header_title', 'Account Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-10">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white leading-tight">My Account</h2>
            <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg font-medium">Manage your personal information and password.</p>
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

    <form action="{{ route('user.account.update') }}" method="POST" class="glass dark:bg-dark-card p-10 rounded-[3rem] border border-slate-200 dark:border-white/5 shadow-2xl space-y-10">
        @csrf
        @method('PUT')
        
        <div class="space-y-8">
            <div class="flex items-center space-x-4 border-b border-slate-100 dark:border-white/5 pb-6">
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-400">
                    <i data-lucide="user" class="w-5 h-5"></i>
                </div>
                <h3 class="font-black text-xl text-slate-900 dark:text-white uppercase tracking-widest">Personal Details</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-3">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full px-5 py-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm font-medium text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all shadow-sm" required>
                    @error('name') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-3">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-5 py-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm font-medium text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all shadow-sm" required>
                    @error('email') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="space-y-8">
            <div class="flex items-center space-x-4 border-b border-slate-100 dark:border-white/5 pb-6">
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-400">
                    <i data-lucide="lock" class="w-5 h-5"></i>
                </div>
                <h3 class="font-black text-xl text-slate-900 dark:text-white uppercase tracking-widest">Security</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-3">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">New Password (Optional)</label>
                    <input type="password" name="password" class="w-full px-5 py-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm font-medium text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all shadow-sm">
                    @error('password') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Leave blank to keep current password</p>
                </div>

                <div class="space-y-3">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="w-full px-5 py-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm font-medium text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all shadow-sm">
                </div>
            </div>
        </div>

        <div class="pt-8 flex justify-end">
            <button type="submit" class="w-full md:w-auto flex items-center justify-center space-x-3 bg-brand-500 hover:bg-brand-600 text-white font-black px-12 py-5 rounded-[1.5rem] transition-all shadow-xl shadow-brand-500/25 active:scale-95 group/submit">
                <i data-lucide="save" class="w-5 h-5 text-white transition-transform group-hover/submit:scale-110"></i>
                <span class="text-xs uppercase tracking-[0.2em]">Update Account</span>
            </button>
        </div>
    </form>
</div>
@endsection
