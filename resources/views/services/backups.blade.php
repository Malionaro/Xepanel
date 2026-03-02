@extends('layouts.app')

@section('header_title', 'Service Backups')

@section('content')
<div class="space-y-8">
    <div class="flex items-center space-x-2 text-gray-500 dark:text-gray-400 mb-2">
        <a href="{{ route('dashboard') }}" class="hover:text-brand-500 transition-colors">Dashboard</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <a href="{{ route('services.show', $service->id) }}" class="hover:text-brand-500 transition-colors">{{ $service->name }}</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-900 dark:text-white font-bold">Backups</span>
    </div>

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Snapshots & Archives</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage historical versions of your service data.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('services.show', $service->id) }}" class="flex items-center space-x-2 bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border text-gray-700 dark:text-gray-300 px-5 py-2.5 rounded-2xl text-sm font-bold transition-all hover:shadow-md">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back</span>
            </a>
            <form action="{{ route('services.backups.store', $service->id) }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center space-x-2 bg-brand-500 hover:bg-brand-600 text-white px-6 py-2.5 rounded-2xl text-sm font-bold shadow-lg shadow-brand-500/25 transition-all active:scale-95">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>CREATE BACKUP</span>
                </button>
            </form>
        </div>
    </div>

    @if(session('status'))
        <div class="bg-green-100 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 text-green-700 dark:text-green-400 p-4 rounded-2xl flex items-center space-x-3">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span class="text-sm font-medium">{{ session('status') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 text-red-600 dark:text-red-400 p-4 rounded-2xl flex items-center space-x-3">
            <i data-lucide="alert-octagon" class="w-5 h-5"></i>
            <span class="text-sm font-bold">{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="card bg-white dark:bg-dark-card rounded-[2rem] border border-gray-200 dark:border-dark-border overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-dark-hover text-[10px] font-black uppercase tracking-widest text-gray-400 border-b border-gray-200 dark:border-dark-border">
                    <th class="p-6">Filename</th>
                    <th class="p-6">Size</th>
                    <th class="p-6 text-center">Created At</th>
                    <th class="p-6 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-dark-border">
                @forelse($backups as $backup)
                    <tr class="hover:bg-gray-50 dark:hover:bg-[#1c2128] transition-colors group">
                        <td class="p-6">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-500">
                                    <i data-lucide="file-archive" class="w-4 h-4"></i>
                                </div>
                                <span class="font-mono text-sm font-bold text-gray-900 dark:text-white">{{ $backup['name'] }}</span>
                            </div>
                        </td>
                        <td class="p-6">
                            <span class="px-2 py-1 bg-gray-100 dark:bg-dark-bg rounded-lg text-[10px] font-black text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-dark-border">{{ $backup['size'] }}</span>
                        </td>
                        <td class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ $backup['time'] }}
                        </td>
                        <td class="p-6 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('services.backups.download', ['id' => $service->id, 'filename' => $backup['name']]) }}" class="p-2 text-gray-400 hover:text-brand-500 transition-colors" title="Download">
                                    <i data-lucide="download" class="w-5 h-5"></i>
                                </a>
                                <form action="{{ route('services.backups.destroy', ['id' => $service->id, 'filename' => $backup['name']]) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition-colors" onclick="return confirm('Delete this backup permanently?')">
                                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-20 text-center">
                            <div class="flex flex-col items-center justify-center space-y-4">
                                <div class="w-16 h-16 bg-gray-100 dark:bg-dark-bg rounded-full flex items-center justify-center text-3xl">📦</div>
                                <div>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">No backups found</p>
                                    <p class="text-sm text-gray-500 italic">Start securing your data by creating your first archive.</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
