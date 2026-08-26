@extends('layouts.app')

@section('title', $project->name . ' Files - GEO CORP.')

@section('content')
<style>
    .project-files {
        max-width: 1150px;
        margin: 0 auto;
        color: #172033;
        font-family: 'Montserrat', sans-serif;
    }

    .files-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .files-heading {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 12px;
    }

    .files-back {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 auto;
        place-items: center;
        border: 1px solid #dfe3ea;
        border-radius: 9px;
        background: #fff;
        color: #475569;
        text-decoration: none;
        transition: all .15s;
    }
    .files-back:hover { background: #f1f5f9; color: #0f766e; }

    .files-title {
        margin: 0;
        overflow: hidden;
        font-size: 24px;
        font-weight: 900;
        line-height: 1.2;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .files-count {
        margin: 4px 0 0;
        color: #778196;
        font-size: 12px;
        font-weight: 600;
    }

    .header-btns {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .btn-action {
        display: inline-flex;
        min-height: 40px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 9px 16px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        border: none;
        transition: all .15s;
    }

    .btn-create-folder {
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .btn-create-folder:hover { background: #dcfce7; }

    .files-add {
        background: #0f766e;
        color: #fff;
    }
    .files-add:hover { background: #0d655e; }

    /* ── FOLDERS SECTION & DROP TARGETS ──────────────────── */
    .folders-section {
        margin-bottom: 20px;
        background: #fff;
        border: 1px solid #dfe3ea;
        border-radius: 14px;
        padding: 16px 20px;
    }

    .folders-section-title {
        font-size: 11px;
        font-weight: 850;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .folders-grid {
        display: flex;
        gap: 12px;
        overflow-x: auto;
        padding-bottom: 4px;
        scroll-behavior: smooth;
    }

    .folder-card {
        min-width: 190px;
        max-width: 240px;
        flex: 0 0 auto;
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
        border-radius: 11px;
        padding: 10px 10px 10px 14px;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        transition: all .2s ease;
        position: relative;
        user-select: none;
    }

    .folder-card:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
        transform: translateY(-1px);
    }

    .folder-card.active {
        background: #ecfdf8;
        border-color: #0f9f8f;
        border-style: solid;
        box-shadow: 0 2px 8px rgba(15, 159, 143, 0.15);
    }

    .folder-card.drag-over {
        background: #dcfce7 !important;
        border-color: #16a34a !important;
        border-style: solid !important;
        transform: scale(1.04);
        box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.25);
    }

    .folder-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 17px;
    }

    .folder-info {
        min-width: 0;
        flex: 1;
    }

    .folder-name {
        font-size: 12px;
        font-weight: 800;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .folder-docs-count {
        font-size: 10px;
        color: #64748b;
        font-weight: 600;
    }

    .drag-hint {
        font-size: 9px;
        color: #15803d;
        font-weight: 800;
        display: none;
    }

    .folder-card.drag-over .drag-hint { display: block; }
    .folder-card.drag-over .folder-docs-count { display: none; }

    .folder-actions-wrap {
        display: none;
        gap: 3px;
        flex-shrink: 0;
    }
    .folder-card:hover .folder-actions-wrap { display: flex; }

    .folder-action-btn {
        background: none;
        border: none;
        font-size: 11px;
        cursor: pointer;
        padding: 3px;
        border-radius: 4px;
        transition: background .15s;
    }
    .folder-action-btn:hover { background: #e2e8f0; }
    .folder-action-btn.danger:hover { background: #fee2e2; }

    /* Active view & search bar */
    .view-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
        gap: 16px;
        flex-wrap: wrap;
    }

    .view-title {
        font-size: 14px;
        font-weight: 850;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .search-box-wrap {
        position: relative;
        width: 260px;
        max-width: 100%;
    }

    .search-input {
        width: 100%;
        padding: 8px 12px 8px 34px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        font-size: 12px;
        font-family: inherit;
        outline: none;
        background: #fff;
        transition: border-color .15s, box-shadow .15s;
    }

    .search-input:focus {
        border-color: #0f9f8f;
        box-shadow: 0 0 0 3px rgba(15, 159, 143, 0.15);
    }

    .search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 14px;
        pointer-events: none;
    }

    /* ── FILES LIST TABLE ────────────────────────────────── */
    .files-list {
        overflow: hidden;
        border: 1px solid #dfe3ea;
        border-radius: 13px;
        background: #fff;
    }

    .files-row {
        display: grid;
        grid-template-columns: minmax(230px, 1.5fr) minmax(140px, 1fr) 100px 100px 135px 120px auto;
        gap: 12px;
        align-items: center;
        padding: 14px 18px;
        border-bottom: 1px solid #edf0f4;
        text-align: left;
        transition: background .15s ease, opacity .2s ease, transform .15s ease;
        cursor: grab;
    }

    .files-row:active { cursor: grabbing; }
    .files-row:last-child { border-bottom: 0; }

    .files-row.dragging {
        opacity: 0.35;
        background: #f1f5f9;
        transform: scale(0.99);
    }

    .files-row.header {
        padding-top: 11px;
        padding-bottom: 11px;
        background: #f8fafc;
        color: #697386;
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .06em;
        text-transform: uppercase;
        cursor: default;
    }

    .file-main,
    .file-cell {
        min-width: 0;
        justify-self: stretch;
        text-align: left;
    }

    .file-title {
        display: block;
        overflow: hidden;
        color: #172033;
        font-size: 12px;
        font-weight: 850;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .file-subtitle {
        display: block;
        margin-top: 3px;
        overflow: hidden;
        color: #8490a3;
        font-size: 9px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .file-folder-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 750;
        background: #f1f5f9;
        color: #475569;
    }

    .file-value {
        color: #475569;
        font-size: 10px;
        font-weight: 650;
    }

    .file-status {
        display: inline-flex;
        padding: 4px 8px;
        border-radius: 999px;
        background: #e2e8f0;
        color: #475569;
        font-size: 8px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .file-status.active { background: #dcfce7; color: #15803d; }
    .file-status.expired { background: #fee2e2; color: #b91c1c; }
    .expiry-soon { color: #b45309; }
    .expiry-overdue { color: #b91c1c; }

    .file-actions {
        display: flex;
        justify-content: flex-start;
        gap: 5px;
        text-align: left;
    }

    .file-action {
        display: inline-flex;
        min-height: 30px;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        border: 1px solid #dfe3ea;
        border-radius: 8px;
        background: #fff;
        color: #475569;
        font-size: 9px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
    }

    .file-action.primary {
        border-color: #1d4ed8;
        background: #1d4ed8;
        color: #fff;
    }

    .file-action.danger {
        border-color: #fca5a5;
        background: #fff5f5;
        color: #dc2626;
    }
    .file-action.danger:hover {
        background: #dc2626;
        color: #fff;
        border-color: #dc2626;
    }

    .files-empty {
        padding: 50px 20px;
        color: #778196;
        text-align: center;
    }

    .files-empty h2 {
        margin: 0 0 7px;
        color: #172033;
        font-size: 17px;
    }

    .files-empty p {
        margin: 0 0 16px;
        font-size: 11px;
    }

    /* Toast */
    .fx-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        padding: 12px 20px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 800;
        color: #fff;
        z-index: 9999;
        display: none;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        animation: toast-in .2s ease;
    }
    .fx-toast.success { background: #059669; }
    .fx-toast.error { background: #dc2626; }
    @keyframes toast-in { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }

    /* Modals */
    .fx-modal-bg {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .6);
        backdrop-filter: blur(3px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    .fx-modal-bg.open { display: flex; }
    .fx-modal {
        background: #fff;
        border-radius: 16px;
        padding: 28px;
        max-width: 440px;
        width: 90%;
        box-shadow: 0 24px 60px rgba(0,0,0,.2);
    }
    .fx-modal-title { font-size: 18px; font-weight: 850; color: #0f172a; margin-bottom: 18px; }
    .fm-group { margin-bottom: 16px; }
    .fm-group label { display: block; font-size: 12px; font-weight: 750; color: #334155; margin-bottom: 6px; }
    .fm-group input, .fm-group textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 13px;
        font-family: inherit;
        outline: none;
    }
    .color-row { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 6px; }
    .cswatch { width: 28px; height: 28px; border-radius: 7px; cursor: pointer; border: 2px solid transparent; }
    .cswatch.sel { border-color: #0f172a; transform: scale(1.1); }
    .fm-footer { display: flex; gap: 10px; justify-content: flex-end; margin-top: 22px; }
</style>

<div class="project-files">
    <header class="files-header">
        <div class="files-heading">
            <a href="{{ route('documents.index') }}" class="files-back" aria-label="Back to Document Tracker">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                    <path d="m15 18-6-6 6-6"></path>
                </svg>
            </a>
            <div style="min-width:0;">
                <h1 class="files-title">{{ $project->name }} Files</h1>
                <p class="files-count" id="total-docs-count">{{ $documents->count() }} {{ Str::plural('document', $documents->count()) }} total</p>
            </div>
        </div>

        <div class="header-btns">
            <button type="button" class="btn-action btn-create-folder" onclick="openCreateFolderModal()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
                + New Folder
            </button>

            <a href="{{ route('documents.create', ['project_id' => $project->id]) }}" id="add-doc-btn" class="btn-action files-add">
                <span aria-hidden="true">+</span>
                Add document
            </a>
        </div>
    </header>

    {{-- FOLDERS & DROP TARGETS --}}
    <section class="folders-section">
        <div class="folders-section-title">
            <span>📁 Folders & Locations</span>
            <span style="font-size:10px;color:#0f766e;font-weight:700;">💡 Drag any file below directly onto a folder card to move it into that folder!</span>
        </div>

        <div class="folders-grid">
            {{-- Main Directory (Unassigned) --}}
            <div class="folder-card active" id="fcard-none" onclick="filterFolder('none')"
                 ondragover="onDragOverFolder(event, this)"
                 ondragleave="onDragLeaveFolder(this)"
                 ondrop="onDropToFolder(event, null)">
                <div class="folder-icon" style="background:#fef3c7;color:#d97706">📂</div>
                <div class="folder-info">
                    <div class="folder-name">Main Directory</div>
                    <div class="folder-docs-count" id="fcount-none">{{ $documents->whereNull('folder_id')->count() }} files</div>
                    <div class="drag-hint">Move back to Main</div>
                </div>
            </div>

            {{-- Folder cards --}}
            @foreach($folders as $folder)
                <div class="folder-card" id="fcard-{{ $folder->id }}" onclick="filterFolder('{{ $folder->id }}')"
                     ondragover="onDragOverFolder(event, this)"
                     ondragleave="onDragLeaveFolder(this)"
                     ondrop="onDropToFolder(event, {{ $folder->id }})">
                    <div class="folder-icon" style="background:{{ $folder->color }}20;color:{{ $folder->color }}">
                        📁
                    </div>
                    <div class="folder-info">
                        <div class="folder-name" id="fname-{{ $folder->id }}" title="{{ $folder->name }}">{{ $folder->name }}</div>
                        <div class="folder-docs-count" id="fcount-{{ $folder->id }}">{{ $folder->documents_count }} files</div>
                        <div class="drag-hint">Drop file here!</div>
                    </div>
                    <div class="folder-actions-wrap" onclick="event.stopPropagation()">
                        <button type="button" class="folder-action-btn" title="Edit folder" onclick="openEditFolderModal(event, {{ json_encode(['id'=>$folder->id, 'name'=>$folder->name, 'description'=>$folder->description, 'color'=>$folder->color]) }})">✏️</button>
                        <button type="button" class="folder-action-btn danger" title="Delete folder" onclick="confirmDeleteFolder(event, {{ $folder->id }}, '{{ addslashes($folder->name) }}')">🗑</button>
                    </div>
                </div>
            @endforeach

            {{-- All files filter tab --}}
            <div class="folder-card" id="fcard-all" onclick="filterFolder('all')">
                <div class="folder-icon" style="background:#f1f5f9;color:#475569">🔍</div>
                <div class="folder-info">
                    <div class="folder-name">All Files (Overview)</div>
                    <div class="folder-docs-count">{{ $documents->count() }} files</div>
                </div>
            </div>
        </div>
    </section>

    {{-- VIEW BAR WITH SEARCH INPUT --}}
    <div class="view-bar">
        <div class="view-title" id="current-view-label">
            📂 Main Directory <span style="font-size:11px;font-weight:600;color:#64748b;">(Files in root project directory)</span>
        </div>

        <div class="search-box-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" class="search-input" id="search-input" placeholder="Search files by title or DOC #..." oninput="handleSearch(this.value)">
        </div>
    </div>

    {{-- DOCUMENTS LIST TABLE --}}
    <section class="files-list">
        <div class="files-row header" aria-hidden="true">
            <span>Document</span>
            <span>Folder Location</span>
            <span>Attached file</span>
            <span>Status</span>
            <span>Expiry</span>
            <span>Uploaded by</span>
            <span>Actions</span>
        </div>

        <div id="docs-container">
            @forelse($documents as $document)
                @php
                    $daysToExpiry = $document->expiry_date
                        ? (int) now()->startOfDay()->diffInDays($document->expiry_date->copy()->startOfDay(), false)
                        : null;
                    $expiryClass = $daysToExpiry !== null && $daysToExpiry < 0
                        ? 'expiry-overdue'
                        : ($daysToExpiry !== null && $daysToExpiry <= 30 ? 'expiry-soon' : '');
                    $expiryText = match(true) {
                        $daysToExpiry === null => 'No expiry',
                        $daysToExpiry < 0 => 'Expired ' . $document->expiry_date->format('M d, Y'),
                        $daysToExpiry === 0 => 'Today',
                        $daysToExpiry === 1 => 'Tomorrow',
                        $daysToExpiry <= 30 => "In {$daysToExpiry} days",
                        default => $document->expiry_date->format('M d, Y'),
                    };
                @endphp

                <article class="files-row doc-row"
                         id="doc-row-{{ $document->id }}"
                         data-doc-id="{{ $document->id }}"
                         data-doc-title="{{ strtolower($document->title) }}"
                         data-doc-num="{{ strtolower($document->document_number) }}"
                         data-folder-id="{{ $document->folder_id ?? 'none' }}"
                         draggable="true"
                         ondragstart="onDragStart(event, this)"
                         ondragend="onDragEnd(this)">
                    <div class="file-main">
                        <span class="file-title">{{ $document->title }}</span>
                        <span class="file-subtitle">{{ $document->document_number }} | {{ ucfirst($document->document_type) }}</span>
                    </div>

                    <div class="file-cell" data-label="Folder Location">
                        <span class="file-folder-badge" id="doc-fbadge-{{ $document->id }}" style="background:{{ $document->folder?->color ? $document->folder->color.'18' : '#f1f5f9' }};color:{{ $document->folder?->color ?? '#64748b' }}">
                            📁 {{ $document->folder?->name ?? 'Main Directory' }}
                        </span>
                    </div>

                    <div class="file-cell" data-label="Attached file">
                        @if($document->file_path)
                            <span class="file-title">{{ $document->original_filename ?: 'Document file' }}</span>
                            <span class="file-subtitle">{{ $document->formatted_file_size }}</span>
                        @elseif($document->scanned_image_path)
                            <span class="file-value">Scanned image</span>
                        @else
                            <span class="file-value">No file attached</span>
                        @endif
                    </div>

                    <div class="file-cell" data-label="Status">
                        <span class="file-status {{ $document->status }}">{{ ucfirst($document->status) }}</span>
                    </div>

                    <div class="file-cell" data-label="Expiry">
                        <span class="file-value {{ $expiryClass }}">{{ $expiryText }}</span>
                    </div>

                    <div class="file-cell" data-label="Uploaded by">
                        <span class="file-value">{{ $document->uploader?->name ?? 'System' }}</span>
                        <span class="file-subtitle">{{ $document->date_added?->format('M d, Y') }}</span>
                    </div>

                    <div class="file-actions">
                        <a href="{{ route('documents.show', $document) }}" class="file-action primary">View</a>
                        <a href="{{ route('documents.edit', $document) }}" class="file-action">Edit</a>
                        @if($document->file_path)
                            <a href="{{ route('documents.download', $document) }}" class="file-action">Download</a>
                        @elseif($document->scanned_image_path)
                            <a href="{{ route('documents.scan', $document) }}" target="_blank" rel="noopener" class="file-action">Open scan</a>
                        @endif

                        <button type="button" class="file-action danger" onclick="confirmDeleteFile({{ $document->id }}, '{{ addslashes($document->title) }}')">
                            Delete
                        </button>
                    </div>
                </article>
            @empty
                <div class="files-empty" id="empty-state-container">
                    <h2>No documents yet</h2>
                    <p>Add the first document for this project.</p>
                    <a href="{{ route('documents.create', ['project_id' => $project->id]) }}" class="btn-action files-add">Add document</a>
                </div>
            @endforelse
        </div>
    </section>
</div>

{{-- Toast Notification --}}
<div class="fx-toast" id="toast"></div>

{{-- CREATE FOLDER MODAL --}}
<div class="fx-modal-bg" id="create-folder-modal">
    <div class="fx-modal">
        <div class="fx-modal-title">📁 Create New Folder for {{ $project->name }}</div>
        <form method="POST" action="{{ route('document-folders.store') }}">
            @csrf
            <input type="hidden" name="project_id" value="{{ $project->id }}">
            <div class="fm-group">
                <label>Folder Name *</label>
                <input name="name" required placeholder="e.g. Invoices, Reports, WAR Files..." id="cf-name">
            </div>
            <div class="fm-group">
                <label>Description (Optional)</label>
                <textarea name="description" rows="2" placeholder="Folder description..."></textarea>
            </div>
            <div class="fm-group">
                <label>Color</label>
                <div class="color-row">
                    @foreach(['#0f9f8f','#2563eb','#7c3aed','#be185d','#b45309','#15803d','#374151'] as $c)
                        <div class="cswatch {{ $loop->first ? 'sel' : '' }}" style="background:{{ $c }}" data-color="{{ $c }}" onclick="selectFolderColor(this, '{{ $c }}')"></div>
                    @endforeach
                </div>
                <input type="hidden" name="color" id="cf-color" value="#0f9f8f">
            </div>
            <div class="fm-footer">
                <button type="button" class="btn-action" style="background:#e2e8f0;color:#334155" onclick="closeFolderModal()">Cancel</button>
                <button type="submit" class="btn-action files-add">Create Folder</button>
            </div>
        </form>
    </div>
</div>

{{-- EDIT FOLDER MODAL --}}
<div class="fx-modal-bg" id="edit-folder-modal">
    <div class="fx-modal">
        <div class="fx-modal-title">✏️ Edit Folder</div>
        <form id="edit-folder-form" method="POST">
            @csrf
            @method('PUT')
            <div class="fm-group">
                <label>Folder Name *</label>
                <input name="name" required id="ef-name">
            </div>
            <div class="fm-group">
                <label>Description (Optional)</label>
                <textarea name="description" rows="2" id="ef-desc"></textarea>
            </div>
            <div class="fm-group">
                <label>Color</label>
                <div class="color-row" id="ef-color-row">
                    @foreach(['#0f9f8f','#2563eb','#7c3aed','#be185d','#b45309','#15803d','#374151'] as $c)
                        <div class="cswatch" style="background:{{ $c }}" data-color="{{ $c }}" onclick="selectEditFolderColor(this, '{{ $c }}')"></div>
                    @endforeach
                </div>
                <input type="hidden" name="color" id="ef-color">
            </div>
            <div class="fm-footer">
                <button type="button" class="btn-action" style="background:#e2e8f0;color:#334155" onclick="closeModal('edit-folder-modal')">Cancel</button>
                <button type="submit" class="btn-action files-add">Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- DELETE FOLDER CONFIRMATION MODAL --}}
<div class="fx-modal-bg" id="delete-folder-modal">
    <div class="fx-modal" style="text-align:center">
        <div style="width:52px;height:52px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:24px;color:#dc2626">🗑</div>
        <div class="fx-modal-title" style="margin-bottom:8px">Delete Folder?</div>
        <p id="delete-folder-msg" style="font-size:13px;color:#6b7280;margin-bottom:20px"></p>
        <form id="delete-folder-form" method="POST">
            @csrf
            @method('DELETE')
            <input type="hidden" name="force" value="1">
            <div style="display:flex;gap:10px;justify-content:center">
                <button type="button" class="btn-action" style="background:#e2e8f0;color:#334155" onclick="closeModal('delete-folder-modal')">Cancel</button>
                <button type="submit" class="btn-action" style="background:#dc2626;color:#fff">Delete Folder</button>
            </div>
        </form>
    </div>
</div>

{{-- DELETE FILE CONFIRMATION MODAL --}}
<div class="fx-modal-bg" id="delete-file-modal">
    <div class="fx-modal" style="text-align:center">
        <div style="width:52px;height:52px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:24px;color:#dc2626">📄</div>
        <div class="fx-modal-title" style="margin-bottom:8px">Delete Document?</div>
        <p id="delete-file-msg" style="font-size:13px;color:#6b7280;margin-bottom:20px"></p>
        <form id="delete-file-form" method="POST">
            @csrf
            @method('DELETE')
            <div style="display:flex;gap:10px;justify-content:center">
                <button type="button" class="btn-action" style="background:#e2e8f0;color:#334155" onclick="closeModal('delete-file-modal')">Cancel</button>
                <button type="submit" class="btn-action" style="background:#dc2626;color:#fff">Delete File</button>
            </div>
        </form>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let draggedDocId = null;
let currentFolderFilter = 'none';
let searchQuery = '';

// Initial filter on page load -> check if folder_id query parameter exists
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const initialFolder = urlParams.get('folder_id') || '{{ request("folder_id") }}' || 'none';
    filterFolder(initialFolder);
});

/* Drag and Drop Handlers */
function onDragStart(e, row) {
    draggedDocId = row.dataset.docId;
    row.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', draggedDocId);
}

function onDragEnd(row) {
    row.classList.remove('dragging');
    draggedDocId = null;
}

function onDragOverFolder(e, card) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    card.classList.add('drag-over');
}

function onDragLeaveFolder(card) {
    card.classList.remove('drag-over');
}

function onDropToFolder(e, targetFolderId) {
    e.preventDefault();
    document.querySelectorAll('.folder-card').forEach(c => c.classList.remove('drag-over'));
    
    if (!draggedDocId) return;

    const row = document.getElementById('doc-row-' + draggedDocId);
    const docTitle = row.dataset.docTitle || 'Document';
    const oldFolderId = row.dataset.folderId;
    const newFolderIdStr = targetFolderId ? String(targetFolderId) : 'none';

    if (oldFolderId === newFolderIdStr) return; // No change

    // AJAX PATCH to set folder
    fetch(`/documents/${draggedDocId}/set-folder`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ folder_id: targetFolderId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Update row dataset
            row.dataset.folderId = newFolderIdStr;

            // Update badge text
            const badge = document.getElementById('doc-fbadge-' + draggedDocId);
            let folderName = 'Main Directory';
            if (badge) {
                if (targetFolderId) {
                    const folderNameEl = document.getElementById('fname-' + targetFolderId);
                    folderName = folderNameEl ? folderNameEl.textContent.trim() : 'Folder';
                    badge.textContent = '📁 ' + folderName;
                    badge.style.background = '#ecfdf8';
                    badge.style.color = '#0f766e';
                } else {
                    badge.textContent = '📁 Main Directory';
                    badge.style.background = '#f1f5f9';
                    badge.style.color = '#64748b';
                }
            }

            // Update folder counts
            updateFolderCount(oldFolderId, -1);
            updateFolderCount(newFolderIdStr, +1);

            // Re-apply filter
            applyCurrentFilters();

            showToast(`Moved to ${folderName}!`, 'success');
        } else {
            showToast('Failed to move document.', 'error');
        }
    })
    .catch(() => showToast('Network error moving document.', 'error'));
}

function updateFolderCount(folderIdStr, delta) {
    const el = document.getElementById('fcount-' + folderIdStr);
    if (el) {
        let current = parseInt(el.textContent) || 0;
        let updated = Math.max(0, current + delta);
        el.textContent = updated + ' files';
    }
}

function filterFolder(folderIdStr) {
    currentFolderFilter = String(folderIdStr);
    document.querySelectorAll('.folder-card').forEach(c => c.classList.remove('active'));

    const activeCard = document.getElementById('fcard-' + folderIdStr);
    if (activeCard) activeCard.classList.add('active');

    // Sync browser URL query string with current folder view so referer and page reloads stay on this folder!
    try {
        const url = new URL(window.location);
        url.searchParams.set('folder_id', folderIdStr);
        window.history.replaceState({}, '', url);
    } catch (e) {}

    // Update Add document button URL to keep folder context
    const addBtn = document.getElementById('add-doc-btn');
    if (addBtn) {
        let baseUrl = "{{ route('documents.create', ['project_id' => $project->id]) }}";
        if (folderIdStr && folderIdStr !== 'none' && folderIdStr !== 'all') {
            baseUrl += '&folder_id=' + folderIdStr;
        }
        addBtn.href = baseUrl;
    }

    // Update Label
    const viewLabel = document.getElementById('current-view-label');
    if (viewLabel) {
        if (folderIdStr === 'none') {
            viewLabel.innerHTML = '📂 Main Directory <span style="font-size:11px;font-weight:600;color:#64748b;">(Files in root project directory)</span>';
        } else if (folderIdStr === 'all') {
            viewLabel.innerHTML = '🔍 All Files <span style="font-size:11px;font-weight:600;color:#64748b;">(Overview of all files across all folders)</span>';
        } else {
            const fName = document.getElementById('fname-' + folderIdStr)?.textContent || 'Folder';
            viewLabel.innerHTML = `📁 Folder: <strong>${fName}</strong>`;
        }
    }

    applyCurrentFilters();
}

function handleSearch(query) {
    searchQuery = query.toLowerCase().trim();
    applyCurrentFilters();
}

function applyCurrentFilters() {
    let visibleCount = 0;

    document.querySelectorAll('.doc-row').forEach(row => {
        let matchesFolder = false;
        if (currentFolderFilter === 'all') {
            matchesFolder = true;
        } else if (currentFolderFilter === 'none') {
            matchesFolder = (row.dataset.folderId === 'none' || !row.dataset.folderId);
        } else {
            matchesFolder = (row.dataset.folderId === currentFolderFilter);
        }

        let matchesSearch = true;
        if (searchQuery) {
            const title = row.dataset.docTitle || '';
            const num = row.dataset.docNum || '';
            matchesSearch = title.includes(searchQuery) || num.includes(searchQuery);
        }

        const show = matchesFolder && matchesSearch;
        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });
}

function showToast(msg, type) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'fx-toast ' + type;
    t.style.display = 'block';
    setTimeout(() => { t.style.display = 'none'; }, 2800);
}

/* Edit Folder Modal */
function openEditFolderModal(e, folder) {
    e.stopPropagation();
    document.getElementById('edit-folder-form').action = `/document-folders/${folder.id}`;
    document.getElementById('ef-name').value = folder.name;
    document.getElementById('ef-desc').value = folder.description || '';
    document.getElementById('ef-color').value = folder.color || '#0f9f8f';
    
    document.querySelectorAll('#ef-color-row .cswatch').forEach(s => {
        s.classList.toggle('sel', s.dataset.color === (folder.color || '#0f9f8f'));
    });

    document.getElementById('edit-folder-modal').classList.add('open');
}

function selectEditFolderColor(swatch, color) {
    document.querySelectorAll('#ef-color-row .cswatch').forEach(s => s.classList.remove('sel'));
    swatch.classList.add('sel');
    document.getElementById('ef-color').value = color;
}

/* Delete Functions */
function confirmDeleteFolder(e, folderId, folderName) {
    e.stopPropagation(); // prevent triggering filterFolder on card click
    document.getElementById('delete-folder-form').action = `/document-folders/${folderId}`;
    document.getElementById('delete-folder-msg').innerHTML = `Are you sure you want to delete folder <strong>"${folderName}"</strong>?<br><small style="color:#94a3b8">Files inside will not be deleted, they will be returned to Main Directory.</small>`;
    document.getElementById('delete-folder-modal').classList.add('open');
}

function confirmDeleteFile(docId, docTitle) {
    document.getElementById('delete-file-form').action = `/documents/${docId}`;
    document.getElementById('delete-file-msg').innerHTML = `Are you sure you want to delete document <strong>"${docTitle}"</strong>?<br><small style="color:#94a3b8">This action cannot be undone.</small>`;
    document.getElementById('delete-file-modal').classList.add('open');
}

/* Modal functions */
function openCreateFolderModal() {
    document.getElementById('create-folder-modal').classList.add('open');
    document.getElementById('cf-name').focus();
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

function closeFolderModal() {
    document.getElementById('create-folder-modal').classList.remove('open');
}

function selectFolderColor(swatch, color) {
    document.querySelectorAll('.cswatch .sel').forEach(s => s.classList.remove('sel'));
    swatch.classList.add('sel');
    document.getElementById('cf-color').value = color;
}
</script>
@endsection
