@extends('layouts.app')

@section('header_title', 'Edit Account')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex items-center space-x-2 text-gray-500 dark:text-gray-400 mb-2">
        <a href="{{ route('dashboard') }}" class="hover:text-brand-500 transition-colors">Dashboard</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <a href="{{ route('users.index') }}" class="hover:text-brand-500 transition-colors">User Management</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-900 dark:text-white font-bold">Edit User</span>
    </div>

    <div class="flex items-center justify-between">
        <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Modify Account: {{ $user->name }}</h2>
        <a href="{{ route('users.index') }}" class="flex items-center space-x-2 bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border text-gray-700 dark:text-gray-300 px-5 py-2.5 rounded-2xl text-sm font-bold transition-all hover:shadow-md">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Cancel</span>
        </a>
    </div>

    <form action="{{ route('users.update', $user->id) }}" method="POST" class="card bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border p-10 rounded-[2.5rem] shadow-xl space-y-8 transition-all duration-300">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Full Name</label>
                <div class="relative">
                    <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required>
                </div>
            </div>
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Email Address</label>
                <div class="relative">
                    <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">New Password (Optional)</label>
                <div class="relative">
                    <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="password" name="password" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" minlength="6" placeholder="Leave blank to keep current">
                </div>
            </div>
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">System Role</label>
                <div class="relative">
                    <i data-lucide="shield" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <select name="role" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium appearance-none">
                        <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>Regular User</option>
                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                </div>
            </div>
        </div>

        <!-- Resource Quotas -->
        <div class="pt-8 border-t border-dashed border-gray-200 dark:border-dark-border space-y-6">
            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-brand-500 flex items-center">
                <i data-lucide="zap" class="w-3 h-3 mr-2"></i>
                Resource Quotas (Account Limits)
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Max RAM (MB)</label>
                    <div class="relative">
                        <i data-lucide="database" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="number" name="max_ram_mb" value="{{ old('max_ram_mb', $user->max_ram_mb ?? 4096) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Max CPU (%)</label>
                    <div class="relative">
                        <i data-lucide="cpu" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="number" name="max_cpu_percent" value="{{ old('max_cpu_percent', $user->max_cpu_percent ?? 200) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Max Disk Space (MB)</label>
                    <div class="relative">
                        <i data-lucide="hard-drive" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="number" name="max_disk_mb" value="{{ old('max_disk_mb', $user->max_disk_mb ?? 10240) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Max Services</label>
                    <div class="relative">
                        <i data-lucide="layers" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="number" name="max_services" value="{{ old('max_services', $user->max_services ?? 5) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required>
                    </div>
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
                <i data-lucide="save" class="w-5 h-5"></i>
                <span>UPDATE ACCOUNT DETAILS</span>
            </button>
        </div>
    </form>

    <div class="p-6 bg-brand-50 dark:bg-brand-900/10 border border-brand-100 dark:border-brand-900/20 rounded-[2rem] flex items-start space-x-4">
        <i data-lucide="info" class="w-6 h-6 text-brand-600 shrink-0 mt-1"></i>
        <p class="text-sm text-brand-800 dark:text-brand-400 font-medium leading-relaxed">
            Updating account details takes effect immediately. If you change the role of a user, their access permissions to services will remain, but their administrative capabilities will change.
        </p>
    </div>
</div>

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
