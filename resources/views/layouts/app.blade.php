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
                            50: '#f0f7ff', 100: '#e0effe', 200: '#bae0fd', 300: '#7cc8fb', 400: '#36acf7',
                            500: '#0c91eb', 600: '#0073ca', 700: '#015ba3', 800: '#064e86', 900: '#0a416f',
                        },
                        dark: { bg: '#0d1117', card: '#161b22', border: '#30363d', hover: '#21262d' }
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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .sidebar-item-active { background-color: #0c91eb; color: white !important; }
        .hover-lift { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .hover-lift:hover { transform: translateY(-4px) scale(1.01); box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1); }
        .active-press { transition: transform 0.1s; }
        .active-press:active { transform: scale(0.97); }
        @keyframes pulse-glow {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
            70% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }
        .pulse-online { animation: pulse-glow 2s infinite; }
        .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05), transparent);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
        @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.2); border-radius: 10px; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-dark-bg text-gray-900 dark:text-[#c9d1d9] transition-colors duration-300">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-72 bg-white dark:bg-dark-card border-r border-gray-200 dark:border-dark-border flex flex-col transition-all">
            <div class="p-6 border-b border-gray-200 dark:border-dark-border flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-brand-500 rounded-lg flex items-center justify-center text-white shadow-lg shadow-brand-500/20">
                        <i data-lucide="box" class="w-5 h-5"></i>
                    </div>
                    <span class="font-bold text-lg tracking-tight text-gray-900 dark:text-white">{{ \App\Models\Setting::get('panel_name', 'FilePanel') }}</span>
                </div>
                <button onclick="toggleTheme()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-hover text-gray-500 transition-all">
                    <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
                    <i data-lucide="moon" class="w-5 h-5 block dark:hidden"></i>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto p-4 space-y-6">
                <!-- Main Nav -->
                <div>
                    <span class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-opacity-70">{{ __('panel.main_menu') }}</span>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'sidebar-item-active shadow-md shadow-brand-500/20' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-dark-hover dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                            <span class="font-medium">{{ __('panel.dashboard') }}</span>
                        </a>
                        <a href="{{ route('services.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('services.*') ? 'sidebar-item-active shadow-md shadow-brand-500/20' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-dark-hover dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                            <i data-lucide="server" class="w-5 h-5"></i>
                            <span class="font-medium">{{ __('panel.my_services') }}</span>
                        </a>
                    </div>
                </div>

                @if(Auth::user() && Auth::user()->role == 'admin')
                <div>
                    <span class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-opacity-70">{{ __('panel.administration') }}</span>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('settings.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('settings.*') ? 'sidebar-item-active shadow-md shadow-brand-500/20' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-dark-hover dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                            <i data-lucide="settings" class="w-5 h-5"></i>
                            <span class="font-medium">{{ __('panel.panel_settings') }}</span>
                        </a>
                        <a href="{{ route('users.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('users.*') ? 'sidebar-item-active shadow-md shadow-brand-500/20' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-dark-hover dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                            <i data-lucide="users" class="w-5 h-5"></i>
                            <span class="font-medium">{{ __('panel.user_management') }}</span>
                        </a>
                        <a href="{{ route('network.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('network.*') ? 'sidebar-item-active shadow-md shadow-brand-500/20' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-dark-hover dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                            <i data-lucide="network" class="w-5 h-5"></i>
                            <span class="font-medium">{{ __('panel.network_ports') }}</span>
                        </a>
                        <a href="{{ route('eggs.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('eggs.*') ? 'sidebar-item-active shadow-md shadow-brand-500/20' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-dark-hover dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                            <i data-lucide="egg" class="w-5 h-5"></i>
                            <span class="font-medium">{{ __('panel.egg_templates') }}</span>
                        </a>
                        <a href="{{ route('logs.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('logs.*') ? 'sidebar-item-active shadow-md shadow-brand-500/20' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-dark-hover dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                            <i data-lucide="history" class="w-5 h-5"></i>
                            <span class="font-medium">{{ __('panel.activity_logs') }}</span>
                        </a>
                    </div>
                </div>
                @endif

                <div>
                    <span class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-opacity-70">{{ __('panel.my_account') }}</span>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('user.two-factor') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('user.two-factor') ? 'sidebar-item-active shadow-md shadow-brand-500/20' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-dark-hover dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                            <span class="font-medium">{{ __('panel.security_2fa') }}</span>
                        </a>
                        <a href="{{ route('user.api-keys') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('user.api-keys') ? 'sidebar-item-active shadow-md shadow-brand-500/20' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-dark-hover dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                            <i data-lucide="key" class="w-5 h-5"></i>
                            <span class="font-medium">{{ __('panel.api_keys') }}</span>
                        </a>
                    </div>
                </div>
            </nav>

            <div class="p-4 border-t border-gray-200 dark:border-dark-border bg-gray-50 dark:bg-[#1c2128]">
                <div class="flex items-center space-x-3 px-4 py-2 mb-2">
                    <div class="w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center text-[10px] font-black text-white shadow-lg shadow-brand-500/20 uppercase">{{ substr(Auth::user()->name, 0, 2) }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold truncate text-gray-900 dark:text-white">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] text-gray-500 truncate uppercase tracking-tighter">{{ Auth::user()->role }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="w-full flex items-center space-x-3 px-4 py-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all text-sm font-bold uppercase tracking-wider">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span>{{ __('panel.sign_out') }}</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            @if(\App\Models\Setting::get('maintenance_mode', false))
                <div class="bg-red-600 text-white text-center py-1.5 text-[10px] font-black uppercase tracking-[0.2em] shadow-lg relative z-50">
                    ⚠️ {{ __('panel.maintenance_mode') }} ⚠️
                </div>
            @endif
            <header class="h-16 bg-white dark:bg-dark-card border-b border-gray-200 dark:border-dark-border flex items-center px-8 shrink-0">
                <h1 class="text-sm font-bold text-gray-400 uppercase tracking-widest">@yield('header_title', __('panel.overview'))</h1>
            </header>
            <main class="flex-1 overflow-y-auto p-8 bg-gray-50 dark:bg-dark-bg transition-colors duration-300">@yield('content')</main>
        </div>
    </div>
    <script>
        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) { document.documentElement.classList.remove('dark'); localStorage.setItem('theme', 'light'); }
            else { document.documentElement.classList.add('dark'); localStorage.setItem('theme', 'dark'); }
        }
        lucide.createIcons();
    </script>
</body>
</html>
