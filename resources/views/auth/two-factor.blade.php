<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - FilePanel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' };
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        .card { transition: background-color 0.3s, border-color 0.3s; }
    </style>
</head>
<body class="bg-gray-100 dark:bg-[#0d1117] text-gray-900 dark:text-[#c9d1d9] h-screen flex items-center justify-center p-4 transition-colors duration-300">
    <div class="card bg-white dark:bg-[#161b22] border border-gray-300 dark:border-[#30363d] w-full max-w-md p-8 rounded-lg shadow-xl">
        <h1 class="text-2xl font-bold mb-2 text-center">Two-Factor Authentication</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-6">Please enter the authentication code from your authenticator app.</p>

        <form action="{{ route('two-factor.verify') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm mb-1 text-gray-600 dark:text-gray-400">Authentication Code</label>
                <input type="text" name="code" class="w-full bg-gray-50 dark:bg-[#0d1117] border border-gray-300 dark:border-[#30363d] rounded p-2 focus:border-blue-500 outline-none font-mono text-center tracking-widest text-lg" required autofocus autocomplete="one-time-code">
            </div>
            
            @if($errors->any())
                <p class="text-red-500 text-sm mt-2 text-center">{{ $errors->first() }}</p>
            @endif
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-gray-900 dark:text-white font-bold py-2 rounded transition mt-4">Verify Code</button>
        </form>
    </div>
</body>
</html>
