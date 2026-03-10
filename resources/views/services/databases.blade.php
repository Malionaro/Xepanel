@extends('layouts.app')

@section('header_title', 'Database Management')

@section('content')
<div class="space-y-10">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
        <div>
            <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white leading-tight">Persistence Layer</h2>
            <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg font-medium">Provision and manage relational data environments for your services.</p>
        </div>
        <a href="{{ route('services.show', $service->id) }}" class="flex items-center space-x-3 px-6 py-3 rounded-2xl glass dark:bg-dark-card border-slate-200 dark:border-dark-border text-slate-600 dark:text-slate-300 text-xs font-black uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-800 transition-all hover:-translate-x-1">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back</span>
        </a>
    </div>

    @if(session('status'))
        <div class="bg-green-500/10 border border-green-500/20 text-green-600 dark:text-green-400 p-5 rounded-3xl flex items-center space-x-4 animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="w-10 h-10 rounded-xl bg-green-500/20 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
            <span class="text-sm font-bold">{{ session('status') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 p-5 rounded-3xl flex items-center space-x-4">
            <i data-lucide="alert-octagon" class="w-6 h-6"></i>
            <span class="text-sm font-bold">{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Database List -->
        <div class="lg:col-span-2 space-y-6">
            @forelse($databases as $db)
                <div class="glass dark:bg-dark-card rounded-[2.5rem] border border-slate-200 dark:border-white/5 p-8 shadow-xl relative overflow-hidden group">
                    <div class="absolute -right-16 -top-16 w-48 h-48 bg-emerald-500/5 rounded-full blur-3xl"></div>
                    
                    <div class="flex items-start justify-between relative z-10">
                        <div class="flex items-center space-x-5">
                            <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-500 border border-emerald-500/20 shadow-lg shadow-emerald-500/5">
                                <i data-lucide="database" class="w-7 h-7"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $db['db_name'] }}</h3>
                                <div class="flex items-center space-x-3 mt-1">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center">
                                        <i data-lucide="globe" class="w-3 h-3 mr-1.5"></i>
                                        {{ $db['db_host'] }}:{{ $db['db_port'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <form action="{{ route('services.databases.destroy', ['id' => $service->id, 'dbId' => $db['id']]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-400 hover:text-red-500 transition-all hover:bg-red-500/10 border border-slate-200 dark:border-white/10" onclick="return confirm('CRITICAL: Permanently drop database and user?')">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </form>
                    </div>

                    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4 relative z-10">
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/[0.02] border border-slate-100 dark:border-white/5 space-y-1">
                            <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest block">Username</span>
                            <div class="flex items-center justify-between">
                                <code class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300">{{ $db['db_user'] }}</code>
                                <button onclick="navigator.clipboard.writeText('{{ $db['db_user'] }}')" class="text-slate-400 hover:text-brand-500 transition-colors"><i data-lucide="copy" class="w-3 h-3"></i></button>
                            </div>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/[0.02] border border-slate-100 dark:border-white/5 space-y-1">
                            <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest block">Password</span>
                            <div class="flex items-center justify-between">
                                <input type="password" value="{{ $db['db_pass'] }}" class="bg-transparent border-none outline-none text-xs font-mono font-bold text-slate-700 dark:text-slate-300 w-full" readonly>
                                <button onclick="const i = this.previousElementSibling; i.type = i.type === 'password' ? 'text' : 'password';" class="text-slate-400 hover:text-brand-500 transition-colors mx-2"><i data-lucide="eye" class="w-3 h-3"></i></button>
                                <button onclick="navigator.clipboard.writeText('{{ $db['db_pass'] }}')" class="text-slate-400 hover:text-brand-500 transition-colors"><i data-lucide="copy" class="w-3 h-3"></i></button>
                            </div>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/[0.02] border border-slate-100 dark:border-white/5 space-y-1">
                            <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest block">External Access</span>
                            <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-tighter flex items-center">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                                Fully Routed
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-24 text-center glass rounded-[3rem] border-dashed border-2 border-slate-200 dark:border-white/10 opacity-50">
                    <div class="w-20 h-20 bg-slate-100 dark:bg-white/5 rounded-[2rem] flex items-center justify-center mx-auto mb-6 text-slate-300">
                        <i data-lucide="database-zap" class="w-10 h-10"></i>
                    </div>
                    <p class="text-slate-500 font-bold uppercase tracking-widest text-[10px]">No databases provisioned</p>
                </div>
            @endforelse
        </div>

        <!-- Creation Form -->
        <div class="lg:col-span-1">
            <form action="{{ route('services.databases.store', $service->id) }}" method="POST" class="glass dark:bg-dark-card p-10 rounded-[3rem] border border-slate-200 dark:border-white/5 shadow-2xl space-y-8 relative overflow-hidden group">
                <div class="absolute -right-24 -top-24 w-48 h-48 bg-emerald-500/5 rounded-full blur-3xl group-hover:bg-emerald-500/10 transition-colors duration-700"></div>
                
                @csrf
                <div class="flex items-center space-x-4 mb-4 relative z-10">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-500 border border-emerald-500/20">
                        <i data-lucide="plus-circle" class="w-7 h-7"></i>
                    </div>
                    <h3 class="font-black text-2xl text-slate-900 dark:text-white tracking-tight">New Database</h3>
                </div>
                
                <div class="space-y-3 relative z-10">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Database Name</label>
                    <div class="relative">
                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-mono text-xs font-bold">s{{ $service->id }}_</div>
                        <input type="text" name="database_name" class="w-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 pl-24 pr-6 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all dark:text-white font-bold text-sm shadow-sm" required placeholder="main_db">
                    </div>
                    <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest ml-1 leading-relaxed">System will auto-prefix identifiers for isolation.</p>
                </div>
                
                <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-black py-5 rounded-[1.5rem] transition-all shadow-xl shadow-emerald-500/25 active:scale-95 flex items-center justify-center space-x-3 group/submit relative z-10">
                    <i data-lucide="database" class="w-5 h-5 text-white transition-transform group-hover/submit:rotate-12"></i>
                    <span class="text-xs uppercase tracking-[0.2em]">PROVISION DATABASE</span>
                </button>
            </form>

            <div class="mt-8 p-8 glass dark:bg-dark-card border border-slate-200 dark:border-white/5 rounded-[2rem] shadow-lg">
                <h4 class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-4 flex items-center">
                    <i data-lucide="info" class="w-3.5 h-3.5 mr-2"></i>
                    Management Protocol
                </h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed font-medium">
                    All provisioned databases are accessible from the node network. Ensure your application uses the correct host address provided in the metadata.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>
@endsection
