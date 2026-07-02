@extends('layouts.app')

@section('content')
<style>
    .settings-container {
        padding: 24px;
        background: #f8f8fb;
        min-height: calc(100vh - 80px);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    .settings-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .settings-tab {
        display: inline-flex;
        align-items: center;
        padding: 9px 14px;
        border-radius: 8px;
        background: #fff;
        border: 1px solid #e5e7eb;
        color: #374151;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
    }
    .settings-tab.active {
        background: #4f46e5;
        color: #fff;
        border-color: #4f46e5;
    }
    .page-title {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
    }
    .btn-primary {
        background: #4f46e5;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        border: none;
    }
    .btn-primary:hover {
        background: #4338ca;
    }
    .table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th {
        background: #f9fafb;
        padding: 16px;
        text-align: left;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    td {
        padding: 16px;
        border-top: 1px solid #e5e7eb;
        color: #111827;
        font-size: 14px;
    }
    .action-btn {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        border: none;
    }
    .btn-delete {
        background: #fee2e2;
        color: #dc2626;
    }
    .btn-delete:hover {
        background: #fecaca;
    }
    .modal {
        display: none;
        position: fixed;
        z-index: 50;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
    }
    .modal-content {
        background: white;
        padding: 24px;
        border-radius: 12px;
        width: 100%;
        max-width: 500px;
    }
    .modal-header {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 16px;
    }
    .form-group {
        margin-bottom: 16px;
    }
    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
    }
    .form-group input, .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-family: inherit;
    }
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 24px;
    }
    .btn-cancel {
        background: white;
        border: 1px solid #d1d5db;
        color: #374151;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
    }
    .btn-dashboard-enhanced {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 20px;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
        margin-bottom: 24px;
    }
    .btn-dashboard-enhanced:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    }
</style>

<div class="settings-container">
    <a href="{{ route('dashboard') }}" class="btn-dashboard-enhanced">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round">
            <rect x="3" y="3" width="7" height="7" />
            <rect x="14" y="3" width="7" height="7" />
            <rect x="14" y="14" width="7" height="7" />
            <rect x="3" y="14" width="7" height="7" />
        </svg>
        Dashboard
    </a>

    <div class="settings-tabs">
        <a href="{{ route('settings.projects.index') }}" class="settings-tab active">Projects</a>
        <a href="{{ route('settings.users.index') }}" class="settings-tab">Users</a>
    </div>

    <div class="header-flex">
        <h1 class="page-title">Project Settings</h1>
        <button class="btn-primary" onclick="openModal()">+ Add New Project</button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Initial Budget</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                    <tr>
                        <td style="font-weight: 600;">{{ $project->name }}</td>
                        <td style="color: #6b7280;">{{ Str::limit($project->description, 50) ?: 'N/A' }}</td>
                        <td>₱{{ number_format($project->budget, 2) }}</td>
                        <td>
                            <span style="background: #dbeafe; color: #1e40af; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                {{ ucfirst($project->status) }}
                            </span>
                        </td>
                        <td>
                            <form action="{{ route('settings.projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this project? This will also delete all associated documents and files in the Document Tracker!');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #6b7280;">No projects found. Add one to get started.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Project Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Add New Project</div>
        <form action="{{ route('settings.projects.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Project Name *</label>
                <input type="text" name="name" required placeholder="e.g. Website Redesign">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="What is this for?"></textarea>
            </div>
            <div class="form-group">
                <label>Initial Budget *</label>
                <input type="number" step="0.01" name="budget" required placeholder="0.00">
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-primary">Create Project</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('addModal').style.display = 'flex';
    }
    function closeModal() {
        document.getElementById('addModal').style.display = 'none';
    }
    window.onclick = function(event) {
        if (event.target == document.getElementById('addModal')) {
            closeModal();
        }
    }
</script>
@endsection
