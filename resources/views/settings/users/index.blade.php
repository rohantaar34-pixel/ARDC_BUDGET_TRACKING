@extends('layouts.app')

@section('content')
<style>
    .settings-container {
        padding: 24px;
        background: #f8f8fb;
        min-height: calc(100vh - 80px);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .top-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
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
        font-weight: 800;
        color: #111827;
        margin: 0;
    }
    .page-subtitle {
        color: #6b7280;
        font-size: 14px;
        margin-top: 4px;
    }
    .btn-primary {
        background: #4f46e5;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        border: none;
    }
    .btn-primary:hover { background: #4338ca; }
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
    }
    .table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th {
        background: #f9fafb;
        padding: 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 800;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    td {
        padding: 16px;
        border-top: 1px solid #e5e7eb;
        color: #111827;
        font-size: 14px;
        vertical-align: middle;
    }
    .user-avatar {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #eef2ff;
        color: #4f46e5;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        margin-right: 10px;
    }
    .user-cell {
        display: flex;
        align-items: center;
    }
    .muted { color: #6b7280; font-size: 13px; }
    .current-badge {
        display: inline-flex;
        padding: 4px 8px;
        border-radius: 999px;
        background: #dcfce7;
        color: #166534;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .action-row {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .action-btn {
        padding: 7px 12px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        border: none;
    }
    .btn-edit {
        background: #eef2ff;
        color: #4f46e5;
    }
    .btn-delete {
        background: #fee2e2;
        color: #dc2626;
    }
    .modal {
        display: none;
        position: fixed;
        z-index: 50;
        inset: 0;
        background-color: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-content {
        background: white;
        padding: 24px;
        border-radius: 12px;
        width: 100%;
        max-width: 520px;
    }
    .modal-header {
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 16px;
    }
    .form-group {
        margin-bottom: 16px;
    }
    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-size: 14px;
        font-weight: 700;
        color: #374151;
    }
    .form-group input, .form-group select {
        width: 100%;
        padding: 11px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        font-family: inherit;
    }
    .project-checks {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 8px;
        padding: 10px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        max-height: 180px;
        overflow: auto;
    }
    .project-check {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
    }
    .project-check input[type="checkbox"] {
        width: 18px;
        height: 18px;
        padding: 0;
        appearance: auto;
        -webkit-appearance: checkbox;
        accent-color: #4f46e5;
        flex: 0 0 auto;
        cursor: pointer;
    }
    .project-check { cursor: pointer; }
    .role-badge {
        display: inline-flex;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .role-admin { background: #fee2e2; color: #991b1b; }
    .role-employee { background: #eef2ff; color: #3730a3; }
    .help-text {
        color: #6b7280;
        font-size: 12px;
        margin-top: 6px;
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
        font-weight: 700;
        cursor: pointer;
    }
    @media (max-width: 720px) {
        .settings-container { padding: 16px; }
        .table-container { overflow-x: auto; }
        table { min-width: 760px; }
    }
</style>

<div class="settings-container">
    <div class="top-actions">
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
        <button class="btn-primary" onclick="openAddModal()">+ Add User</button>
    </div>

    <div class="settings-tabs">
        <a href="{{ route('settings.projects.index') }}" class="settings-tab">Projects</a>
        <a href="{{ route('settings.users.index') }}" class="settings-tab active">Users</a>
    </div>

    <div style="margin-bottom: 20px;">
        <h1 class="page-title">User Management</h1>
        <div class="page-subtitle">Create and maintain accounts that can access the system.</div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Assigned Projects</th>
                    <th>Created</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="user-cell">
                                <span class="user-avatar">{{ Str::upper(Str::substr($user->name, 0, 1)) }}</span>
                                <div>
                                    <div style="font-weight: 800;">{{ $user->name }}</div>
                                    <div class="muted">ID #{{ $user->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="role-badge role-{{ $user->role }}">{{ ucfirst($user->role) }}</span>
                        </td>
                        <td class="muted">
                            @if($user->isEmployee())
                                {{ $user->assignedProjects->pluck('name')->join(', ') ?: 'No assignments' }}
                            @else
                                All projects
                            @endif
                        </td>
                        <td>{{ $user->created_at?->format('M d, Y') ?? '-' }}</td>
                        <td>
                            @if(Auth::id() === $user->id)
                                <span class="current-badge">Current User</span>
                            @else
                                <span class="muted">Active</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-row">
                                <button
                                    type="button"
                                    class="action-btn btn-edit"
                                    onclick="openEditModal({{ $user->id }}, @js($user->name), @js($user->email), @js($user->role), @js($user->assignedProjects->pluck('id')->values()->all()))"
                                >
                                    Edit
                                </button>
                                <form action="{{ route('settings.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete user {{ addslashes($user->name) }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn btn-delete" @disabled(Auth::id() === $user->id)>Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #6b7280;">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Add User</div>
        <form action="{{ route('settings.users.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" required>
                <div class="help-text">Minimum 6 characters.</div>
            </div>
            <div class="form-group">
                <label>Confirm Password *</label>
                <input type="password" name="password_confirmation" required>
            </div>
            <div class="form-group">
                <label>Role *</label>
                <select name="role" id="addRole" onchange="toggleProjectAssignments('add')" required>
                    <option value="employee" @selected(old('role') === 'employee')>Employee</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                </select>
            </div>
            <div class="form-group" id="addProjectAssignments">
                <label>Assigned Projects</label>
                <div class="project-checks">
                    @forelse($projects as $project)
                        <label class="project-check">
                            <input type="checkbox" name="project_ids[]" value="{{ $project->id }}" @checked(in_array($project->id, old('project_ids', [])))>
                            {{ $project->name }}
                        </label>
                    @empty
                        <span class="muted">Create projects before assigning employees.</span>
                    @endforelse
                </div>
                <div class="help-text">Employees only see monitoring projects assigned here.</div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeModals()">Cancel</button>
                <button type="submit" class="btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Edit User</div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" id="editName" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" id="editEmail" required>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password">
                <div class="help-text">Leave blank to keep the current password.</div>
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="password_confirmation">
            </div>
            <div class="form-group">
                <label>Role *</label>
                <select name="role" id="editRole" onchange="toggleProjectAssignments('edit')" required>
                    <option value="employee">Employee</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group" id="editProjectAssignments">
                <label>Assigned Projects</label>
                <div class="project-checks">
                    @forelse($projects as $project)
                        <label class="project-check">
                            <input type="checkbox" name="project_ids[]" value="{{ $project->id }}" data-edit-project>
                            {{ $project->name }}
                        </label>
                    @empty
                        <span class="muted">Create projects before assigning employees.</span>
                    @endforelse
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeModals()">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('addModal').style.display = 'flex';
    }

    function toggleProjectAssignments(prefix) {
        const role = document.getElementById(prefix + 'Role').value;
        document.getElementById(prefix + 'ProjectAssignments').style.display = role === 'employee' ? 'block' : 'none';
    }

    function openEditModal(id, name, email, role, projectIds) {
        const form = document.getElementById('editForm');
        form.action = '{{ url('/settings/users') }}/' + id;
        document.getElementById('editName').value = name;
        document.getElementById('editEmail').value = email;
        document.getElementById('editRole').value = role;
        const assignedProjectIds = Array.isArray(projectIds) ? projectIds.map(Number) : Object.values(projectIds).map(Number);
        document.querySelectorAll('[data-edit-project]').forEach(input => {
            input.checked = assignedProjectIds.includes(Number(input.value));
        });
        toggleProjectAssignments('edit');
        document.getElementById('editModal').style.display = 'flex';
    }

    function closeModals() {
        document.getElementById('addModal').style.display = 'none';
        document.getElementById('editModal').style.display = 'none';
    }

    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            closeModals();
        }
    });

    @if($errors->any())
        openAddModal();
    @endif
    toggleProjectAssignments('add');
</script>
@endsection
