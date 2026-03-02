@extends('layouts.app')

@section('header_title', 'Create Account')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex items-center space-x-2 text-gray-500 dark:text-gray-400 mb-2">
        <a href="{{ route('dashboard') }}" class="hover:text-brand-500 transition-colors">Dashboard</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <a href="{{ route('users.index') }}" class="hover:text-brand-500 transition-colors">User Management</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-900 dark:text-white font-bold">Add User</span>
    </div>

    <div class="flex items-center justify-between">
        <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Provision Account</h2>
        <a href="{{ route('users.index') }}" class="flex items-center space-x-2 bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border text-gray-700 dark:text-gray-300 px-5 py-2.5 rounded-2xl text-sm font-bold transition-all hover:shadow-md">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Cancel</span>
        </a>
    </div>

    <form action="{{ route('users.store') }}" method="POST" class="card bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border p-10 rounded-[2.5rem] shadow-xl space-y-8 transition-all duration-300">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Full Name</label>
                <div class="relative">
                    <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required placeholder="John Doe">
                </div>
            </div>
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Email Address</label>
                <div class="relative">
                    <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required placeholder="john@example.com">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Secure Password</label>
                <div class="relative">
                    <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="password" name="password" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required minlength="6" placeholder="••••••••">
                </div>
            </div>
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">System Role</label>
                <div class="relative">
                    <i data-lucide="shield" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <select name="role" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium appearance-none">
                        <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Regular User</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 text-red-600 dark:text-red-400 p-4 rounded-2xl flex items-center space-x-3">
                <i data-lucide="alert-octagon" class="w-5 h-5"></i>
                <span class="text-sm font-bold">{{ $errors->first() }}</span>
            </div>
        @endif

        <div class="pt-6">
            <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-4 rounded-2xl transition-all shadow-xl shadow-brand-500/25 active:scale-95 flex items-center justify-center space-x-2">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
                <span>CREATE SYSTEM ACCOUNT</span>
            </button>
        </div>
    </form>

    <div class="p-6 bg-brand-50 dark:bg-brand-900/10 border border-brand-100 dark:border-brand-900/20 rounded-[2rem] flex items-start space-x-4">
        <i data-lucide="info" class="w-6 h-6 text-brand-600 shrink-0 mt-1"></i>
        <p class="text-sm text-brand-800 dark:text-brand-400 font-medium leading-relaxed">
            New users will be able to log in immediately. By default, regular users cannot see any services until you grant them specific permissions on the service detail page.
        </p>
    </div>
</div>

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
