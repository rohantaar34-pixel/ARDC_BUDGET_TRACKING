@extends('layouts.app')

@section('content')
<style>
    .view-container {
        max-width: 800px;
        margin: 40px auto;
        background: white;
        border-radius: 16px;
        padding: 32px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e5e7eb;
    }
    .doc-title {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 8px;
    }
    .doc-id {
        font-size: 14px;
        color: #6b7280;
        font-weight: 500;
    }
    .badge {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .badge-active { background: #d1fae5; color: #065f46; }
    .badge-archived { background: #f3f4f6; color: #374151; }
    .badge-expired { background: #fee2e2; color: #dc2626; }
    
    .details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 32px;
    }
    .detail-item {
        margin-bottom: 16px;
    }
    .detail-label {
        font-size: 13px;
        color: #6b7280;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
    }
    .detail-value {
        font-size: 15px;
        color: #111827;
        font-weight: 500;
    }
    .full-width {
        grid-column: 1 / -1;
    }
    .description-box {
        background: #f9fafb;
        padding: 16px;
        border-radius: 8px;
        border: 1px solid #f3f4f6;
        color: #374151;
        font-size: 14px;
        line-height: 1.6;
    }
    
    .file-section {
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
    }
    .file-card {
        display: flex;
        align-items: center;
        padding: 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        margin-bottom: 12px;
    }
    .file-icon {
        width: 40px;
        height: 40px;
        background: #e0e7ff;
        color: #4f46e5;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
    }
    .file-info {
        flex: 1;
    }
    .file-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 14px;
        margin-bottom: 4px;
    }
    .file-meta {
        font-size: 12px;
        color: #64748b;
    }
    
    .action-buttons {
        display: flex;
        gap: 12px;
        margin-top: 32px;
    }
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
    }
    .btn-back {
        background: #f3f4f6;
        color: #374151;
    }
    .btn-back:hover { background: #e5e7eb; }
    
    .btn-edit {
        background: #4f46e5;
        color: white;
    }
    .btn-edit:hover { background: #4338ca; }
    
    .btn-download {
        background: #10b981;
        color: white;
    }
    .btn-download:hover { background: #059669; }
</style>

<div class="view-container">
    <div class="header-section">
        <div>
            <div class="doc-id">{{ $document->document_number }}</div>
            <h1 class="doc-title">{{ $document->title }}</h1>
        </div>
        <div class="badge badge-{{ $document->status }}">
            {{ ucfirst($document->status) }}
        </div>
    </div>

    <div class="details-grid">
        <div class="detail-item full-width">
            <div class="detail-label">Description</div>
            <div class="description-box">
                {{ $document->description ?: 'No description provided.' }}
            </div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Document Type</div>
            <div class="detail-value">{{ ucfirst($document->document_type) }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Category</div>
            <div class="detail-value">{{ ucfirst($document->category ?? 'None') }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Associated Project</div>
            <div class="detail-value">{{ $document->project ? $document->project->name : 'None' }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Uploaded By</div>
            <div class="detail-value">{{ $document->uploader ? $document->uploader->name : 'System' }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Document Date</div>
            <div class="detail-value">{{ $document->document_date ? $document->document_date->format('F d, Y') : 'Not specified' }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Expiry Date</div>
            <div class="detail-value">{{ $document->expiry_date ? $document->expiry_date->format('F d, Y') : 'No expiry' }}</div>
        </div>
    </div>

    @if($document->file_path || $document->scanned_image_path)
    <div class="file-section">
        <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 16px;">Attached Files</h3>
        
        @if($document->file_path)
        <div class="file-card">
            <div class="file-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
            </div>
            <div class="file-info">
                <div class="file-name">{{ $document->original_filename ?? 'Document File' }}</div>
                <div class="file-meta">{{ $document->formatted_file_size ?? 'Unknown size' }}</div>
            </div>
            <a href="{{ route('documents.download', $document) }}" class="btn btn-download" style="padding: 6px 12px; font-size: 12px;">Download</a>
        </div>
        @endif

        @if($document->scanned_image_path)
        <div class="file-card">
            <div class="file-icon" style="background: #fce7f3; color: #db2777;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
            </div>
            <div class="file-info">
                <div class="file-name">Scanned Image</div>
                <div class="file-meta">Image scan attachment</div>
            </div>
            <a href="{{ Storage::url($document->scanned_image_path) }}" target="_blank" class="btn btn-back" style="padding: 6px 12px; font-size: 12px;">View Scan</a>
        </div>
        @endif
    </div>
    @endif

    <div class="action-buttons">
        <a href="{{ route('documents.index') }}" class="btn btn-back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Documents
        </a>
        <a href="{{ route('documents.edit', $document) }}" class="btn btn-edit">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            Edit Document
        </a>
    </div>
</div>
@endsection
