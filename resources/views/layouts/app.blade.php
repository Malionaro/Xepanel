@php
    $currentRoute = request()->route() ? request()->route()->getName() : '';
    $brandColor = \App\Models\Setting::get('brand_primary_color', '#8b5cf6');
    $panelIcon = \App\Models\Setting::get('panel_icon', 'layers');
    $panelName = \App\Models\Setting::get('panel_name', 'FilePanel');

    // Sidebar Visibility: Hidden for infrastructure (services) related pages to provide immersive full-width view
    $showSidebar = !request()->routeIs('services.*') && !request()->routeIs('api.docs');
    
    // Get dynamic version from Git
    $gitHash = @shell_exec('git rev-parse --short HEAD');
    $appVersion = $gitHash ? 'BUILD-' . trim($gitHash) : '1.0.0-BETA1';
@endphp<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $panelName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '{{ $brandColor }}10', 100: '{{ $brandColor }}20', 200: '{{ $brandColor }}30', 
                            300: '{{ $brandColor }}40', 400: '{{ $brandColor }}60', 500: '{{ $brandColor }}', 
                            600: '{{ $brandColor }}ee', 700: '{{ $brandColor }}cc', 800: '{{ $brandColor }}aa', 900: '{{ $brandColor }}88',
                        },
                        slate: {
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617',
                        },
                        dark: { 
                            bg: '#020617', 
                            card: 'rgba(15, 23, 42, 0.6)', 
                            border: 'rgba(255, 255, 255, 0.08)', 
                            hover: 'rgba(30, 41, 59, 0.8)' 
                        }
                    }
                }
            }
        }
        const backendTheme = '{{ \App\Models\Setting::get('ui_theme', 'system') }}';
        if (backendTheme === 'dark') { document.documentElement.classList.add('dark'); }
        else if (backendTheme === 'light') { document.documentElement.classList.remove('dark'); }
        else {
            if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else { document.documentElement.classList.remove('dark'); }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; overflow: hidden; }
        
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .dark .glass {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        .sidebar-item-active { 
            background: linear-gradient(135deg, {{ $brandColor }} 0%, {{ $brandColor }}dd 100%); 
            color: white !important;
            box-shadow: 0 10px 20px -5px {{ $brandColor }}66;
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(139, 92, 246, 0.2); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(139, 92, 246, 0.4); }

        @keyframes fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fade-in 0.5s ease-out forwards; }
    </style>
</head>
<body class="bg-[#f8fafc] dark:bg-[#020617] text-slate-900 dark:text-slate-300 transition-colors duration-300" x-data="{ mobileSidebar: false }">
    <!-- Background Accents -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-10%] right-[-10%] w-[40%] h-[40%] bg-brand-500/5 dark:bg-brand-500/10 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[40%] h-[40%] bg-brand-700/5 dark:bg-brand-700/10 blur-[120px] rounded-full"></div>
    </div>

    <div class="flex h-screen relative z-10">
        @if($showSidebar)
        <!-- Sidebar -->
        <aside class="w-72 glass dark:bg-dark-card border-r border-slate-200 dark:border-dark-border hidden lg:flex flex-col shrink-0">
            <div class="p-8 overflow-y-auto custom-scrollbar flex-1">
                <a href="{{ route('services.index') }}" class="flex items-center space-x-4 mb-10 group">
                    <div class="w-12 h-12 rounded-2xl bg-brand-500 flex items-center justify-center text-white shadow-xl shadow-brand-500/20 group-hover:scale-110 transition-transform">
                        <i data-lucide="{{ $panelIcon }}" class="w-7 h-7"></i>
                    </div>
                    <span class="text-xl font-black tracking-tighter text-slate-900 dark:text-white">{{ $panelName }}</span>
                </a>

                <nav class="space-y-2">
                    <!-- User Account Section -->
                    <div class="pt-2" x-data="{ open: {{ (request()->routeIs('user.*')) ? 'true' : 'false' }} }">
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-4 ml-4">{{ __('panel.my_account') }}</p>
                        
                        <button @click="open = !open" class="w-full flex items-center justify-between px-5 py-3.5 rounded-2xl transition-all duration-300 text-slate-500 hover:bg-slate-100 dark:hover:bg-white/5 group">
                            <div class="flex items-center space-x-3">
                                <i data-lucide="user-cog" class="w-5 h-5"></i>
                                <span class="text-sm font-bold">Profile Settings</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': open }"></i>
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="mt-2 ml-4 space-y-1 border-l-2 border-slate-100 dark:border-white/5 pl-4">
                            <a href="{{ route('user.two-factor') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('user.two-factor') ? 'text-brand-500' : 'text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                                <span>{{ __('panel.security_2fa') }}</span>
                            </a>

                            <a href="{{ route('user.api-keys') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('user.api-keys') ? 'text-brand-500' : 'text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                <i data-lucide="key" class="w-4 h-4"></i>
                                <span>API Schlüssel</span>
                            </a>
                        </div>
                    </div>

                    @if(auth()->user()->role === 'admin')
                    <div class="pt-6" x-data="{ open: {{ (request()->routeIs('dashboard') || request()->routeIs('users.*') || request()->routeIs('eggs.*') || request()->routeIs('network.*') || request()->routeIs('logs.*') || request()->routeIs('settings.*')) ? 'true' : 'false' }} }">
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-4 ml-4">{{ __('panel.administration') }}</p>
                        
                        <!-- Collapsible Administrator Menu -->
                        <button @click="open = !open" class="w-full flex items-center justify-between px-5 py-3.5 rounded-2xl transition-all duration-300 text-slate-500 hover:bg-slate-100 dark:hover:bg-white/5 group">
                            <div class="flex items-center space-x-3">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                                <span class="text-sm font-bold">Administrator</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': open }"></i>
                        </button>
                        
                        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="mt-2 ml-4 space-y-1 border-l-2 border-slate-100 dark:border-white/5 pl-4">
                            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('dashboard') ? 'text-brand-500' : 'text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                <span>Global Analytics</span>
                            </a>
                            <a href="{{ route('users.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('users.*') ? 'text-brand-500' : 'text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                <i data-lucide="users" class="w-4 h-4"></i>
                                <span>{{ __('panel.user_management') }}</span>
                            </a>
                            <a href="{{ route('eggs.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('eggs.*') ? 'text-brand-500' : 'text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                <i data-lucide="egg" class="w-4 h-4"></i>
                                <span>Egg-Vorlagen</span>
                            </a>
                            <a href="{{ route('network.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('network.*') ? 'text-brand-500' : 'text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                <i data-lucide="globe" class="w-4 h-4"></i>
                                <span>Netzwerk</span>
                            </a>
                            <a href="{{ route('logs.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('logs.*') ? 'text-brand-500' : 'text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                <i data-lucide="scroll-text" class="w-4 h-4"></i>
                                <span>Audit Trail</span>
                            </a>
                            <a href="{{ route('settings.security') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('settings.security') ? 'text-brand-500' : 'text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                <i data-lucide="shield-alert" class="w-4 h-4"></i>
                                <span>Security & Sessions</span>
                            </a>
                            <a href="{{ route('settings.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('settings.index') ? 'text-brand-500' : 'text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                <i data-lucide="settings" class="w-4 h-4"></i>
                                <span>{{ __('panel.panel_settings') }}</span>
                            </a>
                        </div>
                    </div>
                    @endif
                </nav>
            </div>

            <div class="mt-auto p-8 border-t border-slate-100 dark:border-slate-800/50">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-500">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-black text-slate-900 dark:text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest truncate">{{ auth()->user()->role }}</p>
                    </div>
                </div>
                
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="w-full flex items-center space-x-3 px-5 py-3 rounded-xl text-red-500 hover:bg-red-500/10 transition-all duration-300 group">
                        <i data-lucide="log-out" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
                        <span class="text-[10px] font-black uppercase tracking-widest">{{ __('panel.sign_out') }}</span>
                    </button>
                </form>
            </div>
        </aside>
        @endif

        <div class="flex-1 flex flex-col overflow-hidden relative">
            <header class="h-20 glass dark:bg-dark-card border-b border-slate-200 dark:border-dark-border flex items-center justify-between px-6 md:px-10 shrink-0 z-10">
                <div class="flex items-center space-x-4">
                    @if($showSidebar)
                    <button class="lg:hidden p-2.5 rounded-xl glass-hover text-slate-500" @click="mobileSidebar = true">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                    @endif
                    
                    <a href="{{ route('services.index') }}" class="flex items-center space-x-4 group">
                        <div class="w-10 h-10 rounded-xl bg-brand-500 flex items-center justify-center text-white shadow-lg shadow-brand-500/20 group-hover:scale-110 transition-transform">
                            <i data-lucide="{{ $panelIcon }}" class="w-6 h-6"></i>
                        </div>
                        <span class="text-lg font-black tracking-tighter text-slate-900 dark:text-white">{{ $panelName }}</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('dashboard') }}" class="px-6 py-2.5 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-600 dark:text-brand-400 text-[10px] font-black uppercase tracking-widest hover:bg-brand-500 hover:text-white transition-all shadow-sm">
                        ADMINISTRATION
                    </a>
                    @endif

                    <button onclick="toggleTheme()" class="p-2.5 rounded-xl glass-hover text-slate-500 transition-all border border-slate-200 dark:border-dark-border">
                        <i data-lucide="sun" class="w-5 h-5 hidden dark:block text-brand-400"></i>
                        <i data-lucide="moon" class="w-5 h-5 block dark:hidden text-slate-600"></i>
                    </button>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6 md:p-10 custom-scrollbar">
                <div class="max-w-7xl mx-auto animate-fade-in">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar Drawer -->
    <div x-show="mobileSidebar" class="fixed inset-0 z-[100] lg:hidden" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="mobileSidebar = false"></div>
        <aside class="absolute top-0 left-0 bottom-0 w-72 glass dark:bg-dark-card border-r border-slate-200 dark:border-dark-border flex flex-col transform transition-transform duration-300" :class="mobileSidebar ? 'translate-x-0' : '-translate-x-full'">
            <div class="p-8 overflow-y-auto custom-scrollbar flex-1">
                <div class="flex items-center justify-between mb-10">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 rounded-xl bg-brand-500 flex items-center justify-center text-white">
                            <i data-lucide="{{ $panelIcon }}" class="w-6 h-6"></i>
                        </div>
                        <span class="text-lg font-black tracking-tighter text-slate-900 dark:text-white">{{ $panelName }}</span>
                    </div>
                    <button @click="mobileSidebar = false" class="text-slate-400">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <nav class="space-y-4">
                    <a href="{{ route('services.index') }}" class="flex items-center space-x-3 px-5 py-3 rounded-xl transition-all text-slate-500">
                        <i data-lucide="server" class="w-5 h-5"></i>
                        <span class="text-sm font-bold">My Services</span>
                    </a>
                </nav>
            </div>
        </aside>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) { 
                document.documentElement.classList.remove('dark'); 
                localStorage.setItem('theme', 'light'); 
            } else { 
                document.documentElement.classList.add('dark'); 
                localStorage.setItem('theme', 'dark'); 
            }
        }
        lucide.createIcons();
    </script>
</body>
</html>
