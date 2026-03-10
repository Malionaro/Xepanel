@extends('layouts.app')

@section('header_title', 'Egg-Vorlagen: Erstellen')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.7/ace.js"></script>

<div class="max-w-5xl mx-auto space-y-10 pb-20">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-8">
        <div>
            <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white uppercase italic tracking-[0.05em]">Provision Egg</h2>
            <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg font-medium">Initialize a new deployment template protocol for infrastructure nodes.</p>
        </div>
        <a href="{{ route('eggs.index') }}" class="flex items-center space-x-3 glass dark:bg-dark-card border-slate-200 dark:border-dark-border text-slate-600 dark:text-slate-300 px-8 py-4 rounded-[2rem] text-xs font-black uppercase tracking-[0.2em] hover:bg-slate-50 dark:hover:bg-slate-800 transition-all hover:-translate-x-1 shrink-0 shadow-sm">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
            <span>Cancel</span>
        </a>
    </div>

    <form id="egg-form" action="{{ route('eggs.store') }}" method="POST" class="space-y-10">
        @csrf
        
        <!-- Header & Core Info -->
        <div class="glass dark:bg-dark-card border border-slate-200 dark:border-white/5 p-10 md:p-12 rounded-[3.5rem] shadow-2xl relative overflow-hidden group">
            <div class="absolute -right-24 -top-24 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl group-hover:bg-brand-500/10 transition-colors duration-700"></div>
            
            <div class="flex flex-col lg:flex-row gap-12 relative z-10">
                <div class="flex-1 space-y-8">
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Protocol Identity</label>
                        <div class="flex gap-6">
                            <div class="relative w-20 h-20 shrink-0">
                                <input type="text" name="icon" id="icon-input" value="box" class="hidden">
                                <button type="button" onclick="openIconPicker()" class="w-full h-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-[1.5rem] flex items-center justify-center text-brand-500 hover:border-brand-500 transition-all shadow-inner group/icon">
                                    <i id="current-icon" data-lucide="box" class="w-10 h-10 group-hover/icon:scale-110 transition-transform"></i>
                                </button>
                            </div>
                            <input type="text" name="name" class="flex-1 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-[1.5rem] py-4 px-8 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-black text-2xl shadow-sm" required placeholder="Name of the service type...">
                        </div>
                    </div>
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Operational Description</label>
                        <textarea name="description" rows="3" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-[1.5rem] py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white text-sm font-medium leading-relaxed shadow-sm" placeholder="Provide a brief explanation of what this egg deploys..."></textarea>
                    </div>
                </div>
                <div class="w-full lg:w-80 space-y-6">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Execution Architecture</label>
                    <div class="space-y-4">
                        <label class="relative flex items-center p-5 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-[1.5rem] cursor-pointer hover:border-brand-500/50 transition-all group/radio shadow-sm">
                            <input type="radio" name="type" value="process" class="sr-only peer" checked onchange="toggleType('process')">
                            <div class="w-12 h-12 rounded-2xl bg-white dark:bg-slate-900 flex items-center justify-center text-slate-400 peer-checked:text-brand-500 peer-checked:shadow-lg transition-all mr-5 border border-transparent peer-checked:border-brand-500/20">
                                <i data-lucide="cpu" class="w-6 h-6"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="block text-sm font-black text-slate-400 peer-checked:text-slate-900 dark:peer-checked:text-white transition-all uppercase tracking-tight">Host Process</span>
                                <span class="block text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-0.5">Native Systemd</span>
                            </div>
                            <div class="w-2.5 h-2.5 rounded-full bg-brand-500 opacity-0 peer-checked:opacity-100 transition-all shadow-[0_0_10px_rgba(139,92,246,0.5)]"></div>
                        </label>
                        <label class="relative flex items-center p-5 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-[1.5rem] cursor-pointer hover:border-brand-500/50 transition-all group/radio shadow-sm">
                            <input type="radio" name="type" value="docker" class="sr-only peer" onchange="toggleType('docker')">
                            <div class="w-12 h-12 rounded-2xl bg-white dark:bg-slate-900 flex items-center justify-center text-slate-400 peer-checked:text-brand-500 peer-checked:shadow-lg transition-all mr-5 border border-transparent peer-checked:border-brand-500/20">
                                <i data-lucide="container" class="w-6 h-6"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="block text-sm font-black text-slate-400 peer-checked:text-slate-900 dark:peer-checked:text-white transition-all uppercase tracking-tight">Docker Node</span>
                                <span class="block text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-0.5">Isolated Container</span>
                            </div>
                            <div class="w-2.5 h-2.5 rounded-full bg-brand-500 opacity-0 peer-checked:opacity-100 transition-all shadow-[0_0_10px_rgba(139,92,246,0.5)]"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Engine Specific Configuration -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <!-- Dynamic Fields Card -->
            <div class="glass dark:bg-dark-card border border-slate-200 dark:border-white/5 p-10 rounded-[3.5rem] shadow-xl">
                <h3 class="text-xs font-black uppercase tracking-[0.3em] text-slate-400 mb-10 flex items-center">
                    <i data-lucide="settings-2" class="w-4 h-4 mr-3 text-brand-500"></i>
                    Engine Configuration
                </h3>

                <!-- Docker Config -->
                <div id="docker-config" class="hidden space-y-8">
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Base Artifact Image</label>
                        <input type="text" name="docker_image" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm shadow-sm" placeholder="e.g. node:20-alpine">
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Communication Ports</label>
                            <input type="text" name="docker_ports" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm shadow-sm" placeholder="8080:8080">
                        </div>
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Network Uplink</label>
                            <input type="text" name="docker_network" value="bridge" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm shadow-sm">
                        </div>
                    </div>
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Runtime Override (Optional)</label>
                        <input type="text" name="docker_start_override" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm shadow-sm" placeholder="node index.js">
                    </div>
                </div>

                <!-- Process Config -->
                <div id="process-config" class="space-y-8">
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Primary Boot Protocol</label>
                        <input type="text" name="start_command" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm shadow-sm" placeholder="python3 main.py">
                    </div>
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Termination Signal</label>
                        <input type="text" name="stop_command" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm shadow-sm" placeholder="kill -9 $PID">
                    </div>
                </div>
            </div>

            <!-- Resource Defaults Card -->
            <div class="glass dark:bg-dark-card border border-slate-200 dark:border-white/5 p-10 rounded-[3.5rem] shadow-xl">
                <h3 class="text-xs font-black uppercase tracking-[0.3em] text-slate-400 mb-10 flex items-center">
                    <i data-lucide="zap" class="w-4 h-4 mr-3 text-yellow-500"></i>
                    Default Quota Allocation
                </h3>

                <div class="space-y-8">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">RAM Capacity (MB)</label>
                            <input type="number" name="default_ram_mb" value="1024" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-black shadow-sm" required>
                        </div>
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">CPU Load (%)</label>
                            <input type="number" name="default_cpu_percent" value="100" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-black shadow-sm" required>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Persistent Storage (MB)</label>
                        <input type="number" name="default_disk_mb" value="5120" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-black shadow-sm" required>
                    </div>
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Operational Tags</label>
                        <input type="text" name="tags" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm" placeholder="Minecraft, Proxy, App">
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Variables Manager -->
        <div class="glass dark:bg-dark-card border border-slate-200 dark:border-white/5 p-10 md:p-12 rounded-[3.5rem] shadow-2xl">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                <h3 class="text-xs font-black uppercase tracking-[0.3em] text-slate-400 flex items-center">
                    <i data-lucide="variable" class="w-4 h-4 mr-3 text-purple-500"></i>
                    Configuration Matrix
                </h3>
                <button type="button" onclick="addVariable()" class="flex items-center space-x-3 bg-purple-500/10 hover:bg-purple-500 text-purple-500 hover:text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all border border-purple-500/20 shadow-sm active:scale-95">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Add Variable</span>
                </button>
            </div>

            <div id="variables-container" class="grid grid-cols-1 gap-6">
                <!-- Variables will be injected here -->
            </div>
            
            <div id="no-vars-notice" class="py-16 text-center border-2 border-dashed border-slate-100 dark:border-white/5 rounded-[2.5rem]">
                <div class="w-16 h-16 bg-slate-50 dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 dark:text-slate-600">
                    <i data-lucide="layers" class="w-8 h-8"></i>
                </div>
                <p class="text-slate-400 text-xs font-black uppercase tracking-widest">No custom variables defined</p>
            </div>
        </div>

        <!-- Installation Script -->
        <div class="glass dark:bg-dark-card border border-slate-200 dark:border-white/5 overflow-hidden rounded-[3.5rem] shadow-2xl flex flex-col group/editor">
            <div class="px-10 py-6 border-b border-slate-100 dark:border-white/5 flex justify-between items-center bg-slate-50/50 dark:bg-white/5">
                <h3 class="text-xs font-black uppercase tracking-[0.3em] text-slate-400 flex items-center">
                    <i data-lucide="terminal" class="w-4 h-4 mr-3 text-red-500"></i>
                    Initialization Protocol (Bash)
                </h3>
                <span class="text-[9px] font-black text-slate-400 bg-white dark:bg-slate-900 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 shadow-sm uppercase tracking-widest italic">Immutable Setup Cycle</span>
            </div>
            <div id="install-script-editor" class="h-80 bg-[#020617] text-sm"></div>
            <textarea name="install_script" id="install_script_hidden" class="hidden"></textarea>
        </div>

        <div class="pt-6">
            <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-6 rounded-[2.5rem] transition-all shadow-2xl shadow-brand-500/25 active:scale-[0.98] flex items-center justify-center space-x-4 group/submit">
                <i data-lucide="save" class="w-7 h-7 transition-transform group-hover/submit:scale-125"></i>
                <span class="text-xl uppercase tracking-[0.2em] italic">Commit New Egg Protocol</span>
            </button>
        </div>
    </form>
</div>

<!-- Icon Picker Modal -->
<div id="icon-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xl animate-in fade-in duration-300">
    <div class="glass dark:bg-dark-card w-full max-w-2xl rounded-[3.5rem] p-10 border border-white/10 shadow-2xl space-y-8">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-500 border border-brand-500/20">
                <i data-lucide="palette" class="w-6 h-6"></i>
            </div>
            <h3 class="text-2xl font-black text-white tracking-tight uppercase italic">Protocol Visuals</h3>
        </div>
        <div class="grid grid-cols-6 gap-4 max-h-96 overflow-y-auto p-4 custom-scrollbar">
            @php $icons = ['box', 'terminal', 'database', 'globe', 'server', 'shield', 'zap', 'cpu', 'container', 'hard-drive', 'activity', 'code', 'layout', 'layers', 'git-branch', 'disc', 'music', 'gamepad-2', 'bot', 'cloud', 'lock', 'key', 'mail', 'anchor', 'package', 'cpu', 'flask-conical', 'binary', 'atom']; @endphp
            @foreach($icons as $icon)
                <button type="button" onclick="selectIcon('{{ $icon }}')" class="aspect-square rounded-2xl bg-white/5 flex items-center justify-center text-slate-400 hover:text-brand-500 hover:bg-brand-500/10 border border-transparent hover:border-brand-500/30 transition-all active:scale-90 group/btn">
                    <i data-lucide="{{ $icon }}" class="w-7 h-7 group-hover/btn:scale-110"></i>
                </button>
            @endforeach
        </div>
        <button type="button" onclick="closeIconPicker()" class="w-full py-4 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl font-black text-xs uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400 hover:text-white transition-all shadow-sm">Abort Selection</button>
    </div>
</div>

<script>
    // Ace Editor Setup
    const editor = ace.edit("install-script-editor");
    editor.setTheme("ace/theme/tomorrow_night");
    editor.session.setMode("ace/mode/sh");
    editor.setShowPrintMargin(false);
    editor.setOptions({ 
        fontSize: "14px",
        fontFamily: "JetBrains Mono, Fira Code, monospace"
    });

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

    let varIndex = 0;
    function addVariable() {
        document.getElementById('no-vars-notice').classList.add('hidden');
        const container = document.getElementById('variables-container');
        const html = `
            <div class="variable-row flex gap-6 bg-slate-50 dark:bg-white/5 p-6 rounded-[2rem] border border-slate-100 dark:border-white/10 group/var animate-in slide-in-from-left-4 duration-500 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 flex-1">
                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest ml-1">Env Key</label>
                        <input type="text" name="variables[${varIndex}][key]" placeholder="e.g. SERVER_PORT" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 rounded-xl py-2.5 px-4 text-xs font-mono font-bold dark:text-brand-400 outline-none focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest ml-1">Alias Name</label>
                        <input type="text" name="variables[${varIndex}][name]" placeholder="Internal Port" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 rounded-xl py-2.5 px-4 text-xs font-bold dark:text-white outline-none focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest ml-1">Default Sequence</label>
                        <input type="text" name="variables[${varIndex}][default]" placeholder="8080" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 rounded-xl py-2.5 px-4 text-xs font-bold dark:text-white outline-none focus:ring-2 focus:ring-brand-500/20">
                    </div>
                </div>
                <button type="button" onclick="this.closest('.variable-row').remove()" class="self-center w-10 h-10 rounded-xl bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center border border-red-500/20 active:scale-90">
                    <i data-lucide="trash-2" class="w-5 h-5"></i>
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
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(139, 92, 246, 0.2); border-radius: 10px; }
    .ace_editor { border-radius: 0 0 3.5rem 3.5rem; font-family: 'JetBrains Mono', 'Fira Code', monospace !important; }
</style>
@endsection
