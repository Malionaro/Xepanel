@extends('layouts.app')

@section('header_title', __('panel.system_settings'))

@section('content')
<div class="max-w-4xl mx-auto space-y-10 pb-20">
    <!-- System Update Section -->
    <div class="glass dark:bg-dark-card border border-slate-200 dark:border-white/5 p-10 md:p-12 rounded-[3.5rem] shadow-2xl relative overflow-hidden group">
        <div class="absolute -right-24 -top-24 w-64 h-64 bg-blue-500/5 rounded-full blur-3xl group-hover:bg-blue-500/10 transition-colors"></div>
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-10 relative z-10">
            <div class="flex items-center space-x-6">
                <div class="w-16 h-16 md:w-20 md:h-20 rounded-3xl bg-blue-500/10 flex items-center justify-center text-blue-500 border border-blue-500/20 shadow-xl shadow-blue-500/5 group-hover:scale-110 transition-transform">
                    <i data-lucide="refresh-cw" class="w-8 h-8 md:w-10 md:h-10"></i>
                </div>
                <div>
                    <h3 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic">{{ __('panel.system_core_update') }}</h3>
                    <div class="flex flex-wrap items-center gap-3 mt-2">
                        <div class="px-3 py-1 rounded-lg bg-blue-500/10 border border-blue-500/20 text-[9px] font-mono font-bold text-blue-500 uppercase tracking-widest">
                            Build: {{ trim(@shell_exec('git rev-parse --short HEAD')) ?: 'STABLE' }}
                        </div>
                        <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-700"></span>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-widest">Version: <span class="text-slate-900 dark:text-white">1.0.0-beta1</span></p>
                    </div>
                </div>
            </div>
            <div id="update-action-container">
                <button onclick="checkUpdates()" id="btn-check-update" class="flex items-center space-x-3 px-10 py-4 rounded-[2rem] bg-blue-500 text-white text-[10px] font-black uppercase tracking-[0.2em] shadow-2xl shadow-blue-500/25 hover:bg-blue-600 transition-all hover:-translate-y-1 active:scale-95 shrink-0">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    <span>{{ __('panel.check_updates') }}</span>
                </button>
            </div>
        </div>

        <div id="update-info" class="hidden mt-10 p-8 bg-slate-50 dark:bg-white/[0.02] border border-slate-100 dark:border-white/5 rounded-[2.5rem] relative z-10 animate-in slide-in-from-top-4 duration-500">
            <div class="flex items-start space-x-6">
                <div class="w-12 h-12 rounded-2xl bg-blue-500/20 flex items-center justify-center text-blue-500 shrink-0">
                    <i data-lucide="git-commit" class="w-6 h-6"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p id="update-message" class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight"></p>
                    <p class="text-[10px] text-slate-500 mt-2 uppercase tracking-[0.2em] font-bold">Latest Signature: <span id="latest-sha" class="font-mono text-blue-500 ml-2">...</span></p>
                </div>
                <button onclick="runUpdate()" class="px-8 py-3.5 rounded-2xl bg-green-500 text-white text-[10px] font-black uppercase tracking-widest shadow-xl shadow-green-500/20 hover:bg-green-600 transition-all hover:-translate-y-0.5 active:scale-95">
                    {{ __('panel.deploy_update') }}
                </button>
            </div>
        </div>
    </div>

    <form action="{{ route('settings.update') }}" method="POST" class="space-y-12">
        @csrf

        <!-- Branding & UI Customization -->
        <div class="glass dark:bg-dark-card border border-slate-200 dark:border-white/5 p-10 md:p-12 rounded-[3.5rem] shadow-2xl space-y-12 relative overflow-hidden group">
            <div class="absolute -right-24 -top-24 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl group-hover:bg-brand-500/10 transition-colors duration-700 pointer-events-none"></div>
            
            <h3 class="!mt-0 text-xs font-black uppercase tracking-[0.3em] text-slate-400 flex items-center relative z-10">
                <i data-lucide="palette" class="w-4 h-4 mr-3 text-brand-500"></i>
                {{ __('panel.visual_identity') }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 relative z-10">
                <div class="space-y-5">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('panel.brand_accent') }}</label>
                    <div class="flex items-center space-x-6">
                        <div class="relative group/color shrink-0">
                            <input type="color" name="brand_primary_color" value="{{ $settings['brand_primary_color'] ?? '#8b5cf6' }}" class="w-20 h-20 rounded-3xl cursor-pointer bg-transparent border-none outline-none overflow-hidden [&::-webkit-color-swatch-wrapper]:p-0 [&::-webkit-color-swatch]:border-none [&::-webkit-color-swatch]:rounded-3xl shadow-2xl shadow-brand-500/20 transition-transform group-hover/color:scale-105">
                            <div class="absolute inset-0 rounded-3xl border-2 border-white/20 pointer-events-none group-hover/color:border-white/40 transition-colors"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ __('panel.core_colorway') }}</p>
                            <p class="text-[10px] text-slate-500 font-medium leading-relaxed mt-1 uppercase tracking-widest">{{ __('panel.core_colorway_desc') }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-5">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('panel.system_icon') }}</label>
                    <div class="relative">
                        <i data-lucide="box" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                        <input type="text" name="panel_icon" value="{{ $settings['panel_icon'] ?? 'layers' }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 pl-14 pr-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm" placeholder="e.g. layers, box, server, zap">
                    </div>
                    <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest ml-1">{{ __('panel.lucide_icons') }} <a href="https://lucide.dev/icons/" target="_blank" class="text-brand-500 hover:underline">Lucide icon signature</a>.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 relative z-10 pt-10 border-t border-slate-100 dark:border-white/5">
                <div class="space-y-5">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('panel.github_repo') }}</label>
                    <div class="relative">
                        <i data-lucide="github" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                        <input type="text" name="github_repo" value="{{ $settings['github_repo'] ?? 'malo/panel' }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 pl-14 pr-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm shadow-sm" placeholder="username/repo">
                    </div>
                </div>
                <div class="space-y-5">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('panel.github_token') }}</label>
                    <div class="relative">
                        <i data-lucide="key" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                        <input type="password" name="github_token" value="{{ $settings['github_token'] ?? '' }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 pl-14 pr-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm shadow-sm" placeholder="ghp_••••••••">
                    </div>
                </div>
            </div>
        </div>

        <!-- Global Control -->
        <div class="glass dark:bg-dark-card border border-slate-200 dark:border-white/5 p-10 md:p-12 rounded-[3.5rem] shadow-2xl space-y-12 relative overflow-hidden group">
            <h3 class="text-xs font-black uppercase tracking-[0.3em] text-slate-400 flex items-center relative z-10">
                <i data-lucide="settings" class="w-4 h-4 mr-3 text-indigo-500"></i>
                {{ __('panel.global_system_control') }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 relative z-10">
                <div class="space-y-3">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('panel.panel_name') }}</label>
                    <input type="text" name="panel_name" value="{{ old('panel_name', $settings['panel_name']) }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm" required>
                </div>
                <div class="space-y-3">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('panel.branding_logo_url') }}</label>
                    <input type="text" name="branding_logo_url" value="{{ old('branding_logo_url', $settings['branding_logo_url'] ?? '') }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm" placeholder="https://example.com/logo.png">
                </div>
                <div class="space-y-3">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('panel.timezone') }}</label>
                    <input type="text" name="default_timezone" value="{{ old('default_timezone', $settings['default_timezone'] ?? 'UTC') }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm shadow-sm" required>
                </div>
                <div class="space-y-3">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('panel.primary_language') }}</label>
                    <div class="relative">
                        <select name="panel_language" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm appearance-none cursor-pointer">
                            <option value="en" {{ ($settings['panel_language'] ?? 'en') === 'en' ? 'selected' : '' }}>English (US)</option>
                            <option value="de" {{ ($settings['panel_language'] ?? '') === 'de' ? 'selected' : '' }}>Deutsch (DE)</option>
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 pointer-events-none"></i>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 pt-10 border-t border-slate-100 dark:border-white/5 relative z-10">
                <label class="flex items-center space-x-6 cursor-pointer group p-6 rounded-[2rem] hover:bg-slate-50 dark:hover:bg-white/5 transition-all border border-transparent hover:border-slate-100 dark:hover:border-white/5">
                    <div class="relative flex items-center shrink-0">
                        <input type="checkbox" name="allow_registration" value="1" {{ ($settings['allow_registration'] ?? false) ? 'checked' : '' }} class="peer sr-only">
                        <div class="w-14 h-7 bg-slate-200 dark:bg-white/10 rounded-full peer-checked:bg-blue-500 transition-colors shadow-inner"></div>
                        <div class="absolute left-1 top-1 w-5 h-5 bg-white rounded-full transition-transform peer-checked:translate-x-7 shadow-lg"></div>
                    </div>
                    <div>
                        <span class="block text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight italic">{{ __('panel.allow_registration') }}</span>
                        <span class="block text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">{{ __('panel.allow_registration_desc') }}</span>
                    </div>
                </label>

                <label class="flex items-center space-x-6 cursor-pointer group p-6 rounded-[2rem] hover:bg-red-500/5 transition-all border border-transparent hover:border-red-500/10">
                    <div class="relative flex items-center shrink-0">
                        <input type="checkbox" name="maintenance_mode" value="1" {{ ($settings['maintenance_mode'] ?? false) ? 'checked' : '' }} class="peer sr-only">
                        <div class="w-14 h-7 bg-slate-200 dark:bg-white/10 rounded-full peer-checked:bg-red-500 transition-colors shadow-inner"></div>
                        <div class="absolute left-1 top-1 w-5 h-5 bg-white rounded-full transition-transform peer-checked:translate-x-7 shadow-lg"></div>
                    </div>
                    <div>
                        <span class="block text-sm font-black text-red-600 dark:text-red-400 uppercase tracking-tight italic text-glow-red">{{ __('panel.maintenance_mode') }}</span>
                        <span class="block text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">{{ __('panel.maintenance_mode_desc') }}</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- Infrastructure & Limits -->
        <div class="glass dark:bg-dark-card border border-slate-200 dark:border-white/5 p-10 md:p-12 rounded-[3.5rem] shadow-2xl space-y-12 relative overflow-hidden group">
            <h3 class="text-xs font-black uppercase tracking-[0.3em] text-slate-400 flex items-center relative z-10">
                <i data-lucide="zap" class="w-4 h-4 mr-3 text-brand-500"></i>
                {{ __('panel.infra_allocation') }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 relative z-10">
                <div class="space-y-3">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('panel.default_ram') }}</label>
                    <input type="number" name="default_user_ram_mb" value="{{ $settings['default_user_ram_mb'] ?? 4096 }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm" required>
                </div>
                <div class="space-y-3">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('panel.default_cpu') }}</label>
                    <input type="number" name="default_user_cpu_percent" value="{{ $settings['default_user_cpu_percent'] ?? 200 }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm" required>
                </div>
                <div class="space-y-3">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('panel.default_disk') }}</label>
                    <input type="number" name="default_user_disk_mb" value="{{ $settings['default_user_disk_mb'] ?? 10240 }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm" required>
                </div>
                <div class="space-y-3">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('panel.max_services') }}</label>
                    <input type="number" name="default_user_services" value="{{ $settings['default_user_services'] ?? 5 }}" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm" required>
                </div>
            </div>
        </div>

        <div class="pt-6">
            <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-6 rounded-[2.5rem] transition-all shadow-2xl shadow-brand-500/25 active:scale-[0.98] flex items-center justify-center space-x-4 group/submit">
                <i data-lucide="check-circle" class="w-7 h-7 transition-transform group-hover/submit:scale-125"></i>
                <span class="text-xl uppercase tracking-[0.2em] italic">{{ __('panel.commit_config') }}</span>
            </button>
        </div>
    </form>
</div>

<script>
    function checkUpdates() {
        const btn = document.getElementById('btn-check-update');
        const info = document.getElementById('update-info');
        
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="refresh-cw" class="w-4 h-4 animate-spin mr-3"></i><span>Scanning Core...</span>';
        if(typeof lucide !== 'undefined') lucide.createIcons();

        fetch('{{ route('settings.check_update') }}')
            .then(res => res.json())
            .then(data => {
                if (data.has_update) {
                    info.classList.remove('hidden');
                    document.getElementById('update-message').textContent = data.message;
                    document.getElementById('latest-sha').textContent = data.latest;
                    btn.innerHTML = '<i data-lucide="alert-circle" class="w-4 h-4 mr-3"></i><span>Update Protocol Ready</span>';
                    btn.classList.replace('bg-blue-500', 'bg-amber-500');
                } else {
                    btn.innerHTML = '<i data-lucide="check" class="w-4 h-4 mr-3"></i><span>System Integrity Stable</span>';
                    btn.classList.replace('bg-blue-500', 'bg-green-500');
                }
            })
            .catch(err => {
                btn.innerHTML = '<i data-lucide="alert-octagon" class="w-4 h-4 mr-3"></i><span>Link Failed</span>';
                btn.classList.replace('bg-blue-500', 'bg-red-500');
            })
            .finally(() => {
                if(typeof lucide !== 'undefined') lucide.createIcons();
            });
    }

    function runUpdate() {
        if (!confirm('This will pull the latest code and run migrations. The panel might be offline for a few seconds. Continue?')) return;

        fetch('{{ route('settings.run_update') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            alert('Update initiated. The panel will now refresh.');
            setTimeout(() => window.location.reload(), 2000);
        });
    }

    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
