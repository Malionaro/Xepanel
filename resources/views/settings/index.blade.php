@extends('layouts.app')

@section('header_title', __('System Settings'))

@section('content')
<div class="max-w-4xl mx-auto space-y-8 pb-12">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">{{ __('Panel Configuration') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Adjust global parameters and system-wide behavior.') }}</p>
        </div>
        <div class="flex items-center space-x-2 text-xs font-bold text-gray-400 uppercase tracking-widest bg-white dark:bg-dark-card px-4 py-2 rounded-xl border border-gray-200 dark:border-dark-border shadow-sm">
            <span class="w-2 h-2 rounded-full bg-brand-500 animate-pulse"></span>
            <span>{{ __('Live System') }}</span>
        </div>
    </div>

    @if(session('status'))
        <div class="bg-green-100 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 text-green-700 dark:text-green-400 p-4 rounded-2xl flex items-center space-x-3">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span class="text-sm font-medium">{{ session('status') }}</span>
        </div>
    @endif

    <form action="{{ route('settings.update') }}" method="POST" class="space-y-8">
        @csrf

        <!-- Branding & UI Customization -->
        <div class="glass dark:bg-dark-card border border-slate-200 dark:border-white/5 p-10 rounded-[3rem] shadow-2xl space-y-10 relative overflow-hidden group">
            <div class="absolute -right-24 -top-24 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl group-hover:bg-brand-500/10 transition-colors duration-700"></div>
            
            <h3 class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 flex items-center relative z-10">
                <i data-lucide="palette" class="w-4 h-4 mr-3 text-brand-500"></i>
                {{ __('Visual Identity & Branding') }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 relative z-10">
                <div class="space-y-4">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('Primary Brand Color') }}</label>
                    <div class="flex items-center space-x-4">
                        <div class="relative group/color">
                            <input type="color" name="brand_primary_color" value="{{ $settings['brand_primary_color'] ?? '#8b5cf6' }}" class="w-16 h-16 rounded-2xl cursor-pointer bg-transparent border-none outline-none overflow-hidden [&::-webkit-color-swatch-wrapper]:p-0 [&::-webkit-color-swatch]:border-none [&::-webkit-color-swatch]:rounded-2xl shadow-lg shadow-brand-500/20">
                            <div class="absolute inset-0 rounded-2xl border-2 border-white/20 pointer-events-none group-hover/color:border-white/40 transition-colors"></div>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Panel Accent') }}</p>
                            <p class="text-[10px] text-slate-500 font-medium leading-relaxed">{{ __('This color will be used for buttons, active states, and glow effects.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('Panel Logo (Lucide Icon)') }}</label>
                    <div class="relative">
                        <i data-lucide="box" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                        <input type="text" name="panel_icon" value="{{ $settings['panel_icon'] ?? 'layers' }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 pl-14 pr-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm" placeholder="e.g. layers, box, server, zap">
                    </div>
                    <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest ml-1">Powered by <a href="https://lucide.dev/icons" target="_blank" class="text-brand-500 hover:underline">Lucide Icons</a></p>
                </div>
            </div>

            <div class="space-y-4 relative z-10 pt-6 border-t border-slate-100 dark:border-white/5">
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('External Branding URL') }}</label>
                <div class="relative">
                    <i data-lucide="link" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                    <input type="text" name="branding_logo_url" value="{{ $settings['branding_logo_url'] ?? '' }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 pl-14 pr-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium text-sm shadow-sm" placeholder="https://cdn.yourhost.com/logo.png">
                </div>
            </div>
        </div>

        <!-- General Settings -->
        <div class="glass dark:bg-dark-card border border-slate-200 dark:border-white/5 p-10 rounded-[3rem] shadow-xl space-y-10 relative overflow-hidden">
            <h3 class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 flex items-center">
                <i data-lucide="settings" class="w-4 h-4 mr-3 text-indigo-500"></i>
                {{ __('Global Infrastructure Control') }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('Panel Name') }}</label>
                    <div class="relative">
                        <i data-lucide="layout" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="panel_name" value="{{ old('panel_name', $settings['panel_name']) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('Branding Logo URL') }}</label>
                    <div class="relative">
                        <i data-lucide="image" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="branding_logo_url" value="{{ old('branding_logo_url', $settings['branding_logo_url'] ?? '') }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" placeholder="https://example.com/logo.png">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('System Timezone') }}</label>
                    <div class="relative">
                        <i data-lucide="globe" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="default_timezone" value="{{ old('default_timezone', $settings['default_timezone'] ?? 'UTC') }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('Panel Language') }}</label>
                    <div class="relative">
                        <i data-lucide="languages" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <select name="panel_language" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium appearance-none">
                            <option value="en" {{ ($settings['panel_language'] ?? 'en') === 'en' ? 'selected' : '' }}>English (US)</option>
                            <option value="de" {{ ($settings['panel_language'] ?? '') === 'de' ? 'selected' : '' }}>Deutsch (DE)</option>
                        </select>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('UI-Design') }}</label>
                    <div class="relative">
                        <i data-lucide="palette" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <select name="ui_theme" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium appearance-none">
                            <option value="system" {{ ($settings['ui_theme'] ?? 'system') === 'system' ? 'selected' : '' }}>System Default</option>
                            <option value="dark" {{ ($settings['ui_theme'] ?? '') === 'dark' ? 'selected' : '' }}>Dark Mode</option>
                            <option value="light" {{ ($settings['ui_theme'] ?? '') === 'light' ? 'selected' : '' }}>Light Mode</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4 border-t border-gray-100 dark:border-dark-border">
                <label class="flex items-center space-x-4 cursor-pointer group">
                    <div class="relative flex items-center">
                        <input type="checkbox" name="allow_registration" value="1" {{ ($settings['allow_registration'] ?? false) ? 'checked' : '' }} class="peer sr-only">
                        <div class="w-12 h-6 bg-gray-300 dark:bg-dark-border rounded-full peer-checked:bg-blue-500 transition-colors"></div>
                        <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-6"></div>
                    </div>
                    <div>
                        <span class="block text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ __('Allow Registration') }}</span>
                        <span class="block text-[10px] text-gray-500">{{ __('Allow new users to sign up.') }}</span>
                    </div>
                </label>

                <label class="flex items-center space-x-4 cursor-pointer group">
                    <div class="relative flex items-center">
                        <input type="checkbox" name="maintenance_mode" value="1" {{ ($settings['maintenance_mode'] ?? false) ? 'checked' : '' }} class="peer sr-only">
                        <div class="w-12 h-6 bg-gray-300 dark:bg-dark-border rounded-full peer-checked:bg-red-500 transition-colors"></div>
                        <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-6"></div>
                    </div>
                    <div>
                        <span class="block text-sm font-black text-red-600 dark:text-red-400 uppercase tracking-tight">{{ __('Maintenance Mode') }}</span>
                        <span class="block text-[10px] text-gray-500">{{ __('Admin-only access.') }}</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- User Quota Defaults -->
        <div class="card bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border p-8 rounded-[2rem] shadow-sm space-y-6">
            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-400 flex items-center">
                <i data-lucide="users" class="w-3 h-3 mr-2 text-purple-500"></i>
                {{ __('New User Default Quotas') }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('Default RAM (MB)') }}</label>
                    <input type="number" name="default_user_ram_mb" value="{{ $settings['default_user_ram_mb'] ?? 4096 }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-none transition-all dark:text-white font-medium" required>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('Default CPU (%)') }}</label>
                    <input type="number" name="default_user_cpu_percent" value="{{ $settings['default_user_cpu_percent'] ?? 200 }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-none transition-all dark:text-white font-medium" required>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('Default Disk (MB)') }}</label>
                    <input type="number" name="default_user_disk_mb" value="{{ $settings['default_user_disk_mb'] ?? 10240 }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-none transition-all dark:text-white font-medium" required>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('Max Services') }}</label>
                    <input type="number" name="default_user_services" value="{{ $settings['default_user_services'] ?? 5 }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-none transition-all dark:text-white font-medium" required>
                </div>
            </div>
        </div>

        <!-- Infrastructure Settings -->
        <div class="card bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border p-8 rounded-[2rem] shadow-sm space-y-6">
            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-400 flex items-center">
                <i data-lucide="cpu" class="w-3 h-3 mr-2 text-indigo-500"></i>
                {{ __('Docker & Infrastructure') }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('Docker Base Path') }}</label>
                    <input type="text" name="docker_base_path" value="{{ $settings['docker_base_path'] ?? '/var/lib/panel/docker' }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all dark:text-white font-medium" required>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('Default Docker Network') }}</label>
                    <input type="text" name="docker_default_network" value="{{ $settings['docker_default_network'] ?? 'bridge' }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all dark:text-white font-medium" required>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('Session Lifetime (Minutes)') }}</label>
                    <input type="number" name="session_lifetime" value="{{ $settings['session_lifetime'] ?? 120 }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all dark:text-white font-medium" required>
                </div>
            </div>
        </div>

        <!-- Notifications & Logging -->
        <div class="card bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border p-8 rounded-[2rem] shadow-sm space-y-6">
            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-400 flex items-center">
                <i data-lucide="bell" class="w-3 h-3 mr-2 text-green-500"></i>
                {{ __('Notifications & Logging') }}
            </h3>

            <div class="space-y-4">
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('Global Discord Webhook URL') }}</label>
                    <div class="relative">
                        <i data-lucide="hash" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="global_webhook_url" value="{{ $settings['global_webhook_url'] ?? '' }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition-all dark:text-white font-medium" placeholder="https://discord.com/api/webhooks/...">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-50 dark:border-dark-border">
                    <div class="space-y-2">
                        <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('Discord Bot Token') }}</label>
                        <input type="password" name="discord_bot_token" value="{{ $settings['discord_bot_token'] ?? '' }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-xs">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('Discord Public Key') }}</label>
                        <input type="text" name="discord_public_key" value="{{ $settings['discord_public_key'] ?? '' }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-xs">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('Discord Client ID') }}</label>
                    <input type="text" name="discord_client_id" value="{{ $settings['discord_client_id'] ?? '' }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-xs">
                </div>
                <p class="text-[10px] text-gray-400 italic">Interactions URL: <span class="font-mono">{{ url('/api/discord/interactions') }}</span></p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-50 dark:border-dark-border">
                    <div class="space-y-2">
                        <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('Max Backup Size (MB)') }}</label>
                        <input type="number" name="max_backup_size_mb" value="{{ $settings['max_backup_size_mb'] }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition-all dark:text-white font-medium" required>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('Max Log Size (MB)') }}</label>
                        <input type="number" name="max_log_size_mb" value="{{ $settings['max_log_size_mb'] }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition-all dark:text-white font-medium" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-5 rounded-[2rem] transition-all shadow-xl shadow-brand-500/25 active:scale-[0.98] flex items-center justify-center space-x-3">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
                <span class="text-lg">{{ __('SAVE PANEL CONFIGURATION') }}</span>
            </button>
        </div>
    </form>
</div>

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
