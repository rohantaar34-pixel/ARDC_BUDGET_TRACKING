@extends('layouts.app')

@section('content')
<style>
    :root {
        --indigo: #0f9f8f;
        --indigo-dark: #0f766e;
        --indigo-light: #ecfdf8;
        --green: #059669;
        --green-light: #ecfdf5;
        --red: #dc2626;
        --red-light: #fef2f2;
        --orange: #ea580c;
        --ink: #111827;
        --ink-2: #374151;
        --ink-3: #6b7280;
        --border: #e8e8ed;
        --bg: #f8f8fb;
        --white: #ffffff;
        --radius: 14px;
        --radius-sm: 9px;
    }

    * { box-sizing: border-box; }

    body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }

    .dt-container {
        padding: 24px 28px;
        background: var(--bg);
        min-height: calc(100vh - 80px);
    }

    /* Header */
    .dt-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .dt-title { font-size: 22px; font-weight: 800; color: var(--ink); }
    .dt-subtitle { font-size: 13px; color: var(--ink-3); margin-top: 2px; }

    .btn-add {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--indigo-dark);
        color: white;
        padding: 10px 20px;
        border-radius: var(--radius-sm);
        text-decoration: none;
        font-weight: 700;
        font-size: 14px;
        transition: background 0.2s;
    }
    .btn-add:hover { background: #0b5f58; }

    .btn-back-dash {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: white;
        color: var(--ink-2);
        padding: 10px 16px;
        border-radius: var(--radius-sm);
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        border: 1px solid var(--border);
        transition: all 0.2s;
    }
    .btn-back-dash:hover { background: var(--indigo-light); border-color: var(--indigo); color: var(--indigo); }

    /* Stats */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }
    .stat-card {
        background: white;
        border-radius: var(--radius);
        padding: 18px 20px;
        border: 1px solid var(--border);
    }
    .stat-val { font-size: 26px; font-weight: 800; color: var(--indigo); }
    .stat-lbl { font-size: 12px; color: var(--ink-3); margin-top: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }

    /* Filters */
    .filter-card {
        background: white;
        border-radius: var(--radius);
        padding: 16px 20px;
        margin-bottom: 24px;
        border: 1px solid var(--border);
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
    }
    .filter-card input, .filter-card select {
        padding: 9px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        outline: none;
    }
    .filter-card input:focus, .filter-card select:focus { border-color: var(--indigo); }
    .filter-card input { flex: 1; min-width: 180px; }
    .btn-filter { background: var(--ink-3); color: white; padding: 9px 18px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; font-family: inherit; }
    .btn-reset { background: white; color: var(--ink-2); padding: 9px 16px; border-radius: 8px; border: 1px solid var(--border); text-decoration: none; font-weight: 600; font-size: 13px; }

    /* View toggle */
    .view-toggle {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        align-items: center;
    }
    .toggle-btn {
        padding: 7px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid var(--border);
        background: white;
        color: var(--ink-3);
        transition: all 0.2s;
    }
    .toggle-btn.active { background: var(--indigo-dark); color: white; border-color: var(--indigo-dark); }

    /* ===== PROJECT FOLDERS VIEW ===== */
    .projects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 16px;
    }
    .project-folder {
        background: white;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        overflow: hidden;
        transition: box-shadow 0.2s;
    }
    .project-folder:hover { box-shadow: 0 4px 12px rgba(15,159,143,0.1); }

    .folder-header {
        padding: 18px 20px 14px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .folder-icon {
        width: 38px; height: 38px;
        background: var(--indigo-light);
        color: var(--indigo);
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 8px;
        flex-shrink: 0;
    }
    .folder-name { font-size: 15px; font-weight: 700; color: var(--ink); margin-bottom: 2px; }
    .folder-count { font-size: 12px; color: var(--ink-3); }
    .btn-add-to-folder {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 6px 12px;
        background: var(--indigo-light);
        color: var(--indigo-dark);
        border-radius: 7px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        flex-shrink: 0;
        transition: all 0.2s;
    }
    .btn-add-to-folder:hover { background: var(--indigo-dark); color: white; }

    .folder-docs { padding: 8px 0; }
    .doc-row {
        display: flex;
        align-items: center;
        padding: 10px 20px;
        gap: 12px;
        border-bottom: 1px solid #f9fafb;
        transition: background 0.1s;
    }
    .doc-row:last-child { border-bottom: none; }
    .doc-row:hover { background: #fafafa; }
    .doc-type-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .doc-info { flex: 1; min-width: 0; }
    .doc-name {
        font-size: 13px; font-weight: 600; color: var(--ink);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .doc-meta { font-size: 11px; color: var(--ink-3); margin-top: 2px; }
    .doc-badge {
        padding: 2px 8px; border-radius: 99px;
        font-size: 10px; font-weight: 700; flex-shrink: 0;
    }
    .doc-badge.active { background: var(--green-light); color: var(--green); }
    .doc-badge.archived { background: #f3f4f6; color: var(--ink-2); }
    .doc-badge.expired { background: var(--red-light); color: var(--red); }

    .doc-actions { display: flex; gap: 6px; flex-shrink: 0; }
    .doc-act-btn {
        padding: 4px 8px; border-radius: 5px;
        font-size: 11px; font-weight: 600;
        text-decoration: none; transition: all 0.15s;
    }
    .doc-act-btn.view { background: #dbeafe; color: #1d4ed8; }
    .doc-act-btn.view:hover { background: #c7d2fe; }
    .doc-act-btn.edit { background: var(--green-light); color: var(--green); }
    .doc-act-btn.edit:hover { background: #a7f3d0; }

    .folder-empty {
        padding: 20px;
        text-align: center;
        color: var(--ink-3);
        font-size: 13px;
    }
    .folder-see-all {
        display: block;
        padding: 10px 20px;
        font-size: 12px;
        font-weight: 600;
        color: var(--indigo);
        text-align: center;
        text-decoration: none;
        border-top: 1px solid #f3f4f6;
        background: #fafafa;
        transition: background 0.15s;
    }
    .folder-see-all:hover { background: var(--indigo-light); }
    .folder-open {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        color: var(--indigo-dark);
    }

    /* No project folder */
    .no-project-folder .folder-icon { background: #f3f4f6; color: var(--ink-3); }
    .no-project-folder .btn-add-to-folder { background: #f3f4f6; color: var(--ink-2); }
    .no-project-folder .btn-add-to-folder:hover { background: var(--ink-2); color: white; }

    /* ===== LIST VIEW ===== */
    .doc-table-wrap {
        background: white;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        overflow: hidden;
    }
    table { width: 100%; border-collapse: collapse; }
    th {
        text-align: left; padding: 14px 16px;
        background: #f9fafb; font-weight: 600;
        color: var(--ink-2); font-size: 12px;
        text-transform: uppercase; letter-spacing: 0.05em;
    }
    td { padding: 14px 16px; border-top: 1px solid #f3f4f6; font-size: 14px; }

    /* type dot colors */
    .type-contract { background: #2563eb; }
    .type-invoice { background: #f59e0b; }
    .type-report { background: #10b981; }
    .type-other { background: #9ca3af; }
    .doc-act-btn.delete { background: #fee2e2; color: #dc2626; }
    .doc-act-btn.delete:hover { background: #fca5a5; }

    /* Category folder extra colors */
    .cat-war  .folder-icon { background: #fef3c7; color: #b45309; }
    .cat-dar  .folder-icon { background: #e0e7ff; color: #4338ca; }
    .cat-fin  .folder-icon { background: #dcfce7; color: #15803d; }
    .cat-leg  .folder-icon { background: #fce7f3; color: #be185d; }
    .cat-tec  .folder-icon { background: #e0f2fe; color: #0369a1; }
    .cat-adm  .folder-icon { background: #f3f4f6; color: #374151; }
    .cat-oth  .folder-icon { background: #faf5ff; color: #7c3aed; }
</style>

<div class="dt-container">

    {{-- Header --}}
    <div class="dt-header">
        <div>
            <div class="dt-title">📁 Document Tracker</div>
            <div class="dt-subtitle">Manage all project documents in one place</div>
        </div>
        <div style="display:flex; gap:12px; flex-wrap:wrap;">
            <a href="{{ route('dashboard') }}" class="btn-back-dash">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>
            <a href="{{ route('document-folders.index') }}" class="btn-back-dash">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                Folders
            </a>
            <a href="{{ route('documents.create') }}" class="btn-add">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Document
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-val">{{ $stats['total'] }}</div>
            <div class="stat-lbl">Total Documents</div>
        </div>
        <div class="stat-card">
            <div class="stat-val" style="color: var(--green);">{{ $stats['active'] }}</div>
            <div class="stat-lbl">Active</div>
        </div>
        <div class="stat-card">
            <div class="stat-val" style="color: var(--ink-3);">{{ $stats['archived'] }}</div>
            <div class="stat-lbl">Archived</div>
        </div>
        <div class="stat-card">
            <div class="stat-val" style="color: var(--orange);">{{ $projects->count() }}</div>
            <div class="stat-lbl">Projects</div>
        </div>
        <div class="stat-card">
            <div class="stat-val" style="color: var(--indigo);">{{ $stats['recent'] }}</div>
            <div class="stat-lbl">Added (30 days)</div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="filter-card">
        <input type="text" name="search" placeholder="Search documents..." value="{{ request('search') }}">
        <select name="type">
            <option value="">All Types</option>
            <option value="contract" {{ request('type') == 'contract' ? 'selected' : '' }}>Contract</option>
            <option value="invoice" {{ request('type') == 'invoice' ? 'selected' : '' }}>Invoice</option>
            <option value="report" {{ request('type') == 'report' ? 'selected' : '' }}>Report</option>
            <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
        </select>
        <select name="status">
            <option value="">All Status</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
        </select>
        <select name="project_id">
            <option value="">All Projects</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                    {{ $project->name }}
                </option>
            @endforeach
        </select>
        <select name="view" style="min-width: 130px;">
            <option value="folders" {{ request('view', 'folders') == 'folders' ? 'selected' : '' }}>📁 By Project</option>
            <option value="categories" {{ request('view') == 'categories' ? 'selected' : '' }}>🗂️ By Category</option>
            <option value="list" {{ request('view') == 'list' ? 'selected' : '' }}>📋 All Documents</option>
        </select>
        <button type="submit" class="btn-filter">Filter</button>
        <a href="{{ route('documents.index') }}" class="btn-reset">Reset</a>
    </form>

    @if(request('view') == 'list')
        {{-- ===== LIST VIEW ===== --}}
        <div class="doc-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Doc ID</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Project</th>
                        <th>Status</th>
                        <th>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $document)
                        <tr>
                            <td style="font-size:12px; color: var(--ink-3); font-family: monospace;">{{ $document->document_number }}</td>
                            <td>
                                <strong>{{ $document->title }}</strong>
                                @if ($document->description)
                                    <br><small style="color: var(--ink-3);">{{ Str::limit($document->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <span style="display:inline-flex; align-items:center; gap:6px;">
                                    <span class="doc-type-dot type-{{ $document->document_type }}" style="width:8px;height:8px;border-radius:50%;display:inline-block;"></span>
                                    {{ ucfirst($document->document_type) }}
                                </span>
                            </td>
                            <td>
                                @if($document->project)
                                    <span style="background:var(--indigo-light);color:var(--indigo-dark);padding:3px 8px;border-radius:5px;font-size:12px;font-weight:600;">{{ $document->project->name }}</span>
                                @else
                                    <span style="color:var(--ink-3);">—</span>
                                @endif
                            </td>
                            <td><span class="doc-badge {{ $document->status }}">{{ ucfirst($document->status) }}</span></td>
                            <td style="color: var(--ink-3); font-size:13px;">{{ $document->date_added->format('M d, Y') }}</td>
                            <td>
                                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                    <a href="{{ route('documents.show', $document) }}" class="doc-act-btn view">View</a>
                                    <a href="{{ route('documents.edit', $document) }}" class="doc-act-btn edit">Edit</a>
                                    @if ($document->file_path)
                                        <a href="{{ route('documents.download', $document) }}" class="doc-act-btn" style="background:#fed7aa;color:#92400e;">⬇</a>
                                    @endif
                                    <button type="button"
                                        onclick="confirmDelete('{{ route('documents.destroy', $document) }}', '{{ addslashes($document->title) }}')"
                                        class="doc-act-btn delete">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:40px; color:var(--ink-3);">
                                No documents found. <a href="{{ route('documents.create') }}" style="color:var(--indigo);">Add one now</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:20px;">{{ $documents->appends(request()->query())->links() }}</div>

    @elseif(request('view') == 'categories')
        {{-- ===== CATEGORY FOLDERS VIEW ===== --}}
        @php
            $categoryDefs = [
                'war'            => ['label' => 'WAR', 'sub' => 'Weekly Accomplishment Report', 'cls' => 'cat-war'],
                'dar'            => ['label' => 'DAR', 'sub' => 'Daily Accomplishment Report',  'cls' => 'cat-dar'],
                'financial'      => ['label' => 'Financial',      'sub' => '',  'cls' => 'cat-fin'],
                'legal'          => ['label' => 'Legal',          'sub' => '',  'cls' => 'cat-leg'],
                'technical'      => ['label' => 'Technical',      'sub' => '',  'cls' => 'cat-tec'],
                'administrative' => ['label' => 'Administrative', 'sub' => '',  'cls' => 'cat-adm'],
                'other'          => ['label' => 'Other',          'sub' => '',  'cls' => 'cat-oth'],
            ];
        @endphp
        <div class="projects-grid">
            @foreach($categoryDefs as $catKey => $catInfo)
                @php
                    $catTotal = \App\Models\Document::where('category', $catKey)->count();
                    $catDocs  = \App\Models\Document::where('category', $catKey)->with('project')->latest('date_added')->take(4)->get();
                @endphp
                <div class="project-folder {{ $catInfo['cls'] }}">
                    <div class="folder-header">
                        <div>
                            <div class="folder-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                            </div>
                            <div class="folder-name">{{ $catInfo['label'] }}</div>
                            @if($catInfo['sub'])
                                <div class="folder-count" style="font-style:italic;">{{ $catInfo['sub'] }}</div>
                            @endif
                            <div class="folder-count">{{ $catTotal }} {{ Str::plural('document', $catTotal) }}</div>
                        </div>
                        <a href="{{ route('documents.create') }}?category={{ $catKey }}" class="btn-add-to-folder">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add Doc
                        </a>
                    </div>
                    <div class="folder-docs">
                        @forelse($catDocs as $doc)
                            <div class="doc-row">
                                <div class="doc-type-dot type-{{ $doc->document_type }}"></div>
                                <div class="doc-info">
                                    <div class="doc-name">{{ $doc->title }}</div>
                                    <div class="doc-meta">{{ $doc->document_number }} · {{ $doc->date_added->format('M d, Y') }}{{ $doc->project ? ' · '.$doc->project->name : '' }}</div>
                                </div>
                                <span class="doc-badge {{ $doc->status }}">{{ ucfirst($doc->status) }}</span>
                                <div class="doc-actions">
                                    <a href="{{ route('documents.show', $doc) }}" class="doc-act-btn view">View</a>
                                </div>
                            </div>
                        @empty
                            <div class="folder-empty">No documents yet. <a href="{{ route('documents.create') }}?category={{ $catKey }}" style="color:var(--indigo);">Add one →</a></div>
                        @endforelse
                    </div>
                    @if($catTotal > 0)
                        <a href="{{ route('documents.by-category', $catKey) }}" class="folder-see-all folder-open">
                            <span>Open folder · view all {{ $catTotal }}</span>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

    @else
        {{-- ===== PROJECT FOLDERS VIEW (default) ===== --}}
        <div class="projects-grid">

            {{-- One card per project --}}
            @foreach($projects as $proj)
                @php
                    $projDocs = $documents->getCollection()->where('project_id', $proj->id);
                    $totalProjDocs = \App\Models\Document::where('project_id', $proj->id)->count();
                @endphp
                <div class="project-folder">
                    <div class="folder-header">
                        <div>
                            <div class="folder-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                            </div>
                            <div class="folder-name">{{ $proj->name }}</div>
                            <div class="folder-count">{{ $totalProjDocs }} {{ Str::plural('document', $totalProjDocs) }}</div>
                        </div>
                        <a href="{{ route('documents.create') }}?project_id={{ $proj->id }}" class="btn-add-to-folder">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add Doc
                        </a>
                    </div>

                    <div class="folder-docs">
                        @php $shownDocs = \App\Models\Document::with('project')->where('project_id', $proj->id)->latest('date_added')->take(4)->get(); @endphp
                        @forelse($shownDocs as $doc)
                            <div class="doc-row">
                                <div class="doc-type-dot type-{{ $doc->document_type }}"></div>
                                <div class="doc-info">
                                    <div class="doc-name">{{ $doc->title }}</div>
                                    <div class="doc-meta">{{ $doc->document_number }} · {{ $doc->date_added->format('M d, Y') }}</div>
                                </div>
                                <span class="doc-badge {{ $doc->status }}">{{ ucfirst($doc->status) }}</span>
                                <div class="doc-actions">
                                    <a href="{{ route('documents.show', $doc) }}" class="doc-act-btn view">View</a>
                                </div>
                            </div>
                        @empty
                            <div class="folder-empty">
                                No documents yet.
                                <a href="{{ route('documents.create') }}?project_id={{ $proj->id }}" style="color: var(--indigo);">Add the first one →</a>
                            </div>
                        @endforelse
                    </div>

                    <a href="{{ route('documents.project', $proj) }}" class="folder-see-all folder-open">
                        <span>Open folder and view all details</span>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path d="M5 12h14"></path>
                            <path d="m13 6 6 6-6 6"></path>
                        </svg>
                    </a>

                </div>
            @endforeach

            {{-- Documents with no project --}}
            @php $unassignedCount = \App\Models\Document::whereNull('project_id')->count(); @endphp
            @if($unassignedCount > 0)
                <div class="project-folder no-project-folder">
                    <div class="folder-header">
                        <div>
                            <div class="folder-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                            </div>
                            <div class="folder-name" style="color:var(--ink-3);">Unassigned Documents</div>
                            <div class="folder-count">{{ $unassignedCount }} {{ Str::plural('document', $unassignedCount) }}</div>
                        </div>
                        <a href="{{ route('documents.index') }}?project_id=none&view=list" class="btn-add-to-folder">View All</a>
                    </div>
                    <div class="folder-docs">
                        @foreach(\App\Models\Document::whereNull('project_id')->latest('date_added')->take(3)->get() as $doc)
                            <div class="doc-row">
                                <div class="doc-type-dot type-{{ $doc->document_type }}"></div>
                                <div class="doc-info">
                                    <div class="doc-name">{{ $doc->title }}</div>
                                    <div class="doc-meta">{{ $doc->document_number }} · {{ $doc->date_added->format('M d, Y') }}</div>
                                </div>
                                <span class="doc-badge {{ $doc->status }}">{{ ucfirst($doc->status) }}</span>
                                <div class="doc-actions">
                                    <a href="{{ route('documents.show', $doc) }}" class="doc-act-btn view">View</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($unassignedCount > 3)
                        <a href="{{ route('documents.index') }}?view=list" class="folder-see-all">See all unassigned →</a>
                    @endif
                </div>
            @endif

            {{-- Empty state --}}
            @if($projects->isEmpty())
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px; color: var(--ink-3);">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 16px;"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                    <p style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">No projects yet</p>
                    <p style="font-size: 14px;">Create a project in <a href="{{ route('settings.projects.index') }}" style="color:var(--indigo);">Settings</a> first, then add documents to it.</p>
                </div>
            @endif
        </div>
    @endif

</div>

{{-- Delete Confirmation Modal --}}
<div id="del-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:16px;padding:32px;max-width:420px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <div style="width:56px;height:56px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
        </div>
        <h3 style="font-size:18px;font-weight:700;color:#111827;margin-bottom:8px;">Delete Document?</h3>
        <p id="del-modal-name" style="font-size:14px;color:#6b7280;margin-bottom:24px;"></p>
        <div style="display:flex;gap:12px;justify-content:center;">
            <button onclick="document.getElementById('del-modal').style.display='none'"
                style="padding:10px 24px;border-radius:8px;border:1px solid #e5e7eb;background:white;font-weight:600;cursor:pointer;font-family:inherit;">Cancel</button>
            <form id="del-form" method="POST" style="margin:0;">
                @csrf @method('DELETE')
                <button type="submit" style="padding:10px 24px;border-radius:8px;background:#dc2626;color:white;font-weight:600;border:none;cursor:pointer;font-family:inherit;">Yes, Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Auto-select project in create form if project_id is passed in URL from "Add Doc" button
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('project_id')) {
        sessionStorage.setItem('preselect_project', urlParams.get('project_id'));
    }

    function confirmDelete(actionUrl, docTitle) {
        document.getElementById('del-form').action = actionUrl;
        document.getElementById('del-modal-name').innerHTML =
            '<strong>' + docTitle + '</strong> will be permanently deleted along with its files. This cannot be undone.';
        document.getElementById('del-modal').style.display = 'flex';
    }
</script>
@endsection
