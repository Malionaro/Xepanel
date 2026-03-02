@extends('layouts.app')

@section('header_title', 'Access Control')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex items-center space-x-2 text-gray-500 dark:text-gray-400 mb-2">
        <a href="{{ route('dashboard') }}" class="hover:text-brand-500 transition-colors">Dashboard</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <a href="{{ route('services.show', $service->id) }}" class="hover:text-brand-500 transition-colors">{{ $service->name }}</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-900 dark:text-white font-bold">Permissions</span>
    </div>

    <div class="flex items-center justify-between">
        <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Service Access</h2>
        <a href="{{ route('services.show', $service->id) }}" class="flex items-center space-x-2 bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border text-gray-700 dark:text-gray-300 px-5 py-2.5 rounded-2xl text-sm font-bold transition-all hover:shadow-md">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back</span>
        </a>
    </div>

    @if(session('status'))
        <div class="bg-green-100 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 text-green-700 dark:text-green-400 p-4 rounded-2xl flex items-center space-x-3">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span class="text-sm font-medium">{{ session('status') }}</span>
        </div>
    @endif

    <div class="bg-brand-50 dark:bg-brand-900/10 border border-brand-100 dark:border-brand-900/20 p-6 rounded-[2rem] flex items-start space-x-4 transition-colors duration-300">
        <div class="w-12 h-12 rounded-2xl bg-brand-500 text-white flex items-center justify-center shrink-0 shadow-lg shadow-brand-500/20">
            <i data-lucide="shield" class="w-6 h-6"></i>
        </div>
        <div>
            <h3 class="font-black text-brand-900 dark:text-brand-400 uppercase tracking-tight text-sm">Permission Management</h3>
            <p class="text-sm text-brand-800 dark:text-brand-500 mt-1 leading-relaxed font-medium">
                Define which users are allowed to see and manage this specific service. 
                <strong class="dark:text-brand-300">Administrators</strong> always have full access to all instances and cannot be restricted.
            </p>
        </div>
    </div>

    <form action="{{ route('services.permissions.update', $service->id) }}" method="POST" class="card bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border p-10 rounded-[2.5rem] shadow-xl space-y-8 transition-all duration-300">
        @csrf
        
        <div class="space-y-6">
            <div class="flex items-center space-x-3 border-b border-gray-100 dark:border-dark-border pb-4">
                <i data-lucide="users" class="w-5 h-5 text-gray-400"></i>
                <h3 class="font-black text-xl text-gray-900 dark:text-white tracking-tight uppercase">Select Authorized Users</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($users as $user)
                    <label class="group flex items-center space-x-4 p-4 rounded-2xl border border-gray-100 dark:border-[#1c2128] bg-gray-50/50 dark:bg-[#1c2128]/50 hover:border-brand-500 hover:bg-white dark:hover:bg-dark-hover transition-all cursor-pointer relative overflow-hidden">
                        <div class="absolute inset-0 bg-brand-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        
                        <div class="relative flex items-center justify-center shrink-0">
                            <input type="checkbox" name="users[]" value="{{ $user->id }}" {{ in_array($user->id, $service->allowed_users ?? []) ? 'checked' : '' }} class="peer sr-only">
                            <div class="w-6 h-6 border-2 border-gray-300 dark:border-dark-border rounded-lg bg-white dark:bg-dark-bg peer-checked:bg-brand-500 peer-checked:border-brand-500 transition-all flex items-center justify-center">
                                <i data-lucide="check" class="w-4 h-4 text-white opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                            </div>
                        </div>

                        <div class="w-10 h-10 rounded-full bg-brand-100 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400 flex items-center justify-center font-black text-xs shrink-0">
                            {{ substr($user->name, 0, 2) }}
                        </div>

                        <div class="flex-1 min-w-0">
                            <span class="block text-sm font-black text-gray-900 dark:text-white truncate">{{ $user->name }}</span>
                            <span class="block text-[10px] text-gray-500 uppercase tracking-tighter">{{ $user->email }}</span>
                        </div>
                    </label>
                @empty
                    <div class="col-span-full py-12 flex flex-col items-center justify-center text-center space-y-3 bg-gray-50 dark:bg-[#1c2128]/30 rounded-3xl border border-dashed border-gray-300 dark:border-dark-border">
                        <i data-lucide="user-minus" class="w-10 h-10 text-gray-300"></i>
                        <p class="text-gray-500 font-medium italic">No regular users found in the system.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="pt-6 border-t border-gray-100 dark:border-dark-border">
            <button type="submit" class="w-full md:w-auto flex items-center justify-center space-x-2 bg-brand-500 hover:bg-brand-600 text-white font-black px-10 py-4 rounded-2xl transition-all shadow-xl shadow-brand-500/25 active:scale-95">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
                <span>UPDATE ACCESS PERMISSIONS</span>
            </button>
        </div>
    </form>
</div>

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
