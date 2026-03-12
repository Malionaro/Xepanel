@extends('layouts.app')

@section('header_title', __('panel.roles_management'))

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white uppercase tracking-[0.05em]">{{ __('panel.roles_permissions') }}</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-2 font-medium">{{ __('panel.roles_permissions_desc') }}</p>
        </div>
        <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center space-x-2 bg-brand-500 hover:bg-brand-600 text-white px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-brand-500/25 active:scale-95">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>{{ __('panel.new_role') }}</span>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($roles as $role)
        <div class="glass dark:bg-dark-card border border-slate-200 dark:border-white/10 rounded-[2.5rem] p-8 shadow-sm group hover:shadow-xl transition-all duration-500">
            <div class="flex items-center justify-between mb-6">
                <div class="w-14 h-14 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-500 group-hover:bg-brand-500 group-hover:text-white transition-all duration-500">
                    <i data-lucide="shield-check" class="w-7 h-7"></i>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('admin.roles.edit', $role->id) }}" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/10 text-slate-400 hover:text-brand-500 transition-all">
                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                    </a>
                    @if($role->id !== 'admin' && $role->id !== 'user')
                    <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('{{ __('panel.confirm_delete_role') }}')">
                        @csrf
                        @method('DELETE')
                        <button class="p-2 rounded-xl hover:bg-red-500/10 text-slate-400 hover:text-red-500 transition-all">
                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <h3 class="text-xl font-black text-slate-900 dark:text-white mb-2">{{ $role->name }}</h3>
            <p class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest mb-6">{{ count($role->permissions) }} {{ __('panel.permissions_count') }}</p>


            <div class="flex flex-wrap gap-2">
                @foreach(array_slice($role->permissions, 0, 5) as $permission)
                    <span class="text-[8px] font-black bg-slate-100 dark:bg-slate-900 text-slate-500 dark:text-slate-400 px-3 py-1 rounded-lg border border-slate-200 dark:border-slate-800 uppercase tracking-tighter">{{ $permission }}</span>
                @endforeach
                @if(count($role->permissions) > 5)
                    <span class="text-[8px] font-black bg-slate-100 dark:bg-slate-900 text-slate-500 dark:text-slate-400 px-3 py-1 rounded-lg border border-slate-200 dark:border-slate-800 uppercase tracking-tighter">+{{ count($role->permissions) - 5 }}</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
