@extends('layouts.app')

@section('header_title', 'Edit Egg Template')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.7/ace.js"></script>

<div class="max-w-5xl mx-auto space-y-8 pb-20">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2 text-gray-500 dark:text-gray-400">
            <a href="{{ route('eggs.index') }}" class="hover:text-brand-500 transition-colors">Egg Management</a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span class="text-gray-900 dark:text-white font-black">Edit: {{ $egg->name }}</span>
        </div>
    </div>

    <form id="egg-form" action="{{ route('eggs.update', $egg->id) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')
        
        <!-- Header & Core Info -->
        <div class="card bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border p-8 rounded-[2.5rem] shadow-sm space-y-8">
            <div class="flex flex-col md:flex-row gap-8">
                <div class="flex-1 space-y-6">
                    <div class="space-y-2">
                        <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Template Identity</label>
                        <div class="flex gap-4">
                            <div class="relative w-16 h-16 shrink-0">
                                <input type="text" name="icon" id="icon-input" value="{{ $egg->icon ?? 'box' }}" class="hidden">
                                <button type="button" onclick="openIconPicker()" class="w-full h-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl flex items-center justify-center text-brand-500 hover:border-brand-500 transition-all shadow-inner group">
                                    <i id="current-icon" data-lucide="{{ $egg->icon ?? 'box' }}" class="w-8 h-8 group-hover:scale-110 transition-transform"></i>
                                </button>
                            </div>
                            <input type="text" name="name" value="{{ old('name', $egg->name) }}" class="flex-1 bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-black text-xl" required>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Description</label>
                        <textarea name="description" rows="2" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white text-sm" placeholder="Provide a brief explanation...">{{ old('description', $egg->description) }}</textarea>
                    </div>
                </div>
                <div class="w-full md:w-72 space-y-4">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Execution Engine</label>
                    <div class="space-y-3">
                        <label class="relative flex items-center p-4 bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl cursor-pointer hover:border-brand-500/50 transition-all group">
                            <input type="radio" name="type" value="process" class="sr-only peer" {{ $egg->type === 'process' ? 'checked' : '' }} onchange="toggleType('process')">
                            <div class="w-10 h-10 rounded-xl bg-white dark:bg-dark-card flex items-center justify-center text-gray-400 peer-checked:text-brand-500 peer-checked:shadow-sm transition-all mr-4">
                                <i data-lucide="cpu" class="w-5 h-5"></i>
                            </div>
                            <div class="flex-1">
                                <span class="block text-sm font-black text-gray-400 peer-checked:text-brand-500 transition-all uppercase tracking-tight">Host Process</span>
                            </div>
                            <div class="w-2 h-2 rounded-full bg-brand-500 opacity-0 peer-checked:opacity-100 transition-all"></div>
                        </label>
                        <label class="relative flex items-center p-4 bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl cursor-pointer hover:border-brand-500/50 transition-all group">
                            <input type="radio" name="type" value="docker" class="sr-only peer" {{ $egg->type === 'docker' ? 'checked' : '' }} onchange="toggleType('docker')">
                            <div class="w-10 h-10 rounded-xl bg-white dark:bg-dark-card flex items-center justify-center text-gray-400 peer-checked:text-brand-500 peer-checked:shadow-sm transition-all mr-4">
                                <i data-lucide="container" class="w-5 h-5"></i>
                            </div>
                            <div class="flex-1">
                                <span class="block text-sm font-black text-gray-400 peer-checked:text-brand-500 transition-all uppercase tracking-tight">Docker Node</span>
                            </div>
                            <div class="w-2 h-2 rounded-full bg-brand-500 opacity-0 peer-checked:opacity-100 transition-all"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Engine Specific Configuration -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Dynamic Fields Card -->
            <div class="card bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border p-8 rounded-[2.5rem] shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-400 mb-8 flex items-center">
                    <i data-lucide="settings-2" class="w-3 h-3 mr-2 text-brand-500"></i>
                    Engine Parameters
                </h3>

                <!-- Docker Config -->
                <div id="docker-config" class="{{ $egg->type === 'docker' ? '' : 'hidden' }} space-y-6">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Base Docker Image</label>
                        <input type="text" name="docker_image" value="{{ old('docker_image', $egg->docker_image) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Default Ports</label>
                            <input type="text" name="docker_ports" value="{{ old('docker_ports', $egg->docker_ports) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Network</label>
                            <input type="text" name="docker_network" value="{{ old('docker_network', $egg->docker_network ?? 'bridge') }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Container Data Path (Inside)</label>
                        <input type="text" name="docker_main_mount" value="{{ old('docker_main_mount', $egg->docker_main_mount ?? '/app') }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm">
                    </div>
                </div>

                <!-- Process Config -->
                <div id="process-config" class="{{ $egg->type === 'process' ? '' : 'hidden' }} space-y-6">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Default Start Command</label>
                        <input type="text" name="start_command" value="{{ old('start_command', $egg->start_command) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Default Stop Command</label>
                        <input type="text" name="stop_command" value="{{ old('stop_command', $egg->stop_command) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm">
                    </div>
                </div>
            </div>

            <!-- Resource Defaults Card -->
            <div class="card bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border p-8 rounded-[2.5rem] shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-400 mb-8 flex items-center">
                    <i data-lucide="zap" class="w-3 h-3 mr-2 text-yellow-500"></i>
                    Default Quotas
                </h3>

                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">RAM (MB)</label>
                            <input type="number" name="default_ram_mb" value="{{ old('default_ram_mb', $egg->default_ram_mb ?? 1024) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold" required>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">CPU (%)</label>
                            <input type="number" name="default_cpu_percent" value="{{ old('default_cpu_percent', $egg->default_cpu_percent ?? 100) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold" required>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Disk Space (MB)</label>
                        <input type="number" name="default_disk_mb" value="{{ old('default_disk_mb', $egg->default_disk_mb ?? 5120) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold" required>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Deployment Tags</label>
                        <input type="text" name="tags" value="{{ old('tags', $egg->tags) }}" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium">
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Variables Manager -->
        <div class="card bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border p-8 rounded-[2.5rem] shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-400 flex items-center">
                    <i data-lucide="variable" class="w-3 h-3 mr-2 text-purple-500"></i>
                    Configuration Variables
                </h3>
                <button type="button" onclick="addVariable()" class="flex items-center space-x-2 bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                    <i data-lucide="plus" class="w-3 h-3"></i>
                    <span>Add Variable</span>
                </button>
            </div>

            <div id="variables-container" class="space-y-4">
                @foreach($egg->variables ?? [] as $index => $var)
                    <div class="variable-row flex gap-4 bg-gray-50 dark:bg-dark-bg p-4 rounded-2xl border border-gray-100 dark:border-dark-border group">
                        <div class="grid grid-cols-3 gap-4 flex-1">
                            <input type="text" name="variables[{{ $index }}][key]" value="{{ $var['key'] }}" placeholder="VAR_KEY" class="w-full bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-lg py-2 px-3 text-xs font-mono dark:text-white">
                            <input type="text" name="variables[{{ $index }}][name]" value="{{ $var['name'] }}" placeholder="Display Name" class="w-full bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-lg py-2 px-3 text-xs font-bold dark:text-white">
                            <input type="text" name="variables[{{ $index }}][default]" value="{{ $var['default'] }}" placeholder="Default Value" class="w-full bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-lg py-2 px-3 text-xs dark:text-white">
                        </div>
                        <button type="button" onclick="this.closest('.variable-row').remove()" class="text-gray-400 hover:text-red-500 transition-colors px-2">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endforeach
            </div>
            
            <div id="no-vars-notice" class="{{ count($egg->variables ?? []) > 0 ? 'hidden' : '' }} py-12 text-center border-2 border-dashed border-gray-100 dark:border-dark-border rounded-3xl">
                <p class="text-gray-400 text-xs font-bold italic">No custom variables defined for this template.</p>
            </div>
        </div>

        <!-- Installation Script (Ace Editor) -->
        <div class="card bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border overflow-hidden rounded-[2.5rem] shadow-sm flex flex-col">
            <div class="px-8 py-6 border-b border-gray-100 dark:border-dark-border flex justify-between items-center bg-gray-50 dark:bg-dark-hover">
                <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-400 flex items-center">
                    <i data-lucide="terminal" class="w-3 h-3 mr-2 text-red-500"></i>
                    Installation Script (Bash)
                </h3>
                <span class="text-[9px] font-black text-gray-400 bg-white dark:bg-dark-bg px-2 py-1 rounded border border-gray-200 dark:border-dark-border">RUNS ONCE ON CREATE</span>
            </div>
            <div id="install-script-editor" class="h-64 bg-[#0d1117] text-sm">{{ $egg->install_script ?? '' }}</div>
            <textarea name="install_script" id="install_script_hidden" class="hidden"></textarea>
        </div>

        <div class="pt-4 flex gap-4">
            <button type="submit" class="flex-1 bg-brand-500 hover:bg-brand-600 text-white font-black py-5 rounded-[2.5rem] transition-all shadow-xl shadow-brand-500/25 active:scale-[0.98] flex items-center justify-center space-x-3">
                <i data-lucide="save" class="w-6 h-6"></i>
                <span class="text-lg">UPDATE EGG TEMPLATE</span>
            </button>
            <a href="{{ route('eggs.index') }}" class="px-10 bg-gray-100 dark:bg-dark-card border border-gray-200 dark:border-dark-border text-gray-500 dark:text-gray-400 font-black py-5 rounded-[2.5rem] transition-all hover:bg-gray-200 dark:hover:bg-dark-hover flex items-center justify-center">
                CANCEL
            </a>
        </div>
    </form>
</div>

<!-- Icon Picker Modal -->
<div id="icon-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-dark-card w-full max-w-2xl rounded-[2.5rem] p-8 border border-gray-200 dark:border-dark-border shadow-2xl">
        <h3 class="text-xl font-black mb-6 dark:text-white">Choose Template Icon</h3>
        <div class="grid grid-cols-6 gap-4 max-h-96 overflow-y-auto p-2 custom-scrollbar">
            @php $icons = ['box', 'terminal', 'database', 'globe', 'server', 'shield', 'zap', 'cpu', 'container', 'hard-drive', 'activity', 'code', 'layout', 'layers', 'git-branch', 'disc', 'music', 'gamepad-2', 'bot', 'cloud', 'lock', 'key', 'mail', 'anchor']; @endphp
            @foreach($icons as $icon)
                <button type="button" onclick="selectIcon('{{ $icon }}')" class="aspect-square rounded-2xl bg-gray-50 dark:bg-dark-bg flex items-center justify-center text-gray-400 hover:text-brand-500 hover:border-brand-500 border border-transparent transition-all">
                    <i data-lucide="{{ $icon }}" class="w-6 h-6"></i>
                </button>
            @endforeach
        </div>
        <button type="button" onclick="closeIconPicker()" class="mt-8 w-full py-3 bg-gray-100 dark:bg-dark-hover rounded-xl font-bold dark:text-white">Close</button>
    </div>
</div>

<script>
    // Ace Editor Setup
    const editor = ace.edit("install-script-editor");
    editor.setTheme("ace/theme/one_dark");
    editor.session.setMode("ace/mode/sh");
    editor.setShowPrintMargin(false);
    editor.setOptions({ fontSize: "13px" });

    // Sync editor to hidden textarea before submit
    document.getElementById('egg-form').onsubmit = function() {
        document.getElementById('install_script_hidden').value = editor.getValue();
    };

    function toggleType(type) {
        document.getElementById('docker-config').classList.toggle('hidden', type !== 'docker');
        document.getElementById('process-config').classList.toggle('hidden', type !== 'process');
    }

    function openIconPicker() {
        document.getElementById('icon-modal').classList.remove('hidden');
    }

    function closeIconPicker() {
        document.getElementById('icon-modal').classList.add('hidden');
    }

    function selectIcon(icon) {
        document.getElementById('icon-input').value = icon;
        document.getElementById('current-icon').setAttribute('data-lucide', icon);
        closeIconPicker();
        if(typeof lucide !== 'undefined') lucide.createIcons();
    }

    let varIndex = {{ count($egg->variables ?? []) }};
    function addVariable() {
        document.getElementById('no-vars-notice').classList.add('hidden');
        const container = document.getElementById('variables-container');
        const html = `
            <div class="variable-row flex gap-4 bg-gray-50 dark:bg-dark-bg p-4 rounded-2xl border border-gray-100 dark:border-dark-border group animate-in slide-in-from-left-2 duration-300">
                <div class="grid grid-cols-3 gap-4 flex-1">
                    <input type="text" name="variables[${varIndex}][key]" placeholder="VAR_KEY" class="w-full bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-lg py-2 px-3 text-xs font-mono dark:text-white">
                    <input type="text" name="variables[${varIndex}][name]" placeholder="Display Name" class="w-full bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-lg py-2 px-3 text-xs font-bold dark:text-white">
                    <input type="text" name="variables[${varIndex}][default]" placeholder="Default Value" class="w-full bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-lg py-2 px-3 text-xs dark:text-white">
                </div>
                <button type="button" onclick="this.closest('.variable-row').remove()" class="text-gray-400 hover:text-red-500 transition-colors px-2">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        varIndex++;
        if(typeof lucide !== 'undefined') lucide.createIcons();
    }

    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #30363d; border-radius: 10px; }
</style>
@endsection
