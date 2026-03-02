@extends('layouts.app')

@section('header_title', 'Systemd Generator')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex items-center space-x-2 text-gray-500 dark:text-gray-400 mb-2">
        <a href="{{ route('dashboard') }}" class="hover:text-brand-500 transition-colors">Dashboard</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <a href="{{ route('services.show', $service->id) }}" class="hover:text-brand-500 transition-colors">{{ $service->name }}</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-900 dark:text-white font-bold">Auto-Start</span>
    </div>

    <div class="flex items-center justify-between">
        <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">OS Level Integration</h2>
        <a href="{{ route('services.show', $service->id) }}" class="flex items-center space-x-2 bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border text-gray-700 dark:text-gray-300 px-5 py-2.5 rounded-2xl text-sm font-bold transition-all hover:shadow-md">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back</span>
        </a>
    </div>

    <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/20 p-6 rounded-[2rem] flex items-start space-x-4">
        <div class="w-12 h-12 rounded-2xl bg-blue-500 text-white flex items-center justify-center shrink-0 shadow-lg shadow-blue-500/20">
            <i data-lucide="info" class="w-6 h-6"></i>
        </div>
        <div>
            <h3 class="font-black text-blue-900 dark:text-blue-400 uppercase tracking-tight text-sm">Systemd Integration</h3>
            <p class="text-sm text-blue-800 dark:text-blue-500 mt-1 leading-relaxed">
                Automate your instance using the native Linux init system. This ensures the service starts immediately upon server boot. 
                <strong class="dark:text-blue-300">Note:</strong> It is recommended to disable "Auto-Restart Guard" in the panel settings if you use Systemd to prevent conflicts.
            </p>
        </div>
    </div>

    <div class="space-y-8">
        <!-- Step 1 -->
        <div class="card bg-white dark:bg-dark-card rounded-[2rem] border border-gray-200 dark:border-dark-border p-8 shadow-sm">
            <div class="flex items-center space-x-3 mb-6">
                <span class="w-8 h-8 rounded-full bg-gray-900 dark:bg-white text-white dark:text-gray-900 flex items-center justify-center font-black text-xs">1</span>
                <h3 class="font-black text-xl text-gray-900 dark:text-white tracking-tight uppercase">Create Unit File</h3>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Execute this command as root to open the editor:</p>
            <div class="group relative">
                <div class="bg-gray-900 dark:bg-black p-4 rounded-2xl border border-gray-800 flex justify-between items-center transition-all group-hover:border-brand-500/50">
                    <code class="font-mono text-sm text-green-400 select-all">sudo nano /etc/systemd/system/{{ Str::slug($service->name) }}.service</code>
                    <i data-lucide="terminal" class="w-4 h-4 text-gray-700"></i>
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="card bg-white dark:bg-dark-card rounded-[2rem] border border-gray-200 dark:border-dark-border p-8 shadow-sm">
            <div class="flex items-center space-x-3 mb-6">
                <span class="w-8 h-8 rounded-full bg-gray-900 dark:bg-white text-white dark:text-gray-900 flex items-center justify-center font-black text-xs">2</span>
                <h3 class="font-black text-xl text-gray-900 dark:text-white tracking-tight uppercase">Configuration Content</h3>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Paste the following generated content into the file:</p>
            <div class="relative group">
                <textarea id="unit-content" class="w-full h-80 bg-gray-900 dark:bg-black text-gray-300 font-mono text-xs p-6 border border-gray-800 rounded-3xl outline-none resize-none transition-all group-hover:border-brand-500/50" readonly>{{ $unitFile }}</textarea>
                <button onclick="copyUnit()" class="absolute top-4 right-4 bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg active:scale-95 flex items-center space-x-2">
                    <i data-lucide="copy" class="w-3 h-3"></i>
                    <span>Copy Config</span>
                </button>
            </div>
        </div>

        <!-- Step 3 -->
        <div class="card bg-white dark:bg-dark-card rounded-[2rem] border border-gray-200 dark:border-dark-border p-8 shadow-sm">
            <div class="flex items-center space-x-3 mb-6">
                <span class="w-8 h-8 rounded-full bg-gray-900 dark:bg-white text-white dark:text-gray-900 flex items-center justify-center font-black text-xs">3</span>
                <h3 class="font-black text-xl text-gray-900 dark:text-white tracking-tight uppercase">Enable & Start</h3>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Refresh the systemd daemon and activate the service:</p>
            <div class="bg-gray-900 dark:bg-black p-6 rounded-3xl border border-gray-800 space-y-3">
                <div class="flex items-center space-x-3 text-xs font-mono">
                    <span class="text-gray-600">#</span>
                    <code class="text-blue-400 select-all">sudo systemctl daemon-reload</code>
                </div>
                <div class="flex items-center space-x-3 text-xs font-mono">
                    <span class="text-gray-600">#</span>
                    <code class="text-blue-400 select-all">sudo systemctl enable {{ Str::slug($service->name) }}.service</code>
                </div>
                <div class="flex items-center space-x-3 text-xs font-mono">
                    <span class="text-gray-600">#</span>
                    <code class="text-blue-400 select-all">sudo systemctl start {{ Str::slug($service->name) }}.service</code>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyUnit() {
        var copyText = document.getElementById("unit-content");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value).then(() => {
            alert("Configuration copied to clipboard!");
        });
    }
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
