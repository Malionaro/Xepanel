@extends('layouts.app')

@section('header_title', 'File Manager')

@section('content')
<!-- Include Ace Editor -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.7/ace.js"></script>

<div class="space-y-8">
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

    @if(session('status'))
        <div class="bg-green-100 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 text-green-700 dark:text-green-400 p-4 rounded-2xl flex items-center space-x-3">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span class="text-sm font-medium">{{ session('status') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 text-red-700 dark:text-red-400 p-4 rounded-2xl flex items-center space-x-3">
            <i data-lucide="alert-octagon" class="w-5 h-5"></i>
            <span class="text-sm font-bold">{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">
        <!-- Sidebar / Directory Tree -->
        <div class="lg:col-span-1 space-y-6">
            <div id="drop-zone" class="card bg-white dark:bg-dark-card rounded-[2rem] border border-gray-200 dark:border-dark-border shadow-sm flex flex-col h-[700px] transition-all relative">
                <div id="drop-overlay" class="hidden absolute inset-0 bg-brand-500/10 border-2 border-dashed border-brand-500 rounded-[2rem] z-50 flex flex-col items-center justify-center pointer-events-none transition-all">
                    <div class="w-16 h-16 bg-brand-500 text-white rounded-full flex items-center justify-center shadow-xl mb-4">
                        <i data-lucide="upload-cloud" class="w-8 h-8"></i>
                    </div>
                    <span class="text-brand-600 dark:text-brand-400 font-black uppercase tracking-widest text-xs">Drop to upload</span>
                </div>

                <div class="p-6 border-b border-gray-100 dark:border-dark-border flex flex-col space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-400">Explorer</h3>
                            <div class="flex items-center">
                                <button type="button" onclick="createNewFile()" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-dark-hover text-brand-500 transition-colors" title="New File">
                                    <i data-lucide="file-plus" class="w-4 h-4"></i>
                                </button>
                                <button type="button" onclick="createNewDir()" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-dark-hover text-yellow-500 transition-colors" title="New Folder">
                                    <i data-lucide="folder-plus" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Bulk Actions Row -->
                    <div id="bulk-actions" class="hidden flex flex-wrap gap-2">
                        <button type="button" onclick="submitMassCompress()" class="flex items-center space-x-1 bg-brand-500 hover:bg-brand-600 text-white px-2 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all shadow-sm">
                            <i data-lucide="package" class="w-3 h-3"></i>
                            <span>Pack</span>
                        </button>
                        <button type="button" onclick="submitMassUnpack()" class="flex items-center space-x-1 bg-indigo-500 hover:bg-indigo-600 text-white px-2 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all shadow-sm">
                            <i data-lucide="package-open" class="w-3 h-3"></i>
                            <span>Unpack</span>
                        </button>
                        <button type="button" onclick="submitMassDelete()" class="flex items-center space-x-1 bg-red-500 hover:bg-red-600 text-white px-2 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all shadow-sm">
                            <i data-lucide="trash-2" class="w-3 h-3"></i>
                            <span>Delete</span>
                        </button>
                    </div>
                </div>

                <form id="mass-file-form" action="{{ route('services.files.mass_destroy', $service->id) }}" method="POST" class="flex-1 overflow-hidden flex flex-col">
                    @csrf
                    @method('DELETE')
                    <div class="flex-1 overflow-y-auto p-4 space-y-1 custom-scrollbar">
                        <!-- Up directory -->
                        <a href="{{ route('services.files', ['id' => $service->id, 'path' => dirname($relativePath)]) }}" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-gray-100 dark:hover:bg-dark-hover transition-colors group">
                            <i data-lucide="corner-left-up" class="w-4 h-4 text-brand-500"></i>
                            <span class="text-sm font-bold text-gray-400 group-hover:text-brand-500">..</span>
                        </a>

                        @foreach($directories as $dir)
                            <div class="flex items-center p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-dark-hover transition-all group">
                                <input type="checkbox" name="files[]" value="{{ $relativePath . '/' . basename($dir) }}" onchange="updateMassActionBtnVisibility()" class="w-4 h-4 rounded-lg border-gray-300 dark:border-dark-border text-brand-500 focus:ring-brand-500 transition-all cursor-pointer mr-3">
                                <a href="{{ route('services.files', ['id' => $service->id, 'path' => $relativePath . '/' . basename($dir)]) }}" class="flex-1 flex items-center space-x-3 min-w-0">
                                    <i data-lucide="folder" class="w-4 h-4 text-yellow-500 fill-yellow-500/20 shrink-0"></i>
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300 truncate group-hover:text-brand-500 transition-colors">{{ basename($dir) }}</span>
                                </a>
                                <button type="button" onclick="renameItem('{{ $relativePath . '/' . basename($dir) }}', '{{ basename($dir) }}')" class="opacity-0 group-hover:opacity-100 p-1.5 text-gray-400 hover:text-brand-500 transition-all" title="Rename">
                                    <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        @endforeach

                        @foreach($files as $file)
                            @php $isZip = strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'zip'; @endphp
                            <div class="flex items-center p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-dark-hover transition-all group">
                                <input type="checkbox" name="files[]" value="{{ $relativePath . '/' . basename($file) }}" onchange="updateMassActionBtnVisibility()" class="w-4 h-4 rounded-lg border-gray-300 dark:border-dark-border text-brand-500 focus:ring-brand-500 transition-all cursor-pointer mr-3">
                                <button type="button" onclick="loadFile('{{ $relativePath . '/' . basename($file) }}')" class="flex-1 flex items-center space-x-3 min-w-0 text-left">
                                    <i data-lucide="{{ $isZip ? 'file-archive' : 'file-text' }}" class="w-4 h-4 {{ $isZip ? 'text-brand-500' : 'text-gray-400' }} shrink-0"></i>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400 truncate group-hover:text-brand-500 transition-colors">{{ basename($file) }}</span>
                                </button>
                                <div class="flex items-center opacity-0 group-hover:opacity-100 transition-all">
                                    @if($isZip)
                                        <button type="button" onclick="extractZip('{{ $relativePath . '/' . basename($file) }}')" class="p-1.5 text-indigo-500 hover:text-indigo-600" title="Extract ZIP">
                                            <i data-lucide="package-open" class="w-3.5 h-3.5"></i>
                                        </button>
                                    @endif
                                    <button type="button" onclick="renameItem('{{ $relativePath . '/' . basename($file) }}', '{{ basename($file) }}')" class="p-1.5 text-gray-400 hover:text-brand-500" title="Rename">
                                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </form>
                
                <!-- Upload Footer -->
                <div class="p-6 bg-gray-50 dark:bg-[#1c2128] border-t border-gray-100 dark:border-dark-border rounded-b-[2rem]">
                    <form action="{{ route('services.files.upload', $service->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <input type="hidden" name="path" value="{{ $relativePath }}">
                        <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-300 dark:border-dark-border rounded-2xl cursor-pointer hover:border-brand-500/50 hover:bg-white dark:hover:bg-dark-bg transition-all">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <i data-lucide="plus" class="w-6 h-6 text-gray-400 mb-1"></i>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Select File</p>
                            </div>
                            <input type="file" name="upload_file" class="hidden" onchange="this.form.submit()" required>
                        </label>
                    </form>
                </div>
            </div>
        </div>

        <!-- Editor -->
        <div class="lg:col-span-3">
            <div id="editor-container" class="hidden flex flex-col h-[700px] group/editor">
                <div class="card bg-white dark:bg-dark-card rounded-t-[2rem] border border-gray-200 dark:border-dark-border px-8 py-4 flex justify-between items-center transition-all group-hover/editor:border-brand-500/30">
                    <div class="flex items-center space-x-4">
                        <div class="w-8 h-8 rounded-xl bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center text-brand-500">
                            <i data-lucide="file-code" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span id="current-filename" class="text-sm font-bold text-gray-900 dark:text-white"></span>
                            <p id="current-mode" class="text-[10px] font-black text-gray-400 uppercase tracking-widest"></p>
                        </div>
                    </div>
                    <button onclick="saveFile()" class="flex items-center space-x-2 bg-brand-500 hover:bg-brand-600 text-white px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-brand-500/25 transition-all active:scale-95">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Save Changes</span>
                    </button>
                </div>
                <div id="file-editor" class="flex-1 bg-[#0d1117] border-x border-b border-gray-200 dark:border-dark-border rounded-b-[2rem] text-sm shadow-xl transition-all group-hover/editor:border-brand-500/30"></div>
            </div>
            
            <div id="editor-placeholder" class="card bg-white dark:bg-dark-card rounded-[2rem] border border-gray-200 dark:border-dark-border h-[700px] flex flex-col items-center justify-center text-center space-y-4 shadow-sm">
                <div class="w-20 h-20 bg-gray-50 dark:bg-dark-bg rounded-full flex items-center justify-center text-4xl grayscale opacity-50 shadow-inner border border-gray-100 dark:border-dark-border">📄</div>
                <div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white">Select a file</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-[200px]">Click on a file in the explorer to start editing its content with live syntax highlighting.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentFile = null;
    
    // Initialize Ace Editor
    const editor = ace.edit("file-editor");
    editor.setTheme(document.documentElement.classList.contains('dark') ? "ace/theme/one_dark" : "ace/theme/chrome");
    editor.setShowPrintMargin(false);
    editor.setOptions({
        fontSize: "13px",
        enableBasicAutocompletion: true,
        enableLiveAutocompletion: true
    });
    
    // Listen for theme changes
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'class') {
                const isDark = document.documentElement.classList.contains('dark');
                editor.setTheme(isDark ? "ace/theme/one_dark" : "ace/theme/chrome");
            }
        });
    });
    observer.observe(document.documentElement, { attributes: true });

    function getModeForFile(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        const map = {
            'js': 'javascript', 'json': 'json', 'html': 'html', 'css': 'css',
            'php': 'php', 'py': 'python', 'sh': 'sh', 'yml': 'yaml',
            'yaml': 'yaml', 'md': 'markdown', 'xml': 'xml', 'env': 'ini', 'conf': 'ini'
        };
        return map[ext] || 'text';
    }

    function loadFile(filePath) {
        currentFile = filePath;
        document.getElementById('editor-placeholder').innerHTML = '<div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-500"></div>';

        fetch(`{{ route('services.files.content', $service->id) }}?file=${encodeURIComponent(filePath)}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('editor-container').classList.remove('hidden');
                document.getElementById('editor-placeholder').classList.add('hidden');
                document.getElementById('current-filename').textContent = data.filename;
                const mode = getModeForFile(data.filename);
                document.getElementById('current-mode').textContent = mode;
                editor.session.setValue(data.content);
                editor.session.setMode(`ace/mode/${mode}`);
                if(typeof lucide !== 'undefined') lucide.createIcons();
            });
    }

    function saveFile() {
        if (!currentFile) return;
        const content = editor.session.getValue();
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('services.files.save', $service->id) }}';
        const csrf = document.createElement('input');
        csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
        const fileInput = document.createElement('input');
        fileInput.type = 'hidden'; fileInput.name = 'file'; fileInput.value = currentFile;
        const contentInput = document.createElement('input');
        contentInput.type = 'hidden'; contentInput.name = 'content'; contentInput.value = content;
        form.appendChild(csrf); form.appendChild(fileInput); form.appendChild(contentInput);
        document.body.appendChild(form); form.submit();
    }

    function updateMassActionBtnVisibility() {
        const checkboxes = document.querySelectorAll('input[name="files[]"]:checked');
        const bulkDiv = document.getElementById('bulk-actions');
        if (checkboxes.length > 0) {
            bulkDiv.classList.remove('hidden');
        } else {
            bulkDiv.classList.add('hidden');
        }
    }

    function submitMassDelete() {
        if (confirm('DANGER: Delete all selected items?')) {
            const form = document.getElementById('mass-file-form');
            form.action = '{{ route('services.files.mass_destroy', $service->id) }}';
            const methodInput = form.querySelector('input[name="_method"]');
            if (methodInput) methodInput.value = 'DELETE';
            form.submit();
        }
    }

    function submitMassCompress() {
        if (confirm('Pack all selected items into a ZIP?')) {
            const form = document.getElementById('mass-file-form');
            form.action = '{{ route('services.files.compress', $service->id) }}';
            const methodInput = form.querySelector('input[name="_method"]');
            if (methodInput) methodInput.value = 'POST';
            form.submit();
        }
    }

    function submitMassUnpack() {
        if (confirm('Unpack all selected ZIP archives?')) {
            const form = document.getElementById('mass-file-form');
            form.action = '{{ route('services.files.mass_extract', $service->id) }}';
            const methodInput = form.querySelector('input[name="_method"]');
            if (methodInput) methodInput.value = 'POST';
            form.submit();
        }
    }

    function renameItem(filePath, currentName) {
        const newName = prompt('Enter new name:', currentName);
        if (newName && newName !== currentName) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('services.files.rename', $service->id) }}';
            const csrf = document.createElement('input');
            csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
            const fileInput = document.createElement('input');
            fileInput.type = 'hidden'; fileInput.name = 'file'; fileInput.value = filePath;
            const nameInput = document.createElement('input');
            nameInput.type = 'hidden'; nameInput.name = 'new_name'; nameInput.value = newName;
            form.appendChild(csrf); form.appendChild(fileInput); form.appendChild(nameInput);
            document.body.appendChild(form); form.submit();
        }
    }

    function createNewFile() {
        const filename = prompt('Enter name for the new file:');
        if (filename) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('services.files.create', $service->id) }}';
            const csrf = document.createElement('input');
            csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
            const pathInput = document.createElement('input');
            pathInput.type = 'hidden'; pathInput.name = 'path'; pathInput.value = '{{ $relativePath }}';
            const nameInput = document.createElement('input');
            nameInput.type = 'hidden'; nameInput.name = 'filename'; nameInput.value = filename;
            form.appendChild(csrf); form.appendChild(pathInput); form.appendChild(nameInput);
            document.body.appendChild(form); form.submit();
        }
    }

    function createNewDir() {
        const dirname = prompt('Enter name for the new folder:');
        if (dirname) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('services.files.create_dir', $service->id) }}';
            const csrf = document.createElement('input');
            csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
            const pathInput = document.createElement('input');
            pathInput.type = 'hidden'; pathInput.name = 'path'; pathInput.value = '{{ $relativePath }}';
            const nameInput = document.createElement('input');
            nameInput.type = 'hidden'; nameInput.name = 'dirname'; nameInput.value = dirname;
            form.appendChild(csrf); form.appendChild(pathInput); form.appendChild(nameInput);
            document.body.appendChild(form); form.submit();
        }
    }

    function extractZip(filePath) {
        if (confirm('Extract this archive in the current directory?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('services.files.extract', $service->id) }}';
            const csrf = document.createElement('input');
            csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
            const fileInput = document.createElement('input');
            fileInput.type = 'hidden'; fileInput.name = 'file'; fileInput.value = filePath;
            form.appendChild(csrf); form.appendChild(fileInput);
            document.body.appendChild(form); form.submit();
        }
    }

    // Drag & Drop
    const dropZone = document.getElementById('drop-zone');
    const dropOverlay = document.getElementById('drop-overlay');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(name => {
        dropZone.addEventListener(name, e => { e.preventDefault(); e.stopPropagation(); }, false);
    });

    ['dragenter', 'dragover'].forEach(name => {
        dropZone.addEventListener(name, () => dropOverlay.classList.remove('hidden'), false);
    });

    ['dragleave', 'drop'].forEach(name => {
        dropZone.addEventListener(name, () => dropOverlay.classList.add('hidden'), false);
    });

    dropZone.addEventListener('drop', e => {
        const files = e.dataTransfer.files;
        if (files.length === 0) return;
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('path', '{{ $relativePath }}');
        for (let i = 0; i < files.length; i++) formData.append('files[]', files[i]);
        dropOverlay.classList.remove('hidden');
        dropOverlay.innerHTML = '<div class="animate-spin rounded-full h-10 w-10 border-b-2 border-white mb-4"></div><span class="text-white font-black text-xs uppercase tracking-widest">Uploading...</span>';
        fetch('{{ route('services.files.multi_upload', $service->id) }}', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => data.success ? location.reload() : alert('Error during upload'))
        .catch(() => alert('Upload failed'));
    }, false);

    if(typeof lucide !== 'undefined') lucide.createIcons();
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #30363d; border-radius: 10px; }
    .dark .ace_editor { background-color: #0d1117 !important; color: #c9d1d9 !important; }
    .ace_gutter { background: #161b22 !important; color: #484f58 !important; border-right: 1px solid #30363d !important; }
    .ace_active-line { background: rgba(255,255,255,0.03) !important; }
</style>
@endsection
