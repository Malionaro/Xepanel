@extends('layouts.app')

@section('header_title', 'File Manager')

@section('content')
<!-- Include Ace Editor -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.7/ace.js"></script>

<div class="space-y-10">
    <!-- Breadcrumbs & Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
        <div class="flex items-center p-1.5 glass dark:bg-white/5 border-slate-200 dark:border-white/10 rounded-2xl shadow-sm">
            <a href="{{ route('services.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 text-slate-500 dark:text-slate-400 hover:text-brand-500 transition-all group">
                <i data-lucide="server" class="w-4 h-4 transition-transform group-hover:scale-110"></i>
                <span class="text-[10px] font-black uppercase tracking-widest">My Services</span>
            </a>
            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 dark:text-slate-600 mx-1"></i>
            <a href="{{ route('services.show', $service->id) }}" class="flex items-center space-x-2 px-4 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 text-slate-500 dark:text-slate-400 hover:text-brand-500 transition-all group">
                <i data-lucide="terminal" class="w-4 h-4 transition-transform group-hover:scale-110"></i>
                <span class="text-[10px] font-black uppercase tracking-widest">{{ $service->name }}</span>
            </a>
            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 dark:text-slate-600 mx-1"></i>
            <div class="flex items-center space-x-2 px-4 py-2 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-600 dark:text-brand-400">
                <i data-lucide="folder-open" class="w-4 h-4"></i>
                <span class="text-[10px] font-black uppercase tracking-widest">Files</span>
            </div>
        </div>
        
        <div class="flex items-center space-x-4">
            <a href="{{ route('services.show', $service->id) }}" class="flex items-center space-x-3 px-6 py-3 rounded-2xl glass dark:bg-dark-card border-slate-200 dark:border-dark-border text-slate-600 dark:text-slate-300 text-xs font-black uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-800 transition-all hover:-translate-x-1">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back</span>
            </a>
        </div>
    </div>

    <!-- Action Bar & Mass Operations -->
    <div class="flex flex-wrap items-center justify-between gap-6 glass dark:bg-white/5 p-6 rounded-[2.5rem] border border-slate-200 dark:border-white/5 shadow-xl">
        <div class="flex items-center space-x-4">
            <button onclick="toggleAllCheckboxes()" class="p-3 rounded-xl bg-slate-100 dark:bg-white/5 text-slate-500 hover:text-brand-500 transition-all border border-slate-200 dark:border-white/10" title="Select All">
                <i data-lucide="check-square" class="w-5 h-5"></i>
            </button>
            <div class="h-8 w-px bg-slate-200 dark:bg-white/10 mx-2"></div>
            <div id="mass-actions" class="flex items-center space-x-3 opacity-50 pointer-events-none transition-all duration-300">
                <button onclick="confirmMassDelete()" class="flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-red-500/10 text-red-500 border border-red-500/20 hover:bg-red-500 hover:text-white transition-all text-[10px] font-black uppercase tracking-widest">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    <span>Delete</span>
                </button>
                <button onclick="massCompress()" class="flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-brand-500/10 text-brand-500 border border-brand-500/20 hover:bg-brand-500 hover:text-white transition-all text-[10px] font-black uppercase tracking-widest">
                    <i data-lucide="archive" class="w-4 h-4"></i>
                    <span>Archive</span>
                </button>
            </div>
        </div>
        
        <div class="flex items-center space-x-3">
            <button onclick="document.getElementById('upload-input').click()" class="flex items-center space-x-3 px-6 py-3 rounded-2xl glass dark:bg-brand-500/10 border border-slate-200 dark:border-brand-500/30 text-slate-600 dark:text-brand-400 text-xs font-black uppercase tracking-widest hover:bg-brand-500 hover:text-white transition-all">
                <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                <span>Upload</span>
            </button>
            <button onclick="showCreateModal('file')" class="flex items-center space-x-3 px-6 py-3 rounded-2xl bg-brand-500 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-brand-500/25 hover:bg-brand-600 transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>New File</span>
            </button>
            <button onclick="showCreateModal('directory')" class="flex items-center space-x-3 px-6 py-3 rounded-2xl glass dark:bg-dark-card border-slate-200 dark:border-dark-border text-slate-600 dark:text-slate-300 text-xs font-black uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                <i data-lucide="folder-plus" class="w-4 h-4"></i>
                <span>Folder</span>
            </button>
        </div>
    </div>

    <form id="upload-form" action="{{ route('services.files.upload', $service->id) }}" method="POST" enctype="multipart/form-data" class="hidden">
        @csrf
        <input type="hidden" name="path" value="{{ $relativePath }}">
        <input type="file" id="upload-input" name="upload_file" onchange="document.getElementById('upload-form').submit()">
    </form>

    @if(session('status'))
        <div class="bg-green-500/10 border border-green-500/20 text-green-600 dark:text-green-400 p-5 rounded-3xl flex items-center space-x-4 animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="w-10 h-10 rounded-xl bg-green-500/20 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
            <span class="text-sm font-bold">{{ session('status') }}</span>
        </div>
    @endif

    <!-- File List Table -->
    <div class="glass dark:bg-dark-card rounded-[3rem] border border-slate-200 dark:border-white/5 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="files-table">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-white/5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-100 dark:border-white/5">
                        <th class="p-6 w-12 text-center">#</th>
                        <th class="p-6">Metadata Name</th>
                        <th class="p-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @if($relativePath !== '')
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="p-6"></td>
                            <td class="p-6" colspan="2">
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
                            <td class="p-6 text-center">
                                <input type="checkbox" name="selected_files[]" value="{{ $relativePath ? $relativePath . '/' . $dirName : $dirName }}" onchange="updateActionState()" class="file-checkbox w-5 h-5 rounded-lg border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-brand-500 focus:ring-brand-500/20 transition-all cursor-pointer">
                            </td>
                            <td class="p-6">
                                <a href="?path={{ $relativePath ? $relativePath . '/' . $dirName : $dirName }}" class="flex items-center space-x-4">
                                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500 border border-blue-500/20 group-hover:scale-110 transition-transform shadow-lg shadow-blue-500/5">
                                        <i data-lucide="folder" class="w-5 h-5 fill-current opacity-20"></i>
                                    </div>
                                    <span class="font-bold text-slate-900 dark:text-white tracking-tight">{{ $dirName }}</span>
                                </a>
                            </td>
                            <td class="p-6 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <button onclick="renameItem('{{ $relativePath ? $relativePath . '/' . $dirName : $dirName }}', '{{ $dirName }}')" class="p-2 text-slate-400 hover:text-brand-500 transition-all" title="Rename"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                                    <button onclick="deleteItem('{{ $relativePath ? $relativePath . '/' . $dirName : $dirName }}')" class="p-2 text-slate-400 hover:text-red-500 transition-all" title="Delete"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
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
                            <td class="p-6 text-center">
                                <input type="checkbox" name="selected_files[]" value="{{ $relativePath ? $relativePath . '/' . $fileName : $fileName }}" onchange="updateActionState()" class="file-checkbox w-5 h-5 rounded-lg border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-brand-500 focus:ring-brand-500/20 transition-all cursor-pointer">
                            </td>
                            <td class="p-6">
                                <div onclick="editFile('{{ $relativePath ? $relativePath . '/' . $fileName : $fileName }}')" class="flex items-center space-x-4 cursor-pointer">
                                    <div class="w-10 h-10 rounded-xl {{ $isZip ? 'bg-amber-500/10 text-amber-500 border-amber-500/20 shadow-amber-500/5' : 'bg-slate-500/10 text-slate-500 border-slate-500/20 shadow-slate-500/5' }} flex items-center justify-center border group-hover:scale-110 transition-transform">
                                        <i data-lucide="{{ $isZip ? 'archive' : 'file-text' }}" class="w-5 h-5"></i>
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-bold text-slate-900 dark:text-white tracking-tight truncate max-w-md">{{ $fileName }}</span>
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">{{ number_format(File::size($file) / 1024, 2) }} KB</span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-6 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    @if($isZip)
                                        <button onclick="extractZip('{{ $relativePath ? $relativePath . '/' . $fileName : $fileName }}')" class="p-2 text-emerald-500 hover:bg-emerald-500/10 rounded-lg transition-all" title="Extract"><i data-lucide="unarchive" class="w-4 h-4"></i></button>
                                    @endif
                                    <button onclick="renameItem('{{ $relativePath ? $relativePath . '/' . $fileName : $fileName }}', '{{ $fileName }}')" class="p-2 text-slate-400 hover:text-brand-500 transition-all" title="Rename"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                                    <button onclick="deleteItem('{{ $relativePath ? $relativePath . '/' . $fileName : $fileName }}')" class="p-2 text-slate-400 hover:text-red-500 transition-all" title="Delete"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for file editing -->
<div id="editor-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-8 bg-slate-950/80 backdrop-blur-xl transition-all duration-500 opacity-0">
    <div class="glass dark:bg-dark-card w-full max-w-6xl rounded-[4rem] border border-white/10 shadow-2xl overflow-hidden flex flex-col max-h-[90vh] scale-95 transition-transform duration-500" id="editor-content">
        <div class="px-12 py-8 border-b border-white/5 flex justify-between items-center bg-white/5">
            <div class="flex items-center space-x-6">
                <div class="w-12 h-12 rounded-2xl bg-brand-500/20 flex items-center justify-center text-brand-500 border border-brand-500/20 shadow-lg shadow-brand-500/5">
                    <i data-lucide="file-edit" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-white uppercase tracking-tight" id="modal-filename">Editor</h3>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Code Synthesis Engine</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <button onclick="closeEditor()" class="px-6 py-3 rounded-xl text-slate-400 hover:text-white transition-all text-[10px] font-black uppercase tracking-widest">Cancel</button>
                <button onclick="saveFile()" class="px-8 py-3 rounded-xl bg-brand-500 text-white text-[10px] font-black uppercase tracking-widest shadow-lg shadow-brand-500/20 hover:bg-brand-600 transition-all">Deploy Changes</button>
            </div>
        </div>
        <div class="flex-1 relative">
            <div id="ace-editor" class="absolute inset-0"></div>
        </div>
    </div>
</div>

<!-- Modal for create file/dir -->
<div id="create-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-8 bg-slate-950/80 backdrop-blur-xl">
    <div class="glass dark:bg-dark-card w-full max-w-md rounded-[3rem] border border-white/10 p-10 shadow-2xl space-y-8 animate-in zoom-in duration-300">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-500 border border-brand-500/20">
                <i id="create-modal-icon" data-lucide="plus-circle" class="w-7 h-7"></i>
            </div>
            <h3 class="text-2xl font-black text-white tracking-tight" id="create-modal-title">Create</h3>
        </div>
        <form id="create-form" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="path" value="{{ $relativePath }}">
            <div class="space-y-3">
                <label id="create-modal-label" class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Metadata Name</label>
                <input type="text" id="create-modal-input" name="filename" class="w-full bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all text-white font-bold text-sm shadow-sm" required>
            </div>
            <div class="flex items-center space-x-4 pt-4">
                <button type="button" onclick="hideCreateModal()" class="flex-1 py-4 rounded-2xl text-slate-400 hover:text-white transition-all text-[10px] font-black uppercase tracking-widest">Abort</button>
                <button type="submit" class="flex-1 py-4 rounded-2xl bg-brand-500 text-white text-[10px] font-black uppercase tracking-widest shadow-lg shadow-brand-500/20 hover:bg-brand-600 transition-all">Confirm</button>
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
            fontSize: "13px",
            showPrintMargin: false,
            showGutter: true,
            highlightActiveLine: true,
            enableBasicAutocompletion: true
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
        if (!confirm('CRITICAL: Delete all selected items permanently?')) return;
        
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
                document.getElementById('modal-filename').textContent = data.filename;
                editor.setValue(data.content, -1);
                
                const ext = data.filename.split('.').pop();
                let mode = "ace/mode/text";
                if(ext === 'js') mode = "ace/mode/javascript";
                if(ext === 'json') mode = "ace/mode/json";
                if(ext === 'php') mode = "ace/mode/php";
                if(ext === 'py') mode = "ace/mode/python";
                if(ext === 'yml' || ext === 'yaml') mode = "ace/mode/yaml";
                if(ext === 'html') mode = "ace/mode/html";
                if(ext === 'css') mode = "ace/mode/css";
                editor.session.setMode(mode);

                const modal = document.getElementById('editor-modal');
                const content = document.getElementById('editor-content');
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.classList.add('opacity-100');
                    content.classList.add('scale-100');
                    content.classList.remove('scale-95');
                }, 10);
            });
    }

    function closeEditor() {
        const modal = document.getElementById('editor-modal');
        const content = document.getElementById('editor-content');
        modal.classList.remove('opacity-100');
        content.classList.add('scale-95');
        content.classList.remove('scale-100');
        setTimeout(() => modal.classList.add('hidden'), 500);
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
            title.textContent = 'New File';
            icon.setAttribute('data-lucide', 'file-plus');
            input.name = 'filename';
            form.action = '{{ route('services.files.create', $service->id) }}';
        } else {
            title.textContent = 'New Folder';
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
        if (!confirm('Permanently delete this item?')) return;
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
        const newName = prompt('Enter new name:', oldName);
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
    .ace_editor { border-radius: 0 0 4rem 4rem; font-family: 'JetBrains Mono', 'Fira Code', monospace !important; }
    .ace_gutter { background: #020617 !important; color: #334155 !important; }
    .ace_content { background: #020617 !important; }
</style>
@endsection
