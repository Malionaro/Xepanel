@extends('layouts.app')

@section('header_title', 'Create Service')

@section('content')
<div class="max-w-4xl mx-auto space-y-10">
    <!-- Breadcrumbs -->
    <div class="flex items-center p-1.5 glass dark:bg-white/5 border-slate-200 dark:border-white/10 rounded-2xl shadow-sm w-fit">
        <a href="{{ route('services.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 text-slate-500 dark:text-slate-400 hover:text-brand-500 transition-all group">
            <i data-lucide="server" class="w-4 h-4 transition-transform group-hover:scale-110"></i>
            <span class="text-[10px] font-black uppercase tracking-widest">My Services</span>
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 dark:text-slate-600 mx-1"></i>
        <div class="flex items-center space-x-2 px-4 py-2 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-600 dark:text-brand-400">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span class="text-[10px] font-black uppercase tracking-widest">New Service</span>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white">Deploy Instance</h2>
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-6 py-3 rounded-2xl glass dark:bg-dark-card border-slate-200 dark:border-dark-border text-slate-600 dark:text-slate-300 text-xs font-black uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
            <i data-lucide="x" class="w-4 h-4"></i>
            <span>Cancel</span>
        </a>
    </div>

    <!-- Template Quick Select (Eggs) -->
    <div class="card bg-brand-500 rounded-[2rem] p-8 text-white shadow-xl shadow-brand-500/20 relative overflow-hidden">
        <div class="relative z-10">
            <div class="flex items-center space-x-3 mb-2">
                <div class="p-2 bg-white/20 rounded-lg">
                    <i data-lucide="egg" class="w-5 h-5 text-white"></i>
                </div>
                <h3 class="text-xl font-black tracking-tight">Express Configuration (Eggs)</h3>
            </div>
            <p class="text-brand-100 text-sm mt-1">Select a pre-configured template for rapid deployment.</p>
            <div class="mt-6">
                <select id="template-selector" onchange="applyTemplate()" class="w-full md:w-72 bg-white/10 border border-white/20 rounded-xl py-2.5 px-4 text-white focus:bg-white focus:text-gray-900 outline-none transition-all font-bold text-sm">
                    <option value="" class="text-gray-900">-- Choose a Template --</option>
                    @foreach($eggs as $egg)
                        <option value="{{ $egg->id }}" class="text-gray-900">{{ $egg->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="absolute right-0 bottom-0 w-48 h-48 bg-white/10 rounded-full -mr-24 -mb-24 blur-2xl"></div>
    </div>

    <form action="{{ route('services.store') }}" method="POST" id="service-form" class="card bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border p-10 rounded-[2.5rem] shadow-xl space-y-8 transition-all duration-300">
        @csrf
        <input type="hidden" name="egg_id" id="egg_id_input">
        
        <!-- Egg-Specific Variables (Dynamic) -->
        <div id="egg-variables-container" class="hidden space-y-6 bg-gray-50 dark:bg-dark-bg/30 p-8 rounded-[2rem] border border-dashed border-gray-200 dark:border-dark-border">
            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-brand-500 flex items-center">
                <i data-lucide="settings-2" class="w-3 h-3 mr-2"></i>
                Egg-Specific Settings
            </h3>
            <div id="egg-variables-grid" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Dynamic fields will be injected here -->
            </div>
        </div>
        
        <!-- Deployment Type Selection -->
        <div class="space-y-4">
            <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Deployment Type</label>
            <div class="grid grid-cols-2 gap-4">
                <input type="radio" name="type" id="type-input-process" value="process" class="hidden" checked>
                <input type="radio" name="type" id="type-input-docker" value="docker" class="hidden">

                <div onclick="selectType('process')" class="relative flex items-center justify-center p-6 bg-gray-50 dark:bg-dark-bg border-2 border-brand-500 rounded-2xl cursor-pointer transition-all hover:bg-gray-100 dark:hover:bg-dark-bg/50" id="card-process">
                    <div class="flex flex-col items-center space-y-2 pointer-events-none">
                        <i data-lucide="cpu" class="w-6 h-6 text-brand-500" id="icon-process"></i>
                        <span class="text-sm font-black text-brand-600 dark:text-brand-400" id="text-process">Host Process</span>
                    </div>
                </div>

                <div onclick="selectType('docker')" class="relative flex items-center justify-center p-6 bg-gray-50 dark:bg-dark-bg border-2 border-transparent rounded-2xl cursor-pointer transition-all hover:bg-gray-100 dark:hover:bg-dark-bg/50" id="card-docker">
                    <div class="flex flex-col items-center space-y-2 pointer-events-none">
                        <i data-lucide="container" class="w-6 h-6 text-gray-400" id="icon-docker"></i>
                        <span class="text-sm font-black text-gray-500 dark:text-dark-text-muted" id="text-docker">Docker Container</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Service Name</label>
                <div class="relative">
                    <i data-lucide="type" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="name" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required placeholder="Production Web API">
                </div>
            </div>
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Group Tags</label>
                <div class="relative">
                    <i data-lucide="tag" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="tags" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" placeholder="Web, Docker, Games">
                </div>
            </div>
        </div>

        <!-- Process Specific Fields -->
        <div id="process-fields" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Working Directory</label>
                    <div class="relative">
                        <i data-lucide="folder" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="working_dir" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" placeholder="/home/malo/my-app">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Auto-Installer Script</label>
                    <div class="relative">
                        <i data-lucide="zap" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <select name="installer_script" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium appearance-none">
                            <option value="">None (Empty Directory)</option>
                            <option value="nodejs">Run 'npm install'</option>
                            <option value="composer">Run 'composer install'</option>
                            <option value="minecraft">Download Minecraft 1.20.1 & Accept EULA</option>
                            <option value="python">Install 'requirements.txt' via pip</option>
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Execution Command</label>
                <div class="relative">
                    <i data-lucide="play-circle" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="start_command" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm" placeholder="npm start">
                </div>
            </div>
        </div>

        <!-- Docker Specific Fields -->
        <div id="docker-fields" class="space-y-8 hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Docker Image</label>
                    <div class="relative">
                        <i data-lucide="container" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="docker_image" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" placeholder="nginx:latest">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Container Data Path (Inside)</label>
                    <div class="relative">
                        <i data-lucide="folder-symlink" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="docker_main_mount" value="/app" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" placeholder="/app">
                    </div>
                    <p class="text-[10px] text-gray-400 ml-1">Your global Docker path will be automatically linked to this folder.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Network</label>
                    <div class="relative">
                        <i data-lucide="network" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="docker_network" value="bridge" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Ports (e.g., 80:80)</label>
                    <div class="relative">
                        <i data-lucide="plug" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="docker_ports" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" placeholder="80:80, 443:443">
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Override Command (Optional)</label>
                <div class="relative">
                    <i data-lucide="terminal" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="start_command" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm" placeholder="sh startup.sh">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Stop Command (Optional)</label>
                <div class="relative">
                    <i data-lucide="square" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="stop_command" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm" placeholder="pkill -f 'my-app'">
                </div>
            </div>
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Alert Webhook (Discord)</label>
                <div class="relative">
                    <i data-lucide="bell" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="url" name="webhook_url" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 pl-11 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white text-sm" placeholder="https://discord.com/api/webhooks/...">
                </div>
            </div>
        </div>

        <div class="bg-gray-50 dark:bg-[#1c2128] border border-gray-200 dark:border-dark-border p-6 rounded-3xl">
            <label class="flex items-center space-x-4 cursor-pointer group">
                <div class="relative flex items-center">
                    <input type="checkbox" name="auto_restart" id="auto_restart" value="1" class="peer sr-only" checked>
                    <div class="w-12 h-6 bg-gray-300 dark:bg-dark-border rounded-full peer-checked:bg-brand-500 transition-colors"></div>
                    <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-6"></div>
                </div>
                <div>
                    <span class="block text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight">Auto-Restart Guard</span>
                    <span class="block text-xs text-gray-500 dark:text-gray-400">Automatically restart the instance if it terminates unexpectedly.</span>
                </div>
            </label>
        </div>

        <div class="pt-6">
            <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-4 rounded-2xl transition-all shadow-xl shadow-brand-500/25 active:scale-95 flex items-center justify-center space-x-2">
                <i data-lucide="rocket" class="w-5 h-5"></i>
                <span>INITIALIZE & DEPLOY SERVICE</span>
            </button>
        </div>
    </form>
</div>

<script>
    function selectType(type) {
        document.getElementById('type-input-' + type).checked = true;
        
        const cardProc = document.getElementById('card-process');
        const cardDock = document.getElementById('card-docker');
        const iconProc = document.getElementById('icon-process');
        const iconDock = document.getElementById('icon-docker');
        const textProc = document.getElementById('text-process');
        const textDock = document.getElementById('text-docker');
        const fieldsProc = document.getElementById('process-fields');
        const fieldsDock = document.getElementById('docker-fields');

        if (type === 'process') {
            fieldsProc.classList.remove('hidden');
            fieldsDock.classList.add('hidden');
            cardProc.classList.add('border-brand-500');
            cardProc.classList.remove('border-transparent');
            iconProc.classList.add('text-brand-500');
            iconProc.classList.remove('text-gray-400');
            textProc.className = 'text-sm font-black text-brand-600 dark:text-brand-400';
            cardDock.classList.remove('border-brand-500');
            cardDock.classList.add('border-transparent');
            iconDock.classList.remove('text-brand-500');
            iconDock.classList.add('text-gray-400');
            textDock.className = 'text-sm font-black text-gray-500 dark:text-dark-text-muted';
        } else {
            fieldsProc.classList.add('hidden');
            fieldsDock.classList.remove('hidden');
            cardDock.classList.add('border-brand-500');
            cardDock.classList.remove('border-transparent');
            iconDock.classList.add('text-brand-500');
            iconDock.classList.remove('text-gray-400');
            textDock.className = 'text-sm font-black text-brand-600 dark:text-brand-400';
            cardProc.classList.remove('border-brand-500');
            cardProc.classList.add('border-transparent');
            iconProc.classList.remove('text-brand-500');
            iconProc.classList.add('text-gray-400');
            textProc.className = 'text-sm font-black text-gray-500 dark:text-dark-text-muted';
        }
    }

    const eggs = @json($eggs->keyBy('id'));

    function applyTemplate() {
        const selector = document.getElementById('template-selector');
        const selectedId = selector.value;
        const variablesContainer = document.getElementById('egg-variables-container');
        const variablesGrid = document.getElementById('egg-variables-grid');
        
        variablesGrid.innerHTML = '';
        variablesContainer.classList.add('hidden');

        if (eggs[selectedId]) {
            const egg = eggs[selectedId];
            document.getElementById('egg_id_input').value = egg.id;
            selectType(egg.type);
            document.querySelector('input[name="name"]').value = egg.name;
            document.querySelector('input[name="tags"]').value = egg.tags || '';

            // Handle Dynamic Variables
            if (egg.variables && egg.variables.length > 0) {
                variablesContainer.classList.remove('hidden');
                egg.variables.forEach(variable => {
                    const div = document.createElement('div');
                    div.className = 'space-y-2';
                    div.innerHTML = `
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">${variable.name}</label>
                        <div class="relative">
                            <input type="text" name="env_vars[${variable.key}]" value="${variable.default || ''}" 
                                class="w-full bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium text-sm"
                                placeholder="${variable.description || ''}">
                        </div>
                        <p class="text-[9px] text-gray-400 ml-1">${variable.description || ''}</p>
                    `;
                    variablesGrid.appendChild(div);
                });
            }

            if (egg.type === 'docker') {
                document.querySelector('input[name="docker_image"]').value = egg.docker_image || '';
                document.querySelector('input[name="docker_main_mount"]').value = egg.docker_main_mount || '/app';
                document.querySelector('input[name="docker_ports"]').value = egg.docker_ports || '';
                document.querySelector('input[name="docker_network"]').value = egg.docker_network || 'bridge';
                document.querySelector('input[name="start_command"]').value = egg.start_command || '';
            } else {
                document.querySelector('input[name="start_command"]').value = egg.start_command || '';
                document.querySelector('input[name="stop_command"]').value = egg.stop_command || '';
                document.querySelector('input[name="working_dir"]').value = '/home/malo/my-app';
            }
        }
    }

    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
