<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\Models\Setting::get('panel_name', 'FilePanel') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f5f3ff', 100: '#ede9fe', 200: '#ddd6fe', 300: '#c4b5fd', 400: '#a78bfa',
                            500: '#8b5cf6', 600: '#7c3aed', 700: '#6d28d9', 800: '#5b21b6', 900: '#4c1d95',
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @keyframes slide-in-right {
            from { transform: translateX(100px); opacity: 0; filter: blur(10px); }
            to { transform: translateX(0); opacity: 1; filter: blur(0); }
        }
        
        @keyframes slide-out-left {
            from { transform: translateX(0); opacity: 1; filter: blur(0); }
            to { transform: translateX(-100px); opacity: 0; filter: blur(10px); }
        }

        .animate-slide-in { animation: slide-in-right 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
        .animate-slide-out { animation: slide-out-left 0.5s ease-in forwards; }

        .sidebar-item-active { 
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); 
            color: white !important;
            box-shadow: 0 10px 15px -3px rgba(109, 40, 217, 0.3);
        }
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
        .glass-hover:hover {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(226, 232, 240, 1);
        }
        .dark .glass-hover:hover {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .glow-purple { box-shadow: 0 10px 15px -3px rgba(139, 92, 246, 0.05); }
        .dark .glow-purple { box-shadow: 0 0 20px rgba(139, 92, 246, 0.15); }
        .glow-green { box-shadow: 0 10px 15px -3px rgba(34, 197, 94, 0.05); }
        .dark .glow-green { box-shadow: 0 0 20px rgba(34, 197, 94, 0.2); }
        .hover-lift { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .hover-lift:hover { transform: translateY(-4px); }
        
        @keyframes pulse-glow {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }
        .pulse-online { animation: pulse-glow 2s infinite; }
        
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(139, 92, 246, 0.2); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(139, 92, 246, 0.4); }
    </style>
</head>
<body class="bg-[#f8fafc] dark:bg-[#020617] text-slate-900 dark:text-slate-300 transition-colors duration-300">
    <!-- Background Accents -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-10%] right-[-10%] w-[40%] h-[40%] bg-brand-500/5 dark:bg-brand-500/10 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[40%] h-[40%] bg-brand-700/5 dark:bg-brand-700/10 blur-[120px] rounded-full"></div>
    </div>

    <div class="flex h-screen overflow-hidden relative z-10">
        <!-- Sidebar -->
        <aside id="main-sidebar" class="w-0 opacity-0 invisible glass dark:bg-dark-card border-r border-slate-200 dark:border-dark-border flex flex-col transition-all duration-500 z-20 overflow-hidden">
            <div class="p-8 flex justify-between items-center min-w-[18rem]">
                <a href="{{ route('services.index') }}" class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-brand-500 to-brand-700 rounded-xl flex items-center justify-center text-white shadow-lg shadow-brand-500/20">
                        <i data-lucide="layers" class="w-6 h-6"></i>
                    </div>
                    <span class="font-bold text-xl tracking-tight text-slate-900 dark:text-white">{{ \App\Models\Setting::get('panel_name', 'FilePanel') }}</span>
                </a>
            </div>

            <nav class="flex-1 overflow-y-auto px-4 py-2 space-y-8">
                <!-- Main Nav -->
                <div>
                    <span class="px-4 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">{{ __('panel.main_menu') }}</span>
                    <div class="mt-4 space-y-1">
                        <a href="{{ route('services.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('services.index') ? 'sidebar-item-active' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-dark-hover hover:text-brand-600 dark:hover:text-white' }}">
                            <i data-lucide="server" class="w-5 h-5"></i>
                            <span class="font-semibold text-sm">{{ __('panel.my_services') }}</span>
                        </a>
                        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'sidebar-item-active' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-dark-hover hover:text-brand-600 dark:hover:text-white' }}">
                            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                            <span class="font-semibold text-sm">{{ __('panel.dashboard') }}</span>
                        </a>
                    </div>
                </div>

                @if(Auth::user() && Auth::user()->role == 'admin')
                <div>
                    <span class="px-4 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">{{ __('panel.administration') }}</span>
                    <div class="mt-4 space-y-1">
                        <a href="{{ route('settings.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('settings.*') ? 'sidebar-item-active' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-dark-hover hover:text-brand-600 dark:hover:text-white' }}">
                            <i data-lucide="settings" class="w-5 h-5"></i>
                            <span class="font-semibold text-sm">{{ __('panel.panel_settings') }}</span>
                        </a>
                        <a href="{{ route('users.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('users.*') ? 'sidebar-item-active' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-dark-hover hover:text-brand-600 dark:hover:text-white' }}">
                            <i data-lucide="users" class="w-5 h-5"></i>
                            <span class="font-semibold text-sm">{{ __('panel.user_management') }}</span>
                        </a>
                        <a href="{{ route('network.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('network.*') ? 'sidebar-item-active' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-dark-hover hover:text-brand-600 dark:hover:text-white' }}">
                            <i data-lucide="network" class="w-5 h-5"></i>
                            <span class="font-semibold text-sm">{{ __('panel.network_ports') }}</span>
                        </a>
                        <a href="{{ route('eggs.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('eggs.*') ? 'sidebar-item-active' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-dark-hover hover:text-brand-600 dark:hover:text-white' }}">
                            <i data-lucide="egg" class="w-5 h-5"></i>
                            <span class="font-semibold text-sm">{{ __('panel.egg_templates') }}</span>
                        </a>
                        <a href="{{ route('logs.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('logs.*') ? 'sidebar-item-active' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-dark-hover hover:text-brand-600 dark:hover:text-white' }}">
                            <i data-lucide="history" class="w-5 h-5"></i>
                            <span class="font-semibold text-sm">{{ __('panel.activity_logs') }}</span>
                        </a>
                    </div>
                </div>
                @endif

                <div>
                    <span class="px-4 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">{{ __('panel.my_account') }}</span>
                    <div class="mt-4 space-y-1">
                        <a href="{{ route('user.two-factor') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('user.two-factor') ? 'sidebar-item-active' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-dark-hover hover:text-brand-600 dark:hover:text-white' }}">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                            <span class="font-semibold text-sm">{{ __('panel.security_2fa') }}</span>
                        </a>
                        <a href="{{ route('user.api-keys') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('user.api-keys') ? 'sidebar-item-active' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-dark-hover hover:text-brand-600 dark:hover:text-white' }}">
                            <i data-lucide="key" class="w-5 h-5"></i>
                            <span class="font-semibold text-sm">{{ __('panel.api_keys') }}</span>
                        </a>
                    </div>
                </div>
            </nav>

            <div class="p-6 border-t border-slate-200 dark:border-dark-border glass dark:bg-transparent">
                <div class="flex items-center space-x-4 p-3 rounded-2xl bg-white/50 dark:bg-slate-900/10 border border-white/20 dark:border-white/5 shadow-sm">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-[11px] font-black text-white shadow-lg uppercase relative">
                        {{ substr(Auth::user()->name, 0, 2) }}
                        <div class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-green-500 border-2 border-white dark:border-[#020617] rounded-full"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-black truncate text-slate-900 dark:text-white tracking-tight">{{ Auth::user()->name }}</p>
                        <p class="text-[9px] text-green-500 uppercase font-bold tracking-widest flex items-center">
                            <span class="w-1 h-1 rounded-full bg-green-500 mr-1.5 animate-pulse"></span>
                            Online
                        </p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                        @csrf
                        <button class="p-2 text-slate-400 hover:text-red-500 transition-colors">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden relative">
            @if(\App\Models\Setting::get('maintenance_mode', false))
                <div class="bg-red-600/90 backdrop-blur-md text-white text-center py-2 text-[10px] font-black uppercase tracking-[0.3em] shadow-lg relative z-50">
                    ⚠️ {{ __('panel.maintenance_mode') }} ⚠️
                </div>
            @endif
            
            <header class="h-20 glass dark:bg-dark-card border-b border-slate-200 dark:border-dark-border flex items-center justify-between px-10 shrink-0 z-10">
                <h1 class="text-xs font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">@yield('header_title', __('panel.overview'))</h1>
                
                <div class="flex items-center">
                    <button onclick="toggleTheme()" class="p-2.5 rounded-xl glass-hover text-slate-500 transition-all border border-slate-200 dark:border-dark-border">
                        <i data-lucide="sun" class="w-5 h-5 hidden dark:block text-brand-400"></i>
                        <i data-lucide="moon" class="w-5 h-5 block dark:hidden text-slate-600"></i>
                    </button>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-10 z-0">
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    <script>
        function navigateWithAnimation(url) {
            const main = document.querySelector('main > div');
            main.classList.add('animate-slide-out');
            setTimeout(() => {
                window.location.href = url;
            }, 450);
        }

        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) { document.documentElement.classList.remove('dark'); localStorage.setItem('theme', 'light'); }
            else { document.documentElement.classList.add('dark'); localStorage.setItem('theme', 'dark'); }
        }
        
        // Default sidebar visibility logic
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('main-sidebar');
            const mainContent = document.querySelector('main > div');
            const currentRoute = '{{ request()->route() ? request()->route()->getName() : '' }}';
            
            if (currentRoute === 'dashboard') {
                mainContent.classList.add('animate-slide-in');
            }

            // Sidebar should be HIDDEN only on these specific service-centric pages
            const hideSidebarOn = ['services.index', 'services.show', 'services.files', 'services.create', 'services.edit'];
            
            if (!hideSidebarOn.includes(currentRoute) || currentRoute === 'dashboard') {
                sidebar.classList.remove('w-0', 'opacity-0', 'invisible');
                sidebar.classList.add('w-72', 'opacity-100', 'visible');
            }
        });

        lucide.createIcons();
    </script>
</body>
</html>
