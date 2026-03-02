@extends('layouts.app')

@section('header_title', 'Create Egg')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex items-center space-x-2 text-gray-500 dark:text-gray-400 mb-2">
        <a href="{{ route('eggs.index') }}" class="hover:text-brand-500 transition-colors">Egg Management</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-900 dark:text-white font-bold">New Template</span>
    </div>

    <form action="{{ route('eggs.store') }}" method="POST" class="card bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border p-10 rounded-[2.5rem] shadow-xl space-y-8 transition-all duration-300">
        @csrf
        
        <div class="space-y-4">
            <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Template Type</label>
            <div class="grid grid-cols-2 gap-4">
                <input type="radio" name="type" id="type-process" value="process" class="hidden" checked onchange="toggleType('process')">
                <label for="type-process" class="relative flex items-center justify-center p-4 bg-gray-50 dark:bg-dark-bg border-2 border-brand-500 rounded-2xl cursor-pointer transition-all" id="label-process">
                    <div class="flex flex-col items-center space-y-1">
                        <i data-lucide="cpu" class="w-5 h-5 text-brand-500"></i>
                        <span class="text-sm font-black text-brand-600">Host Process</span>
                    </div>
                </label>

                <input type="radio" name="type" id="type-docker" value="docker" class="hidden" onchange="toggleType('docker')">
                <label for="type-docker" class="relative flex items-center justify-center p-4 bg-gray-50 dark:bg-dark-bg border-2 border-transparent rounded-2xl cursor-pointer transition-all" id="label-docker">
                    <div class="flex flex-col items-center space-y-1">
                        <i data-lucide="container" class="w-5 h-5 text-gray-400"></i>
                        <span class="text-sm font-black text-gray-500">Docker Container</span>
                    </div>
                </label>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Egg Name</label>
                <input type="text" name="name" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required placeholder="e.g. Minecraft Java">
            </div>
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Tags (Comma separated)</label>
                <input type="text" name="tags" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" placeholder="Docker, Game, Web">
            </div>
        </div>

        <div class="space-y-2">
            <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Description</label>
            <textarea name="description" rows="2" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" placeholder="What is this template for?"></textarea>
        </div>

        <!-- Docker Fields -->
        <div id="docker-fields" class="hidden space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Docker Image</label>
                    <input type="text" name="docker_image" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm" placeholder="nginx:latest">
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Container Data Path (Inside)</label>
                    <input type="text" name="docker_main_mount" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm" placeholder="/app">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Default Ports</label>
                    <input type="text" name="docker_ports" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm" placeholder="80:80, 443:443">
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Default Network</label>
                    <input type="text" name="docker_network" value="bridge" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm">
                </div>
            </div>
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Override Command (Optional)</label>
                <input type="text" name="start_command_docker" id="start_command_docker" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm" placeholder="e.g. sh startup.sh">
            </div>
        </div>

        <!-- Process Fields -->
        <div id="process-fields" class="space-y-8">
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Default Start Command</label>
                <input type="text" name="start_command" id="start_command_process" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm" placeholder="npm start">
            </div>
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Default Stop Command (Optional)</label>
                <input type="text" name="stop_command" class="w-full bg-gray-50 dark:bg-dark-bg border border-gray-200 dark:border-dark-border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-mono text-sm" placeholder="pkill -f 'node'">
            </div>
        </div>

        <div class="pt-6">
            <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-4 rounded-2xl transition-all shadow-xl shadow-brand-500/25 active:scale-95">
                CREATE TEMPLATE
            </button>
        </div>
    </form>
</div>

<script>
    function toggleType(type) {
        const procFields = document.getElementById('process-fields');
        const dockFields = document.getElementById('docker-fields');
        const labelProc = document.getElementById('label-process');
        const labelDock = document.getElementById('label-docker');
        const startProc = document.getElementById('start_command_process');
        const startDock = document.getElementById('start_command_docker');

        if (type === 'process') {
            procFields.classList.remove('hidden');
            dockFields.classList.add('hidden');
            labelProc.classList.add('border-brand-500');
            labelProc.classList.remove('border-transparent');
            labelDock.classList.remove('border-brand-500');
            labelDock.classList.add('border-transparent');
            
            // Sync start command name
            startProc.name = 'start_command';
            startDock.name = 'start_command_unused';
        } else {
            procFields.classList.add('hidden');
            dockFields.classList.remove('hidden');
            labelDock.classList.add('border-brand-500');
            labelDock.classList.remove('border-transparent');
            labelProc.classList.remove('border-brand-500');
            labelProc.classList.add('border-transparent');

            // Sync start command name
            startDock.name = 'start_command';
            startProc.name = 'start_command_unused';
        }
    }
    // Initialize
    toggleType('process');
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
