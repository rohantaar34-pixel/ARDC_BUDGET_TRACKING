@extends('layouts.app')

@section('content')
<style>
    :root {
        --teal:       #0f9f8f;
        --teal-dark:  #0f766e;
        --teal-light: #ecfdf8;
        --green:      #059669;
        --green-light:#ecfdf5;
        --red:        #dc2626;
        --red-light:  #fef2f2;
        --orange:     #ea580c;
        --ink:        #111827;
        --ink-2:      #374151;
        --ink-3:      #6b7280;
        --border:     #e8e8ed;
        --bg:         #f8f8fb;
        --white:      #ffffff;
        --radius:     14px;
        --radius-sm:  9px;
    }
    * { box-sizing: border-box; }
    body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }

    .sf-wrap { padding: 28px; background: var(--bg); min-height: calc(100vh - 80px); }

    /* ── Header ── */
    .sf-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 26px; flex-wrap: wrap; gap: 12px;
    }
    .sf-breadcrumb {
        display: flex; align-items: center; gap: 8px;
        font-size: 13px; color: var(--ink-3); margin-bottom: 6px; flex-wrap: wrap;
    }
    .sf-breadcrumb a { color: var(--teal); text-decoration: none; }
    .sf-breadcrumb a:hover { text-decoration: underline; }
    .sf-breadcrumb span { color: var(--ink-3); }
    .sf-title {
        font-size: 21px; font-weight: 800; color: var(--ink);
        display: flex; align-items: center; gap: 10px;
    }
    .folder-color-dot {
        width: 14px; height: 14px; border-radius: 4px; flex-shrink: 0;
    }
    .sf-sub { font-size: 13px; color: var(--ink-3); margin-top: 3px; }

    .btn-primary {
        display: inline-flex; align-items: center; gap: 8px;
        background: var(--teal-dark); color: white;
        padding: 10px 20px; border-radius: var(--radius-sm);
        font-weight: 700; font-size: 14px; text-decoration: none; border: none; cursor: pointer;
        font-family: inherit; transition: background 0.2s;
    }
    .btn-primary:hover { background: #0b5f58; }

    .btn-ghost {
        display: inline-flex; align-items: center; gap: 8px;
        background: white; color: var(--ink-2);
        padding: 10px 16px; border-radius: var(--radius-sm);
        font-weight: 600; font-size: 13px; text-decoration: none;
        border: 1px solid var(--border); transition: all 0.2s;
    }
    .btn-ghost:hover { background: var(--teal-light); border-color: var(--teal); color: var(--teal); }

    /* ── Flash ── */
    .flash { padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; font-weight: 600; }
    .flash.success { background: #dcfce7; color: #15803d; }
    .flash.error   { background: var(--red-light); color: var(--red); }

    /* ── Filter bar ── */
    .filter-bar {
        background: white; border: 1px solid var(--border);
        border-radius: var(--radius); padding: 14px 18px;
        display: flex; gap: 10px; flex-wrap: wrap; align-items: center;
        margin-bottom: 22px;
    }
    .filter-bar input, .filter-bar select {
        padding: 9px 12px; border: 1px solid #e5e7eb;
        border-radius: 8px; font-size: 14px; font-family: inherit; outline: none;
        transition: border-color 0.2s;
    }
    .filter-bar input { flex: 1; min-width: 180px; }
    .filter-bar input:focus, .filter-bar select:focus { border-color: var(--teal); }
    .btn-filter {
        background: var(--ink-3); color: white; padding: 9px 18px;
        border-radius: 8px; border: none; cursor: pointer; font-weight: 600; font-family: inherit;
    }
    .btn-reset {
        background: white; color: var(--ink-2); padding: 9px 16px;
        border-radius: 8px; border: 1px solid var(--border); text-decoration: none;
        font-weight: 600; font-size: 13px;
    }

    /* ── Table ── */
    .doc-table-wrap {
        background: white; border-radius: var(--radius);
        border: 1px solid var(--border); overflow: hidden;
    }
    table { width: 100%; border-collapse: collapse; }
    th {
        text-align: left; padding: 13px 16px;
        background: #f9fafb; font-weight: 700;
        color: var(--ink-2); font-size: 12px;
        text-transform: uppercase; letter-spacing: 0.05em;
    }
    td { padding: 13px 16px; border-top: 1px solid #f3f4f6; font-size: 14px; vertical-align: middle; }
    tr:hover td { background: #fafafa; }

    .doc-num { font-size: 11px; color: var(--ink-3); font-family: monospace; }
    .doc-badge { padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; }
    .doc-badge.active   { background: var(--green-light); color: var(--green); }
    .doc-badge.archived { background: #f3f4f6; color: var(--ink-2); }
    .doc-badge.expired  { background: var(--red-light); color: var(--red); }

    .act-btn {
        padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600;
        text-decoration: none; display: inline-flex; align-items: center; gap: 4px;
        transition: all 0.15s; border: none; cursor: pointer; font-family: inherit;
    }
    .act-view   { background: #dbeafe; color: #1d4ed8; }
    .act-view:hover { background: #c7d2fe; }
    .act-edit   { background: var(--green-light); color: var(--green); }
    .act-edit:hover { background: #a7f3d0; }
    .act-delete { background: var(--red-light); color: var(--red); }
    .act-delete:hover { background: #fca5a5; }
    .act-dl     { background: #fed7aa; color: #92400e; }
    .act-dl:hover { background: #fdba74; }

    /* ── Empty ── */
    .empty-row td { text-align: center; padding: 60px; color: var(--ink-3); }

    /* ── Delete Modal ── */
    .modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.45); z-index: 999;
        align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .modal-box { background: white; border-radius: 16px; padding: 32px; max-width: 420px; width: 90%; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.25); }
    .del-icon { width: 56px; height: 56px; background: var(--red-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
</style>

<div class="sf-wrap">

    {{-- Breadcrumb + Header --}}
    <div class="sf-header">
        <div>
            <div class="sf-breadcrumb">
                <a href="{{ route('documents.index') }}">Documents</a>
                <span>›</span>
                <a href="{{ route('document-folders.index') }}">Folders</a>
                <span>›</span>
                <strong style="color:var(--ink);">{{ $documentFolder->name }}</strong>
            </div>
            <div class="sf-title">
                <div class="folder-color-dot" style="background: {{ $documentFolder->color }};"></div>
                {{ $documentFolder->name }}
            </div>
            <div class="sf-sub">
                {{ $documents->total() }} {{ Str::plural('document', $documents->total()) }}
                @if($documentFolder->description) · {{ $documentFolder->description }}@endif
                @if($documentFolder->project) · <strong>{{ $documentFolder->project->name }}</strong>@endif
            </div>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="{{ route('document-folders.index') }}" class="btn-ghost">← Folders</a>
            <a href="{{ route('documents.create') }}?folder_id={{ $documentFolder->id }}" class="btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Document
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="flash success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="flash error">⚠ {{ session('error') }}</div>
    @endif

    {{-- Filter bar --}}
    <form method="GET" class="filter-bar">
        <input type="text" name="search" placeholder="Search title, document number…" value="{{ request('search') }}">
        <select name="status">
            <option value="">All Status</option>
            <option value="active"   {{ request('status') == 'active'   ? 'selected' : '' }}>Active</option>
            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
            <option value="expired"  {{ request('status') == 'expired'  ? 'selected' : '' }}>Expired</option>
        </select>
        <button type="submit" class="btn-filter">Filter</button>
        <a href="{{ route('document-folders.show', $documentFolder) }}" class="btn-reset">Reset</a>
    </form>

    {{-- Documents table --}}
    <div class="doc-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Attached File</th>
                    <th>Status</th>
                    <th>Expiry</th>
                    <th>Uploaded By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $doc)
                    <tr>
                        <td>
                            <strong style="display:block; font-size:14px; color:var(--ink);">{{ $doc->title }}</strong>
                            <span class="doc-num">{{ $doc->document_number }}
                                @if($doc->document_type) | {{ ucfirst($doc->document_type) }}@endif
                            </span>
                        </td>
                        <td style="color:var(--ink-3); font-size:13px;">
                            @if($doc->file_path)
                                📎 {{ $doc->original_filename ?? basename($doc->file_path) }}
                                <br><small>{{ $doc->formatted_file_size }}</small>
                            @else
                                <span style="color:var(--ink-3);">No file attached</span>
                            @endif
                        </td>
                        <td><span class="doc-badge {{ $doc->status }}">{{ ucfirst($doc->status) }}</span></td>
                        <td style="font-size:13px; color:var(--ink-3);">
                            @if($doc->expiry_date)
                                {{ $doc->expiry_date->format('M d, Y') }}
                            @else
                                No expiry
                            @endif
                        </td>
                        <td style="font-size:13px; color:var(--ink-2);">
                            {{ $doc->uploader?->name ?? '—' }}<br>
                            <small style="color:var(--ink-3);">{{ $doc->date_added->format('M d, Y') }}</small>
                        </td>
                        <td>
                            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                <a href="{{ route('documents.show', $doc) }}" class="act-btn act-view">View</a>
                                <a href="{{ route('documents.edit', $doc) }}" class="act-btn act-edit">Edit</a>
                                @if($doc->file_path)
                                    <a href="{{ route('documents.download', $doc) }}" class="act-btn act-dl">⬇</a>
                                @endif
                                <button type="button"
                                    onclick="confirmDelete('{{ route('documents.destroy', $doc) }}', '{{ addslashes($doc->title) }}')"
                                    class="act-btn act-delete">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="6">
                            No documents in this folder yet.
                            <a href="{{ route('documents.create') }}?folder_id={{ $documentFolder->id }}" style="color:var(--teal);">Add the first one →</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">{{ $documents->appends(request()->query())->links() }}</div>

</div>

{{-- Delete Confirmation Modal --}}
<div id="del-modal" class="modal-overlay">
    <div class="modal-box">
        <div class="del-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2">
                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
                <path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/>
            </svg>
        </div>
        <h3 style="font-size:18px;font-weight:700;color:#111827;margin-bottom:8px;">Delete Document?</h3>
        <p id="del-modal-name" style="font-size:14px;color:#6b7280;margin-bottom:24px;"></p>
        <div style="display:flex;gap:12px;justify-content:center;">
            <button onclick="document.getElementById('del-modal').classList.remove('open')"
                style="padding:10px 24px;border-radius:8px;border:1px solid #e5e7eb;background:white;font-weight:600;cursor:pointer;font-family:inherit;">
                Cancel
            </button>
            <form id="del-form" method="POST" style="margin:0;">
                @csrf @method('DELETE')
                <button type="submit"
                    style="padding:10px 24px;border-radius:8px;background:#dc2626;color:white;font-weight:600;border:none;cursor:pointer;font-family:inherit;">
                    Yes, Delete
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmDelete(actionUrl, docTitle) {
        document.getElementById('del-form').action = actionUrl;
        document.getElementById('del-modal-name').innerHTML =
            '<strong>' + docTitle + '</strong> will be permanently deleted along with its files. This cannot be undone.';
        document.getElementById('del-modal').classList.add('open');
    }
    document.getElementById('del-modal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
</script>
@endsection
