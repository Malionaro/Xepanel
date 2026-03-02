<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - {{ \App\Models\Setting::get('panel_name', 'FilePanel') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' };
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="bg-gray-100 dark:bg-[#0d1117] text-gray-900 dark:text-[#c9d1d9] h-screen flex items-center justify-center p-4 transition-colors duration-300">
    <div class="max-w-md w-full text-center space-y-6">
        <div class="text-6xl text-red-500">🛠️</div>
        <h1 class="text-4xl font-extrabold">{{ \App\Models\Setting::get('panel_name', 'FilePanel') }}</h1>
        <h2 class="text-2xl font-bold">Maintenance Mode</h2>
        <p class="text-gray-600 dark:text-gray-400">The panel is currently undergoing maintenance. Only administrators can access the system at this time. Please try again later.</p>
        <div class="pt-6">
            <a href="{{ route('login') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded transition">Admin Login</a>
        </div>
    </div>
</body>
</html>
