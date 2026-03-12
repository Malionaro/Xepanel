@extends('layouts.app')

@section('header_title', __('panel.file_manager'))

@section('content')
<!-- Include Ace Editor -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.7/ace.js"></script>

<div class="space-y-10">
    <!-- File Manager View -->
    <div id="file-manager-view" class="space-y-10 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div class="flex flex-col">
                <h2 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white uppercase italic tracking-[0.05em]">{{ __('panel.file_manager') }}</h2>
                <p class="text-xs font-bold text-slate-500 mt-1">{{ __('panel.manage_files_for', ['name' => $service->name]) }}</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('services.show', $service->id) }}" class="flex items-center space-x-3 px-6 py-3 rounded-2xl glass dark:bg-dark-card border-slate-200 dark:border-dark-border text-slate-600 dark:text-slate-300 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-800 transition-all hover:-translate-x-1 shadow-sm">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>{{ __('panel.back') }}</span>
                </a>
            </div>
        </div>

        <!-- Action Bar & Mass Operations -->
        <div class="flex flex-wrap items-center justify-between gap-6 glass dark:bg-white/5 p-6 rounded-[2.5rem] border border-slate-200 dark:border-white/5 shadow-xl">
            <div class="flex items-center space-x-4">
                <button onclick="toggleAllCheckboxes()" class="p-3 rounded-xl bg-slate-100 dark:bg-white/5 text-slate-500 hover:text-brand-500 transition-all border border-slate-200 dark:border-white/10" title="{{ __('panel.select_all') }}">
                    <i data-lucide="check-square" class="w-5 h-5"></i>
                </button>
                <div class="h-8 w-px bg-slate-200 dark:bg-white/10 mx-2"></div>
                <div id="mass-actions" class="flex items-center space-x-3 opacity-50 pointer-events-none transition-all duration-300">
                    <button onclick="confirmMassDelete()" class="flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-red-500/10 text-red-500 border border-red-500/20 hover:bg-red-500 hover:text-white transition-all text-[10px] font-black uppercase tracking-widest">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        <span>{{ __('panel.delete') }}</span>
                    </button>
                    <button onclick="massCompress()" class="flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-brand-500/10 text-brand-500 border border-brand-500/20 hover:bg-brand-500 hover:text-white transition-all text-[10px] font-black uppercase tracking-widest">
                        <i data-lucide="archive" class="w-4 h-4"></i>
                        <span>{{ __('panel.archive') }}</span>
                    </button>
                    <button onclick="massExtract()" class="flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 hover:bg-emerald-500 hover:text-white transition-all text-[10px] font-black uppercase tracking-widest">
                        <i data-lucide="archive-restore" class="w-4 h-4"></i>
                        <span>{{ __('panel.extract') }}</span>
                    </button>
                </div>
            </div>
            
            <div class="flex items-center space-x-3">
                <button onclick="document.getElementById('upload-input').click()" class="flex items-center space-x-3 px-6 py-3 rounded-2xl glass dark:bg-dark-card border border-slate-200 dark:border-dark-border text-slate-600 dark:text-slate-300 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-800 transition-all shadow-sm">
                    <i data-lucide="upload-cloud" class="w-4 h-4 text-brand-500"></i>
                    <span>{{ __('panel.upload') }}</span>
                </button>
                <button onclick="showCreateModal('file')" class="flex items-center space-x-3 px-6 py-3 rounded-2xl bg-brand-500 text-white text-[10px] font-black uppercase tracking-widest shadow-xl shadow-brand-500/25 hover:bg-brand-600 transition-all">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('panel.new_file') }}</span>
                </button>
                <button onclick="showCreateModal('directory')" class="flex items-center space-x-3 px-6 py-3 rounded-2xl glass dark:bg-dark-card border border-slate-200 dark:border-dark-border text-slate-600 dark:text-slate-300 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-800 transition-all shadow-sm">
                    <i data-lucide="folder-plus" class="w-4 h-4"></i>
                    <span>{{ __('panel.folder') }}</span>
                </button>
            </div>
        </div>

        <!-- File List Table -->
        <div class="glass dark:bg-dark-card rounded-[2.5rem] border border-slate-200 dark:border-dark-border overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="files-table">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-white/5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-100 dark:border-white/5">
                            <th class="px-6 py-4 w-12 text-center">#</th>
                            <th class="px-6 py-4">{{ __('panel.metadata_name') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('panel.operations') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @if($relativePath !== '')
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-4"></td>
                                <td class="px-6 py-4" colspan="2">
                                    <a href="?path={{ dirname($relativePath) === '.' ? '' : dirname($relativePath) }}" class="flex items-center space-x-3 text-slate-400 hover:text-brand-500 transition-colors font-bold text-sm">
                                        <i data-lucide="corner-left-up" class="w-4 h-4"></i>
                                        <span>..</span>
                                    </a>
                                </td>
                            </tr>
                        @endif

                        @foreach($directories as $directory)
                            @php $dirName = basename($directory); @endphp
                            <tr class="group hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-4 text-center">
                                    <input type="checkbox" name="selected_files[]" value="{{ $relativePath ? $relativePath . '/' . $dirName : $dirName }}" onchange="updateActionState()" class="file-checkbox w-4 h-4 rounded-lg border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-brand-500 focus:ring-brand-500/20 transition-all cursor-pointer">
                                </td>
                                <td class="px-6 py-4">
                                    <a href="?path={{ $relativePath ? $relativePath . '/' . $dirName : $dirName }}" class="flex items-center space-x-4">
                                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500 border border-blue-500/20 group-hover:scale-110 transition-transform shadow-sm">
                                            <i data-lucide="folder" class="w-4 h-4 fill-current opacity-20"></i>
                                        </div>
                                        <span class="font-bold text-slate-900 dark:text-white tracking-tight text-sm">{{ $dirName }}</span>
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button onclick="renameItem('{{ $relativePath ? $relativePath . '/' . $dirName : $dirName }}', '{{ $dirName }}')" class="p-2 text-slate-400 hover:text-brand-500 transition-all rounded-lg hover:bg-brand-500/10" title="{{ __('panel.rename') }}"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                                        <button onclick="deleteItem('{{ $relativePath ? $relativePath . '/' . $dirName : $dirName }}')" class="p-2 text-slate-400 hover:text-red-500 transition-all rounded-lg hover:bg-red-500/10" title="{{ __('panel.delete') }}"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        @foreach($files as $file)
                            @php 
                                $fileName = basename($file); 
                                $isZip = strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'zip';
                            @endphp
                            <tr class="group hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-4 text-center">
                                    <input type="checkbox" name="selected_files[]" value="{{ $relativePath ? $relativePath . '/' . $fileName : $fileName }}" onchange="updateActionState()" class="file-checkbox w-4 h-4 rounded-lg border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-brand-500 focus:ring-brand-500/20 transition-all cursor-pointer">
                                </td>
                                <td class="px-6 py-4">
                                    <div onclick="editFile('{{ $relativePath ? $relativePath . '/' . $fileName : $fileName }}')" class="flex items-center space-x-4 cursor-pointer">
                                        <div class="w-10 h-10 rounded-xl {{ $isZip ? 'bg-amber-500/10 text-amber-500 border-amber-500/20' : 'bg-slate-500/10 text-slate-500 dark:text-slate-400 border-slate-500/20' }} flex items-center justify-center border group-hover:scale-110 transition-transform shadow-sm">
                                            <i data-lucide="{{ $isZip ? 'archive' : 'file-text' }}" class="w-4 h-4"></i>
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-bold text-slate-900 dark:text-white tracking-tight truncate max-w-md text-sm">{{ $fileName }}</span>
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">{{ number_format(File::size($file) / 1024, 2) }} KB</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        @if($isZip)
                                            <button onclick="extractZip('{{ $relativePath ? $relativePath . '/' . $fileName : $fileName }}')" class="p-2 text-emerald-500 hover:bg-emerald-500/10 rounded-lg transition-all" title="{{ __('panel.extract') }}"><i data-lucide="archive-restore" class="w-4 h-4"></i></button>
                                        @endif                                        <button onclick="renameItem('{{ $relativePath ? $relativePath . '/' . $fileName : $fileName }}', '{{ $fileName }}')" class="p-2 text-slate-400 hover:text-brand-500 transition-all rounded-lg hover:bg-brand-500/10" title="{{ __('panel.rename') }}"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                                        <button onclick="deleteItem('{{ $relativePath ? $relativePath . '/' . $fileName : $fileName }}')" class="p-2 text-slate-400 hover:text-red-500 transition-all rounded-lg hover:bg-red-500/10" title="{{ __('panel.delete') }}"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- File Editor View (Initially Hidden) -->
    <div id="file-editor-view" class="hidden space-y-8 animate-fade-in">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center space-x-5">
                <div class="w-14 h-14 rounded-3xl bg-brand-500/10 flex items-center justify-center text-brand-500 border border-brand-500/20 shadow-xl">
                    <i data-lucide="file-code" class="w-7 h-7"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center space-x-3">
                        <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight truncate max-w-md" id="editor-title-filename">Editor</h2>
                        <span id="editor-language-badge" class="px-2.5 py-1 rounded-lg bg-brand-500/10 text-brand-500 text-[9px] font-black uppercase tracking-widest border border-brand-500/20">Text</span>
                    </div>
                    <div class="flex items-center space-x-3 mt-1.5">
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest flex items-center">
                            <i data-lucide="terminal" class="w-3.5 h-3.5 mr-2"></i>
                            {{ __('panel.infrastructure') }}: <span class="text-brand-500 ml-1.5">{{ $service->name }}</span>
                        </p>
                        <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-700"></span>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest" id="editor-line-count">0 {{ __('panel.lines') }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <button onclick="closeEditor()" class="px-8 py-3.5 rounded-2xl glass dark:bg-dark-card border-slate-200 dark:border-dark-border text-slate-600 dark:text-slate-300 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-800 transition-all shadow-sm">
                    {{ __('panel.abort') }}
                </button>
                <button onclick="saveFile()" class="px-10 py-3.5 rounded-2xl bg-brand-500 text-white text-[10px] font-black uppercase tracking-widest shadow-xl shadow-brand-500/25 hover:bg-brand-600 transition-all hover:-translate-y-0.5 active:scale-95 flex items-center justify-center space-x-3">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>{{ __('panel.save') }} & {{ __('panel.deployment') }}</span>
                </button>
            </div>
        </div>

        <div class="glass dark:bg-dark-card rounded-[3rem] border border-slate-200 dark:border-dark-border overflow-hidden shadow-2xl flex flex-col h-[700px] group/editor relative">
            <!-- Editor Toolbar -->
            <div class="px-8 py-4 bg-slate-50/50 dark:bg-white/5 border-b border-slate-100 dark:border-white/5 flex items-center justify-between relative z-10">
                <div class="flex items-center space-x-6">
                    <div class="flex items-center space-x-2 text-[9px] font-black text-slate-500 uppercase tracking-[0.2em]">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse glow-green"></span>
                        <span>{{ __('panel.protocol_ready') }}</span>
                    </div>
                </div>
                <div class="flex items-center space-x-5">
                    <button onclick="editor.undo()" class="p-2 text-slate-400 hover:text-brand-500 transition-all rounded-lg hover:bg-brand-500/10" title="Undo"><i data-lucide="undo-2" class="w-4 h-4"></i></button>
                    <button onclick="editor.redo()" class="p-2 text-slate-400 hover:text-brand-500 transition-all rounded-lg hover:bg-brand-500/10" title="Redo"><i data-lucide="redo-2" class="w-4 h-4"></i></button>
                    <div class="w-px h-5 bg-slate-200 dark:bg-white/10 mx-1"></div>
                    <button onclick="toggleEditorSettings()" class="p-2 text-slate-400 hover:text-brand-500 transition-all rounded-lg hover:bg-brand-500/10" title="{{ __('panel.settings') }}"><i data-lucide="settings-2" class="w-4 h-4"></i></button>
                </div>
            </div>

            <div class="flex-1 relative">
                <div id="ace-editor" class="absolute inset-0"></div>
            </div>
            
            <div class="absolute bottom-8 right-12 px-5 py-2.5 rounded-2xl bg-slate-900/80 backdrop-blur-md border border-white/10 text-[9px] font-black text-slate-500 uppercase tracking-[0.3em] opacity-0 group-hover/editor:opacity-100 transition-opacity pointer-events-none shadow-2xl">
                {{ __('panel.ctrl_s_to_deploy') }}
            </div>
        </div>
    </div>
</div>

<form id="upload-form" action="{{ route('services.files.upload', $service->id) }}" method="POST" enctype="multipart/form-data" class="hidden">
    @csrf
    <input type="hidden" name="path" value="{{ $relativePath }}">
    <input type="file" id="upload-input" name="upload_file" onchange="document.getElementById('upload-form').submit()">
</form>

<!-- Modal for create file/dir -->
<div id="create-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 md:p-8 bg-slate-950/80 backdrop-blur-xl">
    <div class="glass dark:bg-dark-card w-full max-w-md rounded-[2.5rem] border border-white/10 p-8 shadow-2xl space-y-6 animate-in zoom-in duration-300">
        <div class="flex items-center space-x-4">
            <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl md:rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-500 border border-brand-500/20">
                <i id="create-modal-icon" data-lucide="plus-circle" class="w-7 h-7"></i>
            </div>
            <h3 class="text-2xl font-black text-white tracking-tight" id="create-modal-title">{{ __('panel.create') }}</h3>
        </div>
        <form id="create-form" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="path" value="{{ $relativePath }}">
            <div class="space-y-3">
                <label id="create-modal-label" class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('panel.metadata_name') }}</label>
                <input type="text" id="create-modal-input" name="filename" class="w-full bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-3.5 px-5 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all text-white font-bold text-sm shadow-sm" required>
            </div>
            <div class="flex items-center space-x-4 pt-4">
                <button type="button" onclick="hideCreateModal()" class="flex-1 py-3.5 rounded-2xl text-slate-400 hover:text-white transition-all text-[10px] font-black uppercase tracking-widest bg-white/5 border border-white/10">{{ __('panel.abort') }}</button>
                <button type="submit" class="flex-1 py-3.5 rounded-2xl bg-brand-500 text-white text-[10px] font-black uppercase tracking-widest shadow-lg shadow-brand-500/20 hover:bg-brand-600 transition-all border border-brand-500/50">{{ __('panel.confirm') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    let editor;
    let currentEditingFile = '';

    document.addEventListener('DOMContentLoaded', () => {
        editor = ace.edit("ace-editor");
        editor.setTheme("ace/theme/tomorrow_night");
        editor.session.setMode("ace/mode/text");
        editor.setOptions({
            fontSize: "14px",
            showPrintMargin: false,
            showGutter: true,
            highlightActiveLine: true,
            enableBasicAutocompletion: true,
            fontFamily: "JetBrains Mono, Fira Code, monospace"
        });

        // Add save shortcut
        editor.commands.addCommand({
            name: 'save',
            bindKey: {win: 'Ctrl-S',  mac: 'Command-S'},
            exec: function(editor) {
                saveFile();
            },
            readOnly: false
        });

        // Update line count on change
        editor.session.on('change', () => {
            const lines = editor.session.getLength();
            document.getElementById('editor-line-count').textContent = lines + ' {{ __('panel.lines') }}';
        });
    });

    function toggleAllCheckboxes() {
        const checkboxes = document.querySelectorAll('.file-checkbox');
        const allChecked = Array.from(checkboxes).every(c => c.checked);
        checkboxes.forEach(c => c.checked = !allChecked);
        updateActionState();
    }

    function updateActionState() {
        const selected = document.querySelectorAll('.file-checkbox:checked').length;
        const massActions = document.getElementById('mass-actions');
        if (selected > 0) {
            massActions.classList.remove('opacity-50', 'pointer-events-none');
        } else {
            massActions.classList.add('opacity-50', 'pointer-events-none');
        }
    }

    function massCompress() {
        const checkboxes = document.querySelectorAll('.file-checkbox:checked');
        const files = Array.from(checkboxes).map(c => c.value);
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('services.files.compress', $service->id) }}';
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        const pathInput = document.createElement('input');
        pathInput.type = 'hidden';
        pathInput.name = 'path';
        pathInput.value = '{{ $relativePath }}';
        form.appendChild(pathInput);

        files.forEach(f => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'files[]';
            input.value = f;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }

    function massExtract() {
        const checkboxes = document.querySelectorAll('.file-checkbox:checked');
        const files = Array.from(checkboxes).map(c => c.value);
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('services.files.mass_extract', $service->id) }}';
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        files.forEach(f => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'files[]';
            input.value = f;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }

    function extractZip(file) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('services.files.extract', $service->id) }}';
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'file';
        input.value = file;
        form.appendChild(input);

        document.body.appendChild(form);
        form.submit();
    }

    function confirmMassDelete() {
        if (!confirm('{{ __('panel.confirm_mass_delete') }}')) return;
        
        const checkboxes = document.querySelectorAll('.file-checkbox:checked');
        const files = Array.from(checkboxes).map(c => c.value);
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('services.files.mass_destroy', $service->id) }}';
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'DELETE';
        form.appendChild(method);

        files.forEach(f => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'files[]';
            input.value = f;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }

    function editFile(file) {
        currentEditingFile = file;
        
        fetch('{{ route('services.files.content', $service->id) }}?file=' + encodeURIComponent(file))
            .then(res => res.json())
            .then(data => {
                document.getElementById('editor-title-filename').textContent = data.filename;
                editor.setValue(data.content, -1);
                
                const lines = editor.session.getLength();
                document.getElementById('editor-line-count').textContent = lines + ' {{ __('panel.lines') }}';
                
                const ext = data.filename.split('.').pop().toLowerCase();
                let mode = "ace/mode/text";
                let badgeText = "Text";
                
                const modes = {
                    'js': { mode: 'ace/mode/javascript', label: 'JavaScript' },
                    'ts': { mode: 'ace/mode/typescript', label: 'TypeScript' },
                    'json': { mode: 'ace/mode/json', label: 'JSON' },
                    'php': { mode: 'ace/mode/php', label: 'PHP' },
                    'py': { mode: 'ace/mode/python', label: 'Python' },
                    'yml': { mode: 'ace/mode/yaml', label: 'YAML' },
                    'yaml': { mode: 'ace/mode/yaml', label: 'YAML' },
                    'html': { mode: 'ace/mode/html', label: 'HTML' },
                    'css': { mode: 'ace/mode/css', label: 'CSS' },
                    'sql': { mode: 'ace/mode/sql', label: 'SQL' },
                    'md': { mode: 'ace/mode/markdown', label: 'Markdown' },
                    'sh': { mode: 'ace/mode/sh', label: 'Shell' },
                    'env': { mode: 'ace/mode/sh', label: 'Env' }
                };

                if(modes[ext]) {
                    mode = modes[ext].mode;
                    badgeText = modes[ext].label;
                }

                editor.session.setMode(mode);
                document.getElementById('editor-language-badge').textContent = badgeText;

                document.getElementById('file-manager-view').classList.add('hidden');
                document.getElementById('file-editor-view').classList.remove('hidden');
                
                if(typeof lucide !== 'undefined') lucide.createIcons();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
    }

    function toggleEditorSettings() {
        const current = editor.getOption("showGutter");
        editor.setOptions({
            showGutter: !current,
            showLineNumbers: !current
        });
    }

    function closeEditor() {
        document.getElementById('file-editor-view').classList.add('hidden');
        document.getElementById('file-manager-view').classList.remove('hidden');
        if(typeof lucide !== 'undefined') lucide.createIcons();
    }

    function saveFile() {
        const content = editor.getValue();
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('services.files.save', $service->id) }}';
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        const fileInput = document.createElement('input');
        fileInput.type = 'hidden';
        fileInput.name = 'file';
        fileInput.value = currentEditingFile;
        form.appendChild(fileInput);

        const contentInput = document.createElement('input');
        contentInput.type = 'hidden';
        contentInput.name = 'content';
        contentInput.value = content;
        form.appendChild(contentInput);

        document.body.appendChild(form);
        form.submit();
    }

    function showCreateModal(type) {
        const modal = document.getElementById('create-modal');
        const title = document.getElementById('create-modal-title');
        const icon = document.getElementById('create-modal-icon');
        const input = document.getElementById('create-modal-input');
        const form = document.getElementById('create-form');

        if (type === 'file') {
            title.textContent = '{{ __('panel.new_file') }}';
            icon.setAttribute('data-lucide', 'file-plus');
            input.name = 'filename';
            form.action = '{{ route('services.files.create', $service->id) }}';
        } else {
            title.textContent = '{{ __('panel.new_folder') }}';
            icon.setAttribute('data-lucide', 'folder-plus');
            input.name = 'dirname';
            form.action = '{{ route('services.files.create_dir', $service->id) }}';
        }
        
        modal.classList.remove('hidden');
        if(typeof lucide !== 'undefined') lucide.createIcons();
        input.focus();
    }

    function hideCreateModal() {
        document.getElementById('create-modal').classList.add('hidden');
    }

    function deleteItem(file) {
        if (!confirm('{{ __('panel.confirm_delete_item') }}')) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('services.files.destroy', $service->id) }}';
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'DELETE';
        form.appendChild(method);

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'file';
        input.value = file;
        form.appendChild(input);

        document.body.appendChild(form);
        form.submit();
    }

    function renameItem(file, oldName) {
        const newName = prompt('{{ __('panel.enter_new_name') }}', oldName);
        if (!newName || newName === oldName) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('services.files.rename', $service->id) }}';
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        const fileInput = document.createElement('input');
        fileInput.type = 'hidden';
        fileInput.name = 'file';
        fileInput.value = file;
        form.appendChild(fileInput);

        const nameInput = document.createElement('input');
        nameInput.type = 'hidden';
        nameInput.name = 'new_name';
        nameInput.value = newName;
        form.appendChild(nameInput);

        document.body.appendChild(form);
        form.submit();
    }

    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>

<style>
    .ace_editor { border-radius: 0 0 3rem 3rem; font-family: 'JetBrains Mono', 'Fira Code', monospace !important; }
    .ace_gutter { background: #020617 !important; color: #334155 !important; }
    .ace_content { background: #020617 !important; }
    @keyframes fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.4s ease-out forwards; }
</style>
@endsection
