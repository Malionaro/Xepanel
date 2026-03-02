@extends('layouts.app')

@section('header_title', 'Import Service')

@section('content')
<div class="max-w-3xl mx-auto space-y-8">
    <div class="flex items-center space-x-2 text-gray-500 dark:text-gray-400 mb-2">
        <a href="{{ route('dashboard') }}" class="hover:text-brand-500 transition-colors">Dashboard</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-900 dark:text-white font-bold">Import Service</span>
    </div>

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Import Configuration</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Upload a JSON file to restore or migrate a service instance.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="p-3 bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border text-gray-500 hover:text-brand-500 rounded-2xl transition-all shadow-sm">
            <i data-lucide="x" class="w-5 h-5"></i>
        </a>
    </div>

    <form action="{{ route('services.do_import') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        
        <div class="card bg-white dark:bg-dark-card border-2 border-dashed border-gray-200 dark:border-dark-border p-12 rounded-[2.5rem] flex flex-col items-center justify-center text-center transition-all hover:border-brand-500 group relative">
            <input type="file" name="import_file" id="import_file" accept=".json,application/json" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" required onchange="updateFileName(this)">
            
            <div class="w-20 h-20 bg-brand-50 dark:bg-brand-500/10 rounded-3xl flex items-center justify-center text-brand-500 mb-6 group-hover:scale-110 transition-transform duration-500">
                <i data-lucide="upload-cloud" class="w-10 h-10"></i>
            </div>
            
            <div id="file-info" class="space-y-2">
                <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Select JSON File</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs mx-auto">Click here or drag and drop your exported service configuration file.</p>
            </div>

            <div id="file-selected" class="hidden space-y-2 animate-in fade-in zoom-in duration-300">
                <div class="flex items-center space-x-2 bg-green-50 dark:bg-green-900/20 text-green-600 px-4 py-2 rounded-xl border border-green-100 dark:border-green-900/30">
                    <i data-lucide="file-check" class="w-4 h-4"></i>
                    <span id="file-name" class="text-sm font-bold">config.json</span>
                </div>
                <button type="button" onclick="resetFile()" class="text-[10px] font-black uppercase text-gray-400 hover:text-red-500 tracking-widest transition-colors">Remove File</button>
            </div>
        </div>

        @if($errors->any())
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 text-red-600 dark:text-red-400 p-4 rounded-2xl flex items-center space-x-3">
                <i data-lucide="alert-octagon" class="w-5 h-5"></i>
                <span class="text-sm font-bold">{{ $errors->first() }}</span>
            </div>
        @endif

        <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-5 rounded-[2rem] transition-all shadow-xl shadow-brand-500/25 active:scale-95 flex items-center justify-center space-x-3">
            <i data-lucide="download" class="w-6 h-6"></i>
            <span>IMPORT SERVICE INSTANCE</span>
        </button>
    </form>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="p-6 bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/20 rounded-[2rem] flex items-start space-x-4">
            <i data-lucide="info" class="w-6 h-6 text-blue-600 shrink-0 mt-1"></i>
            <div>
                <h4 class="text-sm font-black text-blue-900 dark:text-blue-400 uppercase tracking-tight">Security Note</h4>
                <p class="text-xs text-blue-800 dark:text-blue-500 mt-1 leading-relaxed">
                    Imported services include all environment variables. Ensure you trust the source of the configuration file.
                </p>
            </div>
        </div>
        
        <div class="p-6 bg-amber-50 dark:bg-yellow-900/10 border border-amber-100 dark:border-yellow-900/20 rounded-[2rem] flex items-start space-x-4">
            <i data-lucide="folder" class="w-6 h-6 text-amber-600 shrink-0 mt-1"></i>
            <div>
                <h4 class="text-sm font-black text-amber-900 dark:text-yellow-500 uppercase tracking-tight">System Paths</h4>
                <p class="text-xs text-amber-800 dark:text-yellow-600 mt-1 leading-relaxed">
                    Host paths must exist on this server. If importing a Docker service, the main data path will be auto-linked.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    function updateFileName(input) {
        const fileInfo = document.getElementById('file-info');
        const fileSelected = document.getElementById('file-selected');
        const fileNameDisplay = document.getElementById('file-name');
        
        if (input.files && input.files[0]) {
            fileInfo.classList.add('hidden');
            fileSelected.classList.remove('hidden');
            fileNameDisplay.textContent = input.files[0].name;
        }
    }

    function resetFile() {
        const input = document.getElementById('import_file');
        const fileInfo = document.getElementById('file-info');
        const fileSelected = document.getElementById('file-selected');
        
        input.value = '';
        fileInfo.classList.remove('hidden');
        fileSelected.classList.add('hidden');
    }

    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
