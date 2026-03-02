@extends('layouts.app')

@section('header_title', 'System Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex items-center space-x-2 text-gray-500 dark:text-gray-400 mb-2">
        <a href="{{ route('dashboard') }}" class="hover:text-brand-500 transition-colors">Dashboard</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-900 dark:text-white font-bold">Global Settings</span>
    </div>

    <div>
        <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Panel Configuration</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Adjust global parameters and system-wide behavior.</p>
    </div>

    @if(session('status'))
        <div class="bg-green-100 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 text-green-700 dark:text-green-400 p-4 rounded-2xl flex items-center space-x-3">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span class="text-sm font-medium">{{ session('status') }}</span>
        </div>
    @endif

    <form action="{{ route('settings.update') }}" method="POST" class="space-y-8">
        @csrf

        <div class="card bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border p-10 rounded-[2.5rem] shadow-xl space-y-8 transition-all duration-300">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Panel Brand Name</label>
                    <div class="relative">
                        <i data-lucide="layout" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="panel_name" value="{{ old('panel_name', $settings['panel_name']) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Max Backup Size (MB)</label>
                    <div class="relative">
                        <i data-lucide="archive" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="number" name="max_backup_size_mb" value="{{ old('max_backup_size_mb', $settings['max_backup_size_mb']) }}" min="1" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">UI Theme Preference</label>
                    <div class="relative">
                        <i data-lucide="moon" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <select name="ui_theme" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium appearance-none">
                            <option value="system" {{ old('ui_theme', $settings['ui_theme'] ?? 'system') === 'system' ? 'selected' : '' }}>System Default</option>
                            <option value="dark" {{ old('ui_theme', $settings['ui_theme'] ?? '') === 'dark' ? 'selected' : '' }}>Always Dark Mode</option>
                            <option value="light" {{ old('ui_theme', $settings['ui_theme'] ?? '') === 'light' ? 'selected' : '' }}>Always Light Mode</option>
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">System Timezone</label>
                    <div class="relative">
                        <i data-lucide="globe" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="default_timezone" value="{{ old('default_timezone', $settings['default_timezone'] ?? 'UTC') }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required placeholder="Europe/Berlin">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Default Docker Network</label>
                    <div class="relative">
                        <i data-lucide="network" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="docker_default_network" value="{{ old('docker_default_network', $settings['docker_default_network'] ?? 'bridge') }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Max Log File Size (MB)</label>
                    <div class="relative">
                        <i data-lucide="file-text" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="number" name="max_log_size_mb" value="{{ old('max_log_size_mb', $settings['max_log_size_mb']) }}" min="1" max="100" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Docker Base Path</label>
                    <div class="relative">
                        <i data-lucide="container" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="docker_base_path" value="{{ old('docker_base_path', $settings['docker_base_path'] ?? '/var/lib/panel/docker') }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required placeholder="/var/lib/panel/docker">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4 border-t border-gray-100 dark:border-dark-border">
                <label class="flex items-center space-x-4 cursor-pointer group">
                    <div class="relative flex items-center">
                        <input type="checkbox" name="enable_public_api" id="enable_public_api" value="1" {{ ($settings['enable_public_api'] ?? false) ? 'checked' : '' }} class="peer sr-only">
                        <div class="w-12 h-6 bg-gray-300 dark:bg-dark-border rounded-full peer-checked:bg-brand-500 transition-colors"></div>
                        <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-6"></div>
                    </div>
                    <div>
                        <span class="block text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight">Public API Access</span>
                        <span class="block text-[10px] text-gray-500">Allow interaction via API tokens.</span>
                    </div>
                </label>

                <label class="flex items-center space-x-4 cursor-pointer group">
                    <div class="relative flex items-center">
                        <input type="checkbox" name="maintenance_mode" id="maintenance_mode" value="1" {{ ($settings['maintenance_mode'] ?? false) ? 'checked' : '' }} class="peer sr-only">
                        <div class="w-12 h-6 bg-gray-300 dark:bg-dark-border rounded-full peer-checked:bg-red-500 transition-colors"></div>
                        <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-6"></div>
                    </div>
                    <div>
                        <span class="block text-sm font-black text-red-600 dark:text-red-400 uppercase tracking-tight">Maintenance Mode</span>
                        <span class="block text-[10px] text-gray-500">Lock panel for non-admin users.</span>
                    </div>
                </label>
            </div>

            <div class="pt-6">
                <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-4 rounded-2xl transition-all shadow-xl shadow-brand-500/25 active:scale-95 flex items-center justify-center space-x-2">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    <span>APPLY GLOBAL SETTINGS</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
