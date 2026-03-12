@extends('layouts.app')

@section('header_title', __('panel.settings'))

@section('content')
<div class="max-w-4xl mx-auto space-y-10">
    <div class="flex items-center justify-between">
        <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white uppercase italic tracking-[0.05em]">{{ __('panel.configuration') }}</h2>
        <a href="{{ route('services.show', $service->id) }}" class="flex items-center space-x-3 px-6 py-3 rounded-2xl glass dark:bg-dark-card border-slate-200 dark:border-dark-border text-slate-600 dark:text-slate-300 text-xs font-black uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>{{ __('panel.back') }}</span>
        </a>
    </div>

    <form action="{{ route('services.update', $service->id) }}" method="POST" id="service-form" class="card bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border p-10 rounded-[2.5rem] shadow-xl space-y-8 transition-all duration-300">
        @csrf
        @method('PUT')
        <input type="hidden" name="egg_id" value="{{ $service->egg_id }}" id="egg_id_input">
        
        <!-- Egg-Specific Variables (Dynamic) -->
        <div id="egg-variables-container" class="hidden space-y-6 bg-gray-50 dark:bg-dark-bg/30 p-8 rounded-[2rem] border border-dashed border-gray-200 dark:border-dark-border">
            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-brand-500 flex items-center">
                <i data-lucide="settings-2" class="w-3 h-3 mr-2"></i>
                {{ __('panel.template_parameters') }}
            </h3>
            <div id="egg-variables-grid" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Dynamic fields will be injected here via JS -->
            </div>
        </div>

        <!-- Deployment Type Selection -->
        <div class="space-y-4">
            <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('panel.deployment_type') }}</label>
            <div class="grid grid-cols-2 gap-4">
                <input type="radio" name="type" id="type-input-process" value="process" class="hidden" {{ ($service->type ?? 'process') === 'process' ? 'checked' : '' }}>
                <input type="radio" name="type" id="type-input-docker" value="docker" class="hidden" {{ ($service->type ?? 'process') === 'docker' ? 'checked' : '' }}>

                <div onclick="selectType('process')" class="relative flex items-center justify-center p-6 bg-gray-50 dark:bg-dark-bg border-2 {{ ($service->type ?? 'process') === 'process' ? 'border-brand-500' : 'border-transparent' }} rounded-2xl cursor-pointer transition-all hover:bg-gray-100 dark:hover:bg-dark-bg/50" id="card-process">
                    <div class="flex flex-col items-center space-y-2 pointer-events-none">
                        <i data-lucide="cpu" class="w-6 h-6 {{ ($service->type ?? 'process') === 'process' ? 'text-brand-500' : 'text-gray-400' }}" id="icon-process"></i>
                        <span class="text-sm font-black {{ ($service->type ?? 'process') === 'process' ? 'text-brand-600 dark:text-brand-400' : 'text-gray-500 dark:text-dark-text-muted' }}" id="text-process">{{ __('panel.host_process') }}</span>
                    </div>
                </div>

                <div onclick="selectType('docker')" class="relative flex items-center justify-center p-6 bg-gray-50 dark:bg-dark-bg border-2 {{ ($service->type ?? 'process') === 'docker' ? 'border-brand-500' : 'border-transparent' }} rounded-2xl cursor-pointer transition-all hover:bg-gray-100 dark:hover:bg-dark-bg/50" id="card-docker">
                    <div class="flex flex-col items-center space-y-2 pointer-events-none">
                        <i data-lucide="container" class="w-6 h-6 {{ ($service->type ?? 'process') === 'docker' ? 'text-brand-500' : 'text-gray-400' }}" id="icon-docker"></i>
                        <span class="text-sm font-black {{ ($service->type ?? 'process') === 'docker' ? 'text-brand-600 dark:text-brand-400' : 'text-gray-500 dark:text-dark-text-muted' }}" id="text-docker">{{ __('panel.docker_container') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('panel.service_name') }}</label>
                <div class="relative">
                    <i data-lucide="type" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="name" value="{{ old('name', $service->name) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required>
                </div>
            </div>
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('panel.group_tags') }}</label>
                <div class="relative">
                    <i data-lucide="tag" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="tags" value="{{ old('tags', implode(', ', $service->tags ?? [])) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" placeholder="Web, DB, Production">
                </div>
            </div>
        </div>

        <!-- Process Specific Fields -->
        <div id="process-fields" class="space-y-8 {{ ($service->type ?? 'process') === 'process' ? '' : 'hidden' }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('panel.working_directory') }}</label>
                    <div class="relative">
                        <i data-lucide="folder" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="working_dir" value="{{ old('working_dir', $service->working_dir) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('panel.auto_installer_script') }}</label>
                    <div class="relative">
                        <i data-lucide="zap" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <select name="installer_script" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium appearance-none">
                            <option value="">{{ __('panel.none_installed') }}</option>
                            <option value="nodejs" {{ $service->installer_script == 'nodejs' ? 'selected' : '' }}>Run 'npm install'</option>
                            <option value="composer" {{ $service->installer_script == 'composer' ? 'selected' : '' }}>Run 'composer install'</option>
                            <option value="minecraft" {{ $service->installer_script == 'minecraft' ? 'selected' : '' }}>Download Minecraft 1.21.4 & Accept EULA</option>
                            <option value="python" {{ $service->installer_script == 'python' ? 'selected' : '' }}>Install 'requirements.txt' via pip</option>
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('panel.start_command') }}</label>
                <div class="relative">
                    <i data-lucide="play-circle" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="start_command" value="{{ old('start_command', $service->start_command) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm">
                </div>
            </div>
        </div>

        <!-- Docker Specific Fields -->
        <div id="docker-fields" class="space-y-8 {{ ($service->type ?? 'process') === 'docker' ? '' : 'hidden' }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('panel.docker_image') }}</label>
                    <div class="relative">
                        <i data-lucide="container" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="docker_image" value="{{ old('docker_image', $service->docker_image ?? '') }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" placeholder="nginx:latest">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('panel.container_data_path') }}</label>
                    <div class="relative">
                        <i data-lucide="folder-symlink" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="docker_main_mount" value="{{ old('docker_main_mount', $service->docker_main_mount ?? '/app') }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" placeholder="/app">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('panel.network') }}</label>
                    <div class="relative">
                        <i data-lucide="network" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="docker_network" value="{{ old('docker_network', $service->docker_network ?? 'bridge') }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('panel.ports') }}</label>
                    <div class="relative">
                        <i data-lucide="plug" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="docker_ports" value="{{ old('docker_ports', implode(', ', $service->docker_ports ?? [])) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" placeholder="80:80, 443:443">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4 border-t border-dashed border-gray-200 dark:border-dark-border">
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-brand-500 ml-1">{{ __('panel.custom_volumes') }}</label>
                    <div class="relative">
                        <i data-lucide="hard-drive" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-brand-500"></i>
                        <input type="text" name="docker_volumes" value="{{ old('docker_volumes', implode(', ', $service->docker_volumes ?? [])) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" placeholder="/host/path:/container/path, ...">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-brand-500 ml-1">{{ __('panel.container_name_override') }}</label>
                    <div class="relative">
                        <i data-lucide="hash" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-brand-500"></i>
                        <input type="text" name="docker_container_name" value="{{ old('docker_container_name', $service->docker_container_name ?? '') }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium">
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('panel.override_command') }}</label>
                <div class="relative">
                    <i data-lucide="terminal" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="start_command" value="{{ ($service->type ?? 'process') === 'docker' ? old('start_command', $service->start_command) : '' }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm" placeholder="sh startup.sh">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('panel.stop_command') }}</label>
                <div class="relative">
                    <i data-lucide="square" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="stop_command" value="{{ old('stop_command', $service->stop_command) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm" placeholder="pkill -f 'my-app'">
                </div>
            </div>
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">{{ __('panel.alert_webhook') }}</label>
                <div class="relative">
                    <i data-lucide="bell" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="url" name="webhook_url" value="{{ old('webhook_url', $service->webhook_url ?? '') }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white text-sm" placeholder="https://discord.com/api/webhooks/...">
                </div>
            </div>
        </div>

        <div class="bg-gray-50 dark:bg-[#1c2128] border border-gray-200 dark:border-dark-border p-6 rounded-3xl">
            <label class="flex items-center space-x-4 cursor-pointer group">
                <div class="relative flex items-center">
                    <input type="checkbox" name="auto_restart" id="auto_restart" value="1" {{ $service->auto_restart ? 'checked' : '' }} class="peer sr-only">
                    <div class="w-12 h-6 bg-gray-300 dark:bg-dark-border rounded-full peer-checked:bg-brand-500 transition-colors"></div>
                    <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-6"></div>
                </div>
                <div>
                    <span class="block text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ __('panel.auto_restart_guard') }}</span>
                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ __('panel.auto_restart_desc') }}</span>
                </div>
            </label>
        </div>

        <div class="pt-6 flex items-center space-x-4">
            <button type="submit" class="flex-1 bg-brand-500 hover:bg-brand-600 text-white font-black py-4 rounded-2xl transition-all shadow-xl shadow-brand-500/25 active:scale-95 flex items-center justify-center space-x-2">
                <i data-lucide="save" class="w-5 h-5"></i>
                <span>{{ __('panel.save_configuration') }}</span>
            </button>
        </div>
    </form>
</div>

<script>
    const eggs = @json($eggs->keyBy('id'));
    const serviceEnvVars = @json($service->env_vars ?? []);

    function renderEggVariables() {
        const eggId = document.getElementById('egg_id_input').value;
        const variablesContainer = document.getElementById('egg-variables-container');
        const variablesGrid = document.getElementById('egg-variables-grid');
        
        variablesGrid.innerHTML = '';
        variablesContainer.classList.add('hidden');

        if (eggs[eggId]) {
            const egg = eggs[eggId];
            if (egg.variables && egg.variables.length > 0) {
                variablesContainer.classList.remove('hidden');
                egg.variables.forEach(variable => {
                    const value = serviceEnvVars[variable.key] !== undefined ? serviceEnvVars[variable.key] : (variable.default || '');
                    const div = document.createElement('div');
                    div.className = 'space-y-2';
                    div.innerHTML = `
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">${variable.name}</label>
                        <div class="relative">
                            <input type="text" name="env_vars[${variable.key}]" value="${value}" 
                                class="w-full bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium text-sm">
                        </div>
                        <p class="text-[9px] text-gray-400 ml-1">${variable.description || ''}</p>
                    `;
                    variablesGrid.appendChild(div);
                });
            }
        }
    }

    function selectType(type) {
        document.getElementById('type-input-' + type).checked = true;
        const cardProc = document.getElementById('card-process');
        const cardDock = document.getElementById('card-docker');
        const fieldsProc = document.getElementById('process-fields');
        const fieldsDock = document.getElementById('docker-fields');

        if (type === 'process') {
            fieldsProc.classList.remove('hidden');
            fieldsDock.classList.add('hidden');
            cardProc.classList.add('border-brand-500');
            cardDock.classList.remove('border-brand-500');
        } else {
            fieldsProc.classList.add('hidden');
            fieldsDock.classList.remove('hidden');
            cardDock.classList.add('border-brand-500');
            cardProc.classList.remove('border-brand-500');
        }
    }

    // Initialize
    renderEggVariables();
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
