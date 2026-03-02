@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center space-x-2 text-gray-600 dark:text-gray-400 mb-4">
        <a href="{{ route('dashboard') }}" class="hover:text-gray-900 dark:text-white transition">Dashboard</a>
        <span>/</span>
        <a href="{{ route('services.show', $service->id) }}" class="hover:text-gray-900 dark:text-white transition">{{ $service->name }}</a>
        <span>/</span>
        <a href="{{ route('services.schedules', $service->id) }}" class="hover:text-gray-900 dark:text-white transition">Schedules</a>
        <span>/</span>
        <span class="text-gray-900 dark:text-white">Edit Task</span>
    </div>

    <h2 class="text-3xl font-bold">Edit Scheduled Task</h2>

    <form action="{{ route('services.schedules.update', ['id' => $service->id, 'taskId' => $task['id']]) }}" method="POST" class="card bg-white dark:bg-[#161b22] border border-gray-300 dark:border-[#30363d] p-8 rounded-lg shadow-sm space-y-6 transition-colors duration-300">
        @csrf
        @method('PUT')
        
        <div>
            <label class="block text-sm mb-1 text-gray-600 dark:text-gray-400">Task Name</label>
            <input type="text" name="name" value="{{ old('name', $task['name']) }}" class="w-full bg-gray-50 dark:bg-[#0d1117] border border-gray-300 dark:border-[#30363d] rounded p-2 focus:border-blue-500 outline-none text-sm transition-colors" required>
        </div>
        
        <div>
            <label class="block text-sm mb-1 text-gray-600 dark:text-gray-400">Cron Expression</label>
            <input type="text" name="cron" value="{{ old('cron', $task['cron']) }}" class="w-full bg-gray-50 dark:bg-[#0d1117] border border-gray-300 dark:border-[#30363d] rounded p-2 focus:border-blue-500 outline-none font-mono text-sm transition-colors" required>
            <p class="text-[10px] text-gray-500 mt-1">Format: MIN HOUR DAY MONTH DAY_OF_WEEK</p>
        </div>

        <div>
            <label class="block text-sm mb-1 text-gray-600 dark:text-gray-400">Command (runs in service dir)</label>
            <input type="text" name="command" value="{{ old('command', $task['command']) }}" class="w-full bg-gray-50 dark:bg-[#0d1117] border border-gray-300 dark:border-[#30363d] rounded p-2 focus:border-blue-500 outline-none font-mono text-sm transition-colors" required>
        </div>

        @if($errors->any())
            <div class="text-red-500 text-sm mt-2">{{ $errors->first() }}</div>
        @endif

        <div class="pt-4 flex space-x-4 border-t border-gray-200 dark:border-[#30363d]">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded transition shadow">Update Task</button>
            <a href="{{ route('services.schedules', $service->id) }}" class="bg-gray-200 dark:bg-[#21262d] hover:bg-gray-300 dark:hover:bg-[#30363d] text-gray-900 dark:text-white py-2 px-6 rounded transition border border-gray-300 dark:border-[#30363d]">Cancel</a>
        </div>
    </form>
</div>
@endsection
