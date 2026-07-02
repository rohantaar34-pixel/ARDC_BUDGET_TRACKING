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
            background: #4f46e5;
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
    </style>

    <div class="form-container">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:24px;">
            <a href="{{ route('documents.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:#f3f4f6;color:#374151;border-radius:8px;text-decoration:none;font-weight:600;font-size:13px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back
            </a>
            <h1 style="font-size: 22px; font-weight: 700; margin:0;">Add New Document</h1>
        </div>

        <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label>Document Number</label>
                <input type="text" name="document_number" value="{{ $documentNumber }}" readonly>
                <small style="color: #6b7280;">Auto-generated document ID</small>
            </div>

            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label>Document Type *</label>
                <select name="document_type" required>
                    <option value="contract">Contract</option>
                    <option value="invoice">Invoice</option>
                    <option value="report">Report</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category">
                    <option value="">Select Category</option>
                    <option value="financial">Financial</option>
                    <option value="legal">Legal</option>
                    <option value="technical">Technical</option>
                    <option value="administrative">Administrative</option>
                </select>
            </div>

            <div class="form-group">
                <label>Associated Project (Optional)</label>
                <select name="project_id">
                    <option value="">None</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" {{ (request('project_id') == $project->id || old('project_id') == $project->id) ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Document Date</label>
                <input type="date" name="document_date">
            </div>

            <div class="form-group">
                <label>Expiry Date (If applicable)</label>
                <input type="date" name="expiry_date">
            </div>

            <div class="form-group">
                <label>Upload Document File (PDF, DOC, etc.)</label>
                <input type="file" name="document_file" accept=".pdf,.doc,.docx,.xls,.xlsx">
                <small>Max size: 10MB</small>
            </div>

            <div class="form-group">
                <label>Upload Scanned Image</label>
                <input type="file" name="scanned_image" accept="image/*">
                <small>Max size: 5MB</small>
            </div>

            <div>
                <button type="submit" class="btn-submit">Save Document</button>
                <a href="{{ route('documents.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
