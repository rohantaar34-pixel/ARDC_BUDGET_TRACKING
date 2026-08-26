@extends('layouts.app')

@section('content')
    <style>
        .form-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
        }

        .btn-submit {
            background: #0f766e;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-cancel {
            background: #e5e7eb;
            color: #374151;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            margin-left: 12px;
        }

        .current-file {
            background: #f3f4f6;
            padding: 8px;
            border-radius: 6px;
            margin-top: 8px;
            font-size: 12px;
        }
    </style>

    <div class="form-container">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:24px;">
            <a href="{{ $document->project_id ? route('documents.project', [$document->project_id, 'folder_id' => $document->folder_id ?? 'none']) : route('documents.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:#f3f4f6;color:#374151;border-radius:8px;text-decoration:none;font-weight:600;font-size:13px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back
            </a>
            <h1 style="font-size: 22px; font-weight: 700; margin:0;">Edit Document: {{ $document->document_number }}</h1>
        </div>

        <form method="POST" action="{{ route('documents.update', $document) }}" enctype="multipart/form-data" onsubmit="const b=this.querySelector('.btn-submit'); if(b){b.disabled=true; b.innerText='Updating Document...';}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" value="{{ old('title', $document->title) }}" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3">{{ old('description', $document->description) }}</textarea>
            </div>

            <div class="form-group">
                <label>Document Type *</label>
                <select name="document_type" required>
                    <option value="contract" {{ $document->document_type == 'contract' ? 'selected' : '' }}>Contract</option>
                    <option value="invoice" {{ $document->document_type == 'invoice' ? 'selected' : '' }}>Invoice</option>
                    <option value="report" {{ $document->document_type == 'report' ? 'selected' : '' }}>Report</option>
                    <option value="other" {{ $document->document_type == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category">
                    <option value="">Select Category</option>
                    <option value="war" {{ $document->category == 'war' ? 'selected' : '' }}>WAR – Weekly Accomplishment Report</option>
                    <option value="dar" {{ $document->category == 'dar' ? 'selected' : '' }}>DAR – Daily Accomplishment Report</option>
                    <option value="financial" {{ $document->category == 'financial' ? 'selected' : '' }}>Financial</option>
                    <option value="legal" {{ $document->category == 'legal' ? 'selected' : '' }}>Legal</option>
                    <option value="technical" {{ $document->category == 'technical' ? 'selected' : '' }}>Technical</option>
                    <option value="administrative" {{ $document->category == 'administrative' ? 'selected' : '' }}>Administrative</option>
                    <option value="other" {{ $document->category == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label>Associated Project</label>
                <select name="project_id">
                    <option value="">None</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" {{ $document->project_id == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Folder / Location</label>
                <select name="folder_id">
                    <option value="">— Main Directory (Root / No Folder) —</option>
                    @foreach ($folders as $folder)
                        <option value="{{ $folder->id }}" {{ old('folder_id', $document->folder_id) == $folder->id ? 'selected' : '' }}>
                            {{ $folder->name }} {{ $folder->project ? '('.$folder->project->name.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" required>
                    <option value="active" {{ $document->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="archived" {{ $document->status == 'archived' ? 'selected' : '' }}>Archived</option>
                    <option value="expired" {{ $document->status == 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>

            <div class="form-group">
                <label>Document Date</label>
                <input type="date" name="document_date"
                    value="{{ old('document_date', $document->document_date ? $document->document_date->format('Y-m-d') : '') }}">
            </div>

            <div class="form-group">
                <label>Expiry Date</label>
                <input type="date" name="expiry_date"
                    value="{{ old('expiry_date', $document->expiry_date ? $document->expiry_date->format('Y-m-d') : '') }}">
            </div>

            @if ($document->file_path)
                <div class="form-group">
                    <label>Current Document File</label>
                    <div class="current-file">
                        {{ $document->original_filename }} ({{ $document->formatted_file_size }})
                    </div>
                    <small>Upload a new file to replace it (optional)</small>
                </div>
            @endif

            <div class="form-group">
                <label>Replace Document File (Optional)</label>
                <input type="file" name="document_file" accept=".pdf,.doc,.docx,.xls,.xlsx">
            </div>

            @if ($document->scanned_image_path)
                <div class="form-group">
                    <label>Current Scanned Image</label>
                    <div class="current-file">
                        Current image file is stored
                    </div>
                </div>
            @endif

            <div class="form-group">
                <label>Replace Scanned Image (Optional)</label>
                <input type="file" name="scanned_image" accept="image/*">
            </div>

            <div>
                <button type="submit" class="btn-submit">Update Document</button>
                <a href="{{ route('documents.show', $document) }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
