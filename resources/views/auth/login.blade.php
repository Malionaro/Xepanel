<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php $panelName = \App\Models\Setting::get('panel_name', 'FilePanel'); @endphp
    <title>Login - {{ $panelName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            200: '#bae0fd',
                            300: '#7cc8fb',
                            400: '#36acf7',
                            500: '#0c91eb',
                            600: '#0073ca',
                            700: '#015ba3',
                            800: '#064e86',
                            900: '#0a416f',
                        },
                        dark: {
                            bg: '#0d1117',
                            card: '#161b22',
                            border: '#30363d',
                            hover: '#21262d'
                        }
                    }
                }
            }
        }
        
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .animate-float { animation: float 6s ease-in-out infinite; }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-dark-bg text-gray-900 dark:text-[#c9d1d9] min-h-screen flex items-center justify-center p-6 transition-colors duration-300 relative overflow-hidden">
    
    <!-- Background Decorations -->
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-brand-500/10 rounded-full blur-[120px] animate-pulse"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-purple-500/10 rounded-full blur-[120px] animate-pulse" style="animation-delay: 2s"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Logo & Title -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-brand-500 rounded-[2.5rem] shadow-2xl shadow-brand-500/30 text-white mb-6 animate-float">
                <i data-lucide="box" class="w-10 h-10"></i>
            </div>
            <h1 class="text-4xl font-black tracking-tight text-gray-900 dark:text-white mb-2">{{ $panelName }}</h1>
            <p class="text-gray-500 dark:text-gray-400 font-medium">Authentication Portal</p>
        </div>

        <!-- Login Card -->
        <div class="card bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border p-10 rounded-[3rem] shadow-2xl space-y-8">
            <div class="space-y-2">
                <h2 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Sign In</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Please enter your credentials to continue.</p>
            </div>

            <form action="/login" method="POST" class="space-y-6">
                @csrf
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ml-1">Email Address</label>
                    <div class="relative">
                        <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="email" name="email" class="w-full bg-gray-50 dark:bg-[#0d1117] border border-gray-200 dark:border-dark-border rounded-2xl py-3.5 pl-12 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required value="{{ old('email') }}" placeholder="admin@example.com">
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex justify-between items-center ml-1">
                        <label class="block text-xs font-black uppercase tracking-widest text-gray-400">Password</label>
                    </div>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="password" name="password" class="w-full bg-gray-50 dark:bg-[#0d1117] border border-gray-200 dark:border-dark-border rounded-2xl py-3.5 pl-12 pr-4 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all dark:text-white font-medium" required placeholder="••••••••">
                    </div>
                </div>

                @if($errors->any())
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 text-red-600 dark:text-red-400 p-4 rounded-2xl flex items-center space-x-3 animate-in fade-in slide-in-from-top-2 duration-300">
                        <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                        <span class="text-sm font-bold">{{ $errors->first() }}</span>
                    </div>
                @endif

                <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-4 rounded-2xl transition-all shadow-xl shadow-brand-500/25 active:scale-95 flex items-center justify-center space-x-2">
                    <span>SECURE LOGIN</span>
                    <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </button>
            </form>
        </div>

        <!-- Footer Info -->
        <p class="text-center mt-10 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]">
            &copy; {{ date('Y') }} {{ $panelName }} &bull; All rights reserved
        </p>
    </div>

    <script>
        // Initialize Lucide
        lucide.createIcons();
    </script>
</body>
</html>
