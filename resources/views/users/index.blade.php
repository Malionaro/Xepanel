@extends('layouts.app')

@section('header_title', 'User Management')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">System Accounts</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage administrative and regular user access.</p>
        </div>
        <a href="{{ route('users.create') }}" class="flex items-center space-x-2 bg-brand-500 hover:bg-brand-600 text-white px-6 py-2.5 rounded-2xl text-sm font-bold shadow-lg shadow-brand-500/25 transition-all hover:-translate-y-0.5 active:translate-y-0">
            <i data-lucide="user-plus" class="w-4 h-4 text-white"></i>
            <span>Add New User</span>
        </a>
    </div>

    @if(session('status'))
        <div class="bg-green-100 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 text-green-700 dark:text-green-400 p-4 rounded-2xl flex items-center space-x-3">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span class="text-sm font-medium">{{ session('status') }}</span>
        </div>
    @endif

    <div class="card bg-white dark:bg-dark-card rounded-[2rem] border border-gray-200 dark:border-dark-border overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[600px] md:min-w-0">
                <thead>
                    <tr class="bg-gray-50 dark:bg-dark-hover text-[10px] font-black uppercase tracking-widest text-gray-400 border-b border-gray-200 dark:border-dark-border">
                        <th class="p-6">Identity</th>
                        <th class="p-6">Role</th>
                        <th class="p-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-dark-border">
                    @foreach($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-[#1c2128] transition-colors group">
                            <td class="p-4 md:p-6">
                                <div class="flex items-center space-x-3 md:space-x-4">
                                    <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-brand-500 flex items-center justify-center text-[9px] md:text-[10px] font-black text-white shadow-lg shadow-brand-500/20 uppercase shrink-0">
                                        {{ substr($user->name, 0, 2) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs md:text-sm font-bold text-gray-900 dark:text-white truncate">{{ $user->name }}</p>
                                        <p class="text-[9px] md:text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-tighter truncate">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 md:p-6 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[9px] md:text-[10px] font-black uppercase tracking-widest {{ $user->role == 'admin' ? 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 border border-purple-100 dark:border-purple-900/30' : 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 border border-blue-100 dark:border-blue-900/30' }}">
                                    <i data-lucide="{{ $user->role == 'admin' ? 'shield-check' : 'user' }}" class="w-3 h-3 mr-1.5 shrink-0"></i>
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="p-4 md:p-6 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end space-x-2 md:space-x-3">
                                    <a href="{{ route('users.edit', $user->id) }}" class="p-1.5 md:p-2 text-gray-400 hover:text-brand-500 transition-colors" title="Edit User">
                                        <i data-lucide="edit-3" class="w-4 h-4 md:w-5 md:h-5"></i>
                                    </a>
                                    @if($user->id != Auth::id())
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 md:p-2 text-gray-400 hover:text-red-500 transition-colors">
                                            <i data-lucide="user-minus" class="w-4 h-4 md:w-5 md:h-5"></i>
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
