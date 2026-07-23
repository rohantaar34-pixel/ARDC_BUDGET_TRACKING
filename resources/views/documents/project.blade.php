@extends('layouts.app')

@section('title', $project->name . ' Files - GEO CORP.')

@section('content')
<style>
    .project-files {
        max-width: 1100px;
        margin: 0 auto;
        color: #172033;
    }

    .files-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
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
    }

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
    }

    .files-add {
        display: inline-flex;
        min-height: 40px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 9px 15px;
        border-radius: 9px;
        background: #0f766e;
        color: #fff;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
    }

    .files-list {
        overflow: hidden;
        border: 1px solid #dfe3ea;
        border-radius: 13px;
        background: #fff;
    }

    .files-row {
        display: grid;
        grid-template-columns: minmax(230px, 1.5fr) minmax(170px, 1fr) 100px 145px 135px auto;
        gap: 14px;
        align-items: start;
        padding: 15px 18px;
        border-bottom: 1px solid #edf0f4;
        text-align: left;
    }

    .files-row:last-child { border-bottom: 0; }

    .files-row.header {
        padding-top: 11px;
        padding-bottom: 11px;
        background: #f8fafc;
        color: #697386;
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .06em;
        text-transform: uppercase;
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
        gap: 6px;
        text-align: left;
    }

    .file-action {
        display: inline-flex;
        min-height: 32px;
        align-items: center;
        justify-content: center;
        padding: 7px 10px;
        border: 1px solid #dfe3ea;
        border-radius: 8px;
        background: #fff;
        color: #475569;
        font-size: 9px;
        font-weight: 800;
        text-decoration: none;
    }

    .file-action.primary {
        border-color: #1d4ed8;
        background: #1d4ed8;
        color: #fff;
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

    .files-pagination { margin-top: 18px; }

    @media (max-width: 850px) {
        .files-row.header { display: none; }

        .files-row {
            grid-template-columns: 1fr 1fr;
            gap: 11px 18px;
            padding: 16px;
        }

        .file-main {
            grid-column: 1 / -1;
        }

        .file-cell::before {
            display: block;
            margin-bottom: 3px;
            color: #94a3b8;
            content: attr(data-label);
            font-size: 8px;
            font-weight: 850;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .file-actions {
            grid-column: 1 / -1;
            justify-content: flex-start;
        }
    }

    @media (max-width: 560px) {
        .files-header {
            align-items: stretch;
            flex-direction: column;
        }

        .files-title { font-size: 19px; }
        .files-add { width: 100%; }
        .files-row { grid-template-columns: 1fr; }
        .file-main,
        .file-actions { grid-column: auto; }
        .file-action { flex: 1; }
    }
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
                <p class="files-count">{{ $documents->total() }} {{ Str::plural('document', $documents->total()) }}</p>
            </div>
        </div>

        <a href="{{ route('documents.create', ['project_id' => $project->id]) }}" class="files-add">
            <span aria-hidden="true">+</span>
            Add document
        </a>
    </header>

    <section class="files-list">
        @if($documents->isNotEmpty())
            <div class="files-row header" aria-hidden="true">
                <span>Document</span>
                <span>Attached file</span>
                <span>Status</span>
                <span>Expiry</span>
                <span>Uploaded by</span>
                <span>Actions</span>
            </div>
        @endif

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

            <article class="files-row">
                <div class="file-main">
                    <span class="file-title">{{ $document->title }}</span>
                    <span class="file-subtitle">{{ $document->document_number }} | {{ ucfirst($document->document_type) }}</span>
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
                    @if($document->file_path)
                        <a href="{{ route('documents.download', $document) }}" class="file-action">Download</a>
                    @elseif($document->scanned_image_path)
                        <a href="{{ route('documents.scan', $document) }}" target="_blank" rel="noopener" class="file-action">Open scan</a>
                    @endif
                </div>
            </article>
        @empty
            <div class="files-empty">
                <h2>No documents yet</h2>
                <p>Add the first document for this project.</p>
                <a href="{{ route('documents.create', ['project_id' => $project->id]) }}" class="files-add">Add document</a>
            </div>
        @endforelse
    </section>

    @if($documents->hasPages())
        <div class="files-pagination">{{ $documents->links() }}</div>
    @endif
</div>
@endsection
