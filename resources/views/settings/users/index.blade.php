@extends('layouts.app')

@php
    $addInitialRoleId = (string) old('access_role_id', $roles->first()?->id ?? '');
    $addOldModulePermissions = collect(old('module_permissions', $roleDefaults[$addInitialRoleId] ?? []))
        ->map(fn ($module) => (string) $module)
        ->values()
        ->all();
    $isEditError = old('_method') === 'PUT';
    $editModalUser = $isEditError ? $users->firstWhere('id', (int) old('modal_user_id')) : null;
    $editOldProjectIds = collect(old('project_ids', $editModalUser?->assignedProjects->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->values()
        ->all();
    $editOldModulePermissions = collect(old('module_permissions', $editModalUser?->resolvedModulePermissions() ?? []))
        ->map(fn ($module) => (string) $module)
        ->values()
        ->all();
@endphp

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
        background: #1d4ed8;
        color: #fff;
        border-color: #1d4ed8;
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
        background: #0f766e;
        color: #fff;
        padding: 10px 20px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        border: none;
        transition: background .2s ease, transform .2s ease;
    }

    .btn-primary:hover {
        background: #0b5f58;
        transform: translateY(-1px);
    }

    .btn-dashboard-enhanced {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 20px;
        background: linear-gradient(135deg, #2563eb 0%, #0f9f8f 100%);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        transition: all .3s ease;
        box-shadow: 0 2px 8px rgba(15, 159, 143, .3);
    }

    .table-container {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
        overflow-x: auto;
        border: 1px solid #e5e7eb;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1260px;
    }

    th {
        background: #f9fafb;
        padding: 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 800;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .05em;
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
        color: #1d4ed8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        margin-right: 10px;
        flex: 0 0 auto;
    }

    .user-cell {
        display: flex;
        align-items: center;
        min-width: 220px;
    }

    .muted {
        color: #6b7280;
        font-size: 13px;
    }

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
        flex-wrap: wrap;
    }

    .action-btn {
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        border: none;
        transition: background .2s ease, color .2s ease, opacity .2s ease;
    }

    .action-btn:disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    .btn-edit {
        background: #eef2ff;
        color: #0f766e;
    }

    .btn-delete {
        background: #fee2e2;
        color: #dc2626;
    }

    .role-badge {
        display: inline-flex;
        padding: 4px 8px;
        border-radius: 999px;
        background: #e0e7ff;
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 50;
        inset: 0;
        background: rgba(15, 23, 42, .55);
        backdrop-filter: blur(4px);
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .modal-card {
        width: min(100%, 760px);
        max-height: calc(100vh - 32px);
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, .22);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .modal-card.compact {
        width: min(100%, 520px);
    }

    .modal-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 22px 24px 14px;
        border-bottom: 1px solid #eef2f7;
    }

    .modal-title {
        font-size: 22px;
        font-weight: 800;
        color: #111827;
        margin: 0;
    }

    .modal-subtitle {
        color: #6b7280;
        font-size: 14px;
        margin-top: 6px;
    }

    .modal-close {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #4b5563;
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
        flex: 0 0 auto;
    }

    .modal-form {
        padding: 20px 24px 24px;
        overflow-y: auto;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .field-span-2 {
        grid-column: 1 / -1;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        font-weight: 700;
        color: #374151;
    }

    .field-input,
    .field-select {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-family: inherit;
        font-size: 15px;
        color: #111827;
        background: #fff;
        transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .field-input:focus,
    .field-select:focus {
        outline: none;
        border-color: #0f9f8f;
        box-shadow: 0 0 0 4px rgba(15, 159, 143, .12);
    }

    .field-input.is-invalid,
    .field-select.is-invalid,
    .project-checks.is-invalid,
    .module-grid.is-invalid {
        border-color: #ef4444;
        background: #fff5f5;
    }

    .input-shell {
        position: relative;
    }

    .field-input.with-toggle {
        padding-right: 76px;
    }

    .field-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: transparent;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        padding: 4px 6px;
    }

    .field-error {
        min-height: 18px;
        margin-top: 6px;
        color: #dc2626;
        font-size: 12px;
        font-weight: 600;
    }

    .field-hint {
        color: #6b7280;
        font-size: 12px;
        margin-top: 6px;
        line-height: 1.5;
    }

    .password-strength {
        margin-top: 10px;
    }

    .strength-track {
        width: 100%;
        height: 8px;
        border-radius: 999px;
        background: #e5e7eb;
        overflow: hidden;
    }

    .strength-fill {
        height: 100%;
        width: 0;
        border-radius: 999px;
        transition: width .2s ease, background .2s ease;
    }

    .strength-fill.weak { background: #ef4444; }
    .strength-fill.fair { background: #f59e0b; }
    .strength-fill.good { background: #3b82f6; }
    .strength-fill.strong { background: #16a34a; }

    .strength-copy {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-top: 8px;
        font-size: 12px;
        color: #475569;
    }

    .strength-label {
        font-weight: 800;
        color: #111827;
    }

    .password-rules {
        display: grid;
        gap: 6px;
        margin: 10px 0 0;
        padding: 0;
        list-style: none;
    }

    .password-rule {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
    }

    .password-rule::before {
        content: '*';
        color: #cbd5e1;
        font-size: 14px;
    }

    .password-rule.satisfied {
        color: #166534;
    }

    .password-rule.satisfied::before {
        content: 'OK';
        color: #16a34a;
        font-weight: 800;
        font-size: 11px;
    }

    .module-shell {
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 14px;
        background: #fff;
    }

    .module-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 12px;
        border-radius: 10px;
    }

    .module-option {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #f8fafc;
        cursor: pointer;
        transition: border-color .2s ease, background .2s ease;
        position: relative;
        min-height: 112px;
    }

    .module-option:hover {
        border-color: #c7d2fe;
        background: #eef2ff;
    }

    .module-option:has(input:checked) {
        border-color: #0f9f8f;
        background: #eef2ff;
        box-shadow: 0 0 0 1px rgba(29, 78, 216, .08);
    }

    .module-option.is-disabled {
        cursor: not-allowed;
        background: #f8fafc;
        border-style: dashed;
        opacity: .58;
    }

    .module-option.is-disabled:hover {
        border-color: #e5e7eb;
        background: #f8fafc;
    }

    .module-option input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin-top: 2px;
        appearance: auto;
        -webkit-appearance: checkbox;
        accent-color: #0f9f8f;
        flex: 0 0 auto;
        cursor: pointer;
    }

    .module-copy {
        display: grid;
        gap: 4px;
    }

    .module-title {
        font-size: 13px;
        font-weight: 800;
        color: #111827;
    }

    .module-description {
        font-size: 12px;
        line-height: 1.5;
        color: #64748b;
    }

    .module-access-note {
        display: inline-flex;
        width: fit-content;
        margin-top: 4px;
        padding: 3px 7px;
        border-radius: 999px;
        background: #e2e8f0;
        color: #475569;
        font-size: 10px;
        line-height: 1.2;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .module-option:has(input:checked) .module-access-note {
        background: #c7d2fe;
        color: #0f766e;
    }

    .module-empty {
        font-size: 13px;
        color: #6b7280;
    }

    .project-checks {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
        padding: 12px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        max-height: 180px;
        overflow: auto;
        background: #fff;
    }

    .project-check {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        padding: 4px 2px;
    }

    .project-check input[type="checkbox"] {
        width: 18px;
        height: 18px;
        padding: 0;
        appearance: auto;
        -webkit-appearance: checkbox;
        accent-color: #0f9f8f;
        flex: 0 0 auto;
        cursor: pointer;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 28px;
        padding-top: 18px;
        border-top: 1px solid #eef2f7;
    }

    .btn-cancel {
        background: #fff;
        border: 1px solid #d1d5db;
        color: #374151;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 700;
        cursor: pointer;
    }

    .modal-text {
        color: #4b5563;
        font-size: 14px;
        line-height: 1.7;
        padding: 4px 24px 0;
    }

    .modal-text strong {
        color: #111827;
    }

    @media (max-width: 900px) {
        .settings-container {
            padding: 16px;
        }

        .top-actions {
            align-items: stretch;
        }

        .top-actions > * {
            width: 100%;
        }

        .btn-primary,
        .btn-dashboard-enhanced {
            justify-content: center;
        }
    }

    @media (max-width: 720px) {
        .page-title {
            font-size: 21px;
        }

        .modal {
            padding: 10px;
            align-items: flex-end;
        }

        .modal-card,
        .modal-card.compact {
            width: 100%;
            max-height: calc(100vh - 12px);
            border-radius: 20px 20px 14px 14px;
        }

        .modal-head {
            padding: 18px 18px 12px;
        }

        .modal-form {
            padding: 16px 18px 18px;
        }

        .form-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .module-grid,
        .project-checks {
            grid-template-columns: 1fr;
        }

        .modal-footer {
            flex-direction: column-reverse;
        }

        .modal-footer button {
            width: 100%;
        }
    }
</style>

<div class="settings-container">
    <div class="top-actions">
        <a href="{{ route('dashboard') }}" class="btn-dashboard-enhanced">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <rect x="3" y="3" width="7" height="7" />
                <rect x="14" y="3" width="7" height="7" />
                <rect x="14" y="14" width="7" height="7" />
                <rect x="3" y="14" width="7" height="7" />
            </svg>
            Dashboard
        </a>
        <button class="btn-primary" type="button" onclick="openAddModal()">+ Add User</button>
    </div>

    <div class="settings-tabs">
        @if(Auth::user()->canManageProjectsSettings())
            <a href="{{ route('settings.projects.index') }}" class="settings-tab">Projects</a>
        @endif
        <a href="{{ route('settings.users.index') }}" class="settings-tab active">Users</a>
        <a href="{{ route('settings.roles.index') }}" class="settings-tab">Roles</a>
    </div>

    <div style="margin-bottom: 20px;">
        <h1 class="page-title">User Management</h1>
        <div class="page-subtitle">Create user accounts, set positions, and control which modules each account can handle.</div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Position</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Modules</th>
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
                        <td>{{ $user->displayPosition() }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="role-badge">{{ $user->displayRole() }}</span>
                        </td>
                        <td class="muted">{{ $user->moduleSummary() }}</td>
                        <td class="muted">
                            @if($user->isEmployee())
                                {{ $user->assignedProjects->pluck('name')->join(', ') ?: 'No assignments' }}
                            @else
                                Role-based operational access
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
                                    data-edit-user="{{ json_encode([
                                        "id" => $user->id,
                                        "name" => $user->name,
                                        "position" => $user->position,
                                        "email" => $user->email,
                                        "accessRoleId" => $user->access_role_id,
                                        "projectIds" => $user->assignedProjects->pluck("id")->values()->all(),
                                        "modulePermissions" => $user->resolvedModulePermissions(),
                                    ]) }}"
                                >
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    class="action-btn btn-delete"
                                    onclick="openDeleteModal(@js(route('settings.users.destroy', $user)), @js('Delete user \"' . $user->name . '\"? This cannot be undone.'))"
                                    @disabled(Auth::id() === $user->id)
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: #6b7280;">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="addModal" class="modal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-head">
            <div>
                <h2 class="modal-title">Add User</h2>
                <div class="modal-subtitle">Create a new account with a position, module access, assigned projects, and a strong password.</div>
            </div>
            <button type="button" class="modal-close" onclick="closeModals()" aria-label="Close add user modal">&times;</button>
        </div>
        <form id="addUserForm" class="modal-form" action="{{ route('settings.users.store') }}" method="POST" novalidate>
            @csrf
            <div class="form-grid">
                <div class="form-group field-span-2">
                    <label for="addName">Name *</label>
                    <input id="addName" class="field-input" type="text" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="255" required>
                    <div class="field-error" id="addNameError">@if(!$isEditError) @error('name'){{ $message }}@enderror @endif</div>
                </div>

                <div class="form-group">
                    <label for="addPosition">Position *</label>
                    <input id="addPosition" class="field-input" type="text" name="position" value="{{ old('position') }}" autocomplete="organization-title" maxlength="255" placeholder="Project Engineer, Accountant, Site Supervisor" required>
                    <div class="field-error" id="addPositionError">@if(!$isEditError) @error('position'){{ $message }}@enderror @endif</div>
                </div>

                <div class="form-group">
                    <label for="addRole">Role *</label>
                    <select name="access_role_id" id="addRole" class="field-select" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected($addInitialRoleId === (string) $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <div class="field-error" id="addRoleError">@if(!$isEditError) @error('access_role_id'){{ $message }}@enderror @endif</div>
                    <div class="field-hint"><a href="{{ route('settings.roles.index') }}">Create or edit roles</a></div>
                </div>

                <div class="form-group field-span-2">
                    <label for="addEmail">Email *</label>
                    <input id="addEmail" class="field-input" type="email" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required>
                    <div class="field-error" id="addEmailError">@if(!$isEditError) @error('email'){{ $message }}@enderror @endif</div>
                </div>

                <div class="form-group field-span-2">
                    <label>Module Access *</label>
                    <div class="module-shell">
                        <div class="module-grid" id="addModuleChecks"></div>
                    </div>
                    <div class="field-error" id="addModulesError">@if(!$isEditError) @error('module_permissions'){{ $message }}@enderror @endif</div>
                    <div class="field-hint" id="addModulesHint">Role defaults are preselected. You can check or uncheck any module for this account.</div>
                </div>

                <div class="form-group field-span-2" id="addProjectAssignments">
                    <label>Assigned Projects</label>
                    <div class="project-checks" id="addProjectChecks">
                        @forelse($projects as $project)
                            <label class="project-check">
                                <input
                                    type="checkbox"
                                    name="project_ids[]"
                                    value="{{ $project->id }}"
                                    @checked(in_array($project->id, old('project_ids', [])))
                                >
                                {{ $project->name }}
                            </label>
                        @empty
                            <span class="muted">Create projects before assigning employees.</span>
                        @endforelse
                    </div>
                    <div class="field-error" id="addProjectsError">@if(!$isEditError) @error('project_ids'){{ $message }}@enderror @endif</div>
                    <div class="field-hint">Employees need assigned projects for monitoring submissions and material requests.</div>
                </div>

                <div class="form-group">
                    <label for="addPassword">Password *</label>
                    <div class="input-shell">
                        <input id="addPassword" class="field-input with-toggle" type="password" name="password" autocomplete="new-password" required>
                        <button type="button" class="field-toggle" data-toggle-target="addPassword">Show</button>
                    </div>
                    <div class="field-error" id="addPasswordError">@if(!$isEditError) @error('password'){{ $message }}@enderror @endif</div>
                    <div class="field-hint">Use at least 8 characters with uppercase, lowercase, number, and symbol.</div>
                    <div class="password-strength" id="addPasswordStrength" hidden>
                        <div class="strength-track">
                            <div class="strength-fill" id="addPasswordStrengthFill"></div>
                        </div>
                        <div class="strength-copy">
                            <span class="strength-label" id="addPasswordStrengthLabel">Too weak</span>
                            <span id="addPasswordStrengthMeta">0 of 5 checks passed</span>
                        </div>
                    </div>
                    <ul class="password-rules" id="addPasswordRules" hidden>
                        <li class="password-rule" data-rule="length">At least 8 characters</li>
                        <li class="password-rule" data-rule="lower">At least one lowercase letter</li>
                        <li class="password-rule" data-rule="upper">At least one uppercase letter</li>
                        <li class="password-rule" data-rule="number">At least one number</li>
                        <li class="password-rule" data-rule="symbol">At least one symbol</li>
                    </ul>
                </div>

                <div class="form-group">
                    <label for="addPasswordConfirmation">Confirm Password *</label>
                    <div class="input-shell">
                        <input id="addPasswordConfirmation" class="field-input with-toggle" type="password" name="password_confirmation" autocomplete="new-password" required>
                        <button type="button" class="field-toggle" data-toggle-target="addPasswordConfirmation">Show</button>
                    </div>
                    <div class="field-error" id="addPasswordConfirmationError"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModals()">Cancel</button>
                <button type="submit" class="btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="modal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-head">
            <div>
                <h2 class="modal-title">Edit User</h2>
                <div class="modal-subtitle">Update the user position, assigned modules, projects, and account password.</div>
            </div>
            <button type="button" class="modal-close" onclick="closeModals()" aria-label="Close edit user modal">&times;</button>
        </div>
        <form id="editForm" class="modal-form" method="POST" novalidate>
            @csrf
            @method('PUT')
            <input type="hidden" name="modal_user_id" id="editModalUserId" value="{{ $isEditError ? old('modal_user_id') : '' }}">
            <div class="form-grid">
                <div class="form-group field-span-2">
                    <label for="editName">Name *</label>
                    <input id="editName" class="field-input" type="text" name="name" value="{{ $isEditError ? old('name') : '' }}" autocomplete="name" maxlength="255" required>
                    <div class="field-error" id="editNameError">@if($isEditError) @error('name'){{ $message }}@enderror @endif</div>
                </div>

                <div class="form-group">
                    <label for="editPosition">Position *</label>
                    <input id="editPosition" class="field-input" type="text" name="position" value="{{ $isEditError ? old('position') : '' }}" autocomplete="organization-title" maxlength="255" placeholder="Project Engineer, Accountant, Site Supervisor" required>
                    <div class="field-error" id="editPositionError">@if($isEditError) @error('position'){{ $message }}@enderror @endif</div>
                </div>

                <div class="form-group">
                    <label for="editRole">Role *</label>
                    <select name="access_role_id" id="editRole" class="field-select" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <div class="field-error" id="editRoleError">@if($isEditError) @error('access_role_id'){{ $message }}@enderror @endif</div>
                    <div class="field-hint"><a href="{{ route('settings.roles.index') }}">Create or edit roles</a></div>
                </div>

                <div class="form-group field-span-2">
                    <label for="editEmail">Email *</label>
                    <input id="editEmail" class="field-input" type="email" name="email" value="{{ $isEditError ? old('email') : '' }}" autocomplete="email" maxlength="255" required>
                    <div class="field-error" id="editEmailError">@if($isEditError) @error('email'){{ $message }}@enderror @endif</div>
                </div>

                <div class="form-group field-span-2">
                    <label>Module Access *</label>
                    <div class="module-shell">
                        <div class="module-grid" id="editModuleChecks"></div>
                    </div>
                    <div class="field-error" id="editModulesError">@if($isEditError) @error('module_permissions'){{ $message }}@enderror @endif</div>
                    <div class="field-hint" id="editModulesHint">Checked cards are the only dashboard modules this account can open.</div>
                </div>

                <div class="form-group field-span-2" id="editProjectAssignments">
                    <label>Assigned Projects</label>
                    <div class="project-checks" id="editProjectChecks">
                        @forelse($projects as $project)
                            <label class="project-check">
                                <input
                                    type="checkbox"
                                    name="project_ids[]"
                                    value="{{ $project->id }}"
                                    data-edit-project
                                    @checked(in_array($project->id, $editOldProjectIds, true))
                                >
                                {{ $project->name }}
                            </label>
                        @empty
                            <span class="muted">Create projects before assigning employees.</span>
                        @endforelse
                    </div>
                    <div class="field-error" id="editProjectsError">@if($isEditError) @error('project_ids'){{ $message }}@enderror @endif</div>
                    <div class="field-hint">Employees should keep at least one assigned project.</div>
                </div>

                <div class="form-group">
                    <label for="editPassword">New Password</label>
                    <div class="input-shell">
                        <input id="editPassword" class="field-input with-toggle" type="password" name="password" autocomplete="new-password">
                        <button type="button" class="field-toggle" data-toggle-target="editPassword">Show</button>
                    </div>
                    <div class="field-error" id="editPasswordError">@if($isEditError) @error('password'){{ $message }}@enderror @endif</div>
                    <div class="field-hint">Leave blank to keep the current password. If you change it, the same strength rules apply.</div>
                    <div class="password-strength" id="editPasswordStrength" hidden>
                        <div class="strength-track">
                            <div class="strength-fill" id="editPasswordStrengthFill"></div>
                        </div>
                        <div class="strength-copy">
                            <span class="strength-label" id="editPasswordStrengthLabel">Too weak</span>
                            <span id="editPasswordStrengthMeta">0 of 5 checks passed</span>
                        </div>
                    </div>
                    <ul class="password-rules" id="editPasswordRules" hidden>
                        <li class="password-rule" data-rule="length">At least 8 characters</li>
                        <li class="password-rule" data-rule="lower">At least one lowercase letter</li>
                        <li class="password-rule" data-rule="upper">At least one uppercase letter</li>
                        <li class="password-rule" data-rule="number">At least one number</li>
                        <li class="password-rule" data-rule="symbol">At least one symbol</li>
                    </ul>
                </div>

                <div class="form-group">
                    <label for="editPasswordConfirmation">Confirm New Password</label>
                    <div class="input-shell">
                        <input id="editPasswordConfirmation" class="field-input with-toggle" type="password" name="password_confirmation" autocomplete="new-password">
                        <button type="button" class="field-toggle" data-toggle-target="editPasswordConfirmation">Show</button>
                    </div>
                    <div class="field-error" id="editPasswordConfirmationError"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModals()">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteModal" class="modal" aria-hidden="true">
    <div class="modal-card compact">
        <div class="modal-head">
            <div>
                <h2 class="modal-title">Delete User</h2>
                <div class="modal-subtitle">Confirm this action before the account is removed.</div>
            </div>
            <button type="button" class="modal-close" onclick="closeModals()" aria-label="Close delete user modal">&times;</button>
        </div>
        <div class="modal-text" id="deleteMessage"></div>
        <form id="deleteForm" class="modal-form" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-footer" style="margin-top: 12px;">
                <button type="button" class="btn-cancel" onclick="closeModals()">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #dc2626;">Delete User</button>
            </div>
        </form>
    </div>
</div>

<script>
    const moduleOptionsByRole = @json($moduleOptionsByRole);
    const roleDefaults = @json($roleDefaults);
    const roleWorkflows = @json($roleWorkflows);

    function setModalVisibility(modalId, shouldShow) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.style.display = shouldShow ? 'flex' : 'none';
        modal.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');

        const anyOpen = Array.from(document.querySelectorAll('.modal')).some(item => item.style.display === 'flex');
        document.body.style.overflow = anyOpen ? 'hidden' : '';
    }

    function clearFieldError(inputId, errorId, containerId = null) {
        const input = inputId ? document.getElementById(inputId) : null;
        const error = errorId ? document.getElementById(errorId) : null;
        const container = containerId ? document.getElementById(containerId) : null;

        if (input) {
            input.classList.remove('is-invalid');
            input.setCustomValidity('');
        }

        if (container) {
            container.classList.remove('is-invalid');
        }

        if (error) {
            error.textContent = '';
        }
    }

    function setFieldError(inputId, errorId, message, containerId = null) {
        const input = inputId ? document.getElementById(inputId) : null;
        const error = errorId ? document.getElementById(errorId) : null;
        const container = containerId ? document.getElementById(containerId) : null;

        if (input) {
            input.classList.add('is-invalid');
            input.setCustomValidity(message);
        }

        if (container) {
            container.classList.add('is-invalid');
        }

        if (error) {
            error.textContent = message;
        }
    }

    function passwordScore(value) {
        const checks = {
            length: value.length >= 8,
            lower: /[a-z]/.test(value),
            upper: /[A-Z]/.test(value),
            number: /\d/.test(value),
            symbol: /[^A-Za-z0-9]/.test(value),
        };

        const passed = Object.values(checks).filter(Boolean).length;
        let tone = 'weak';
        let label = 'Too weak';

        if (passed >= 5) {
            tone = 'strong';
            label = 'Strong';
        } else if (passed === 4) {
            tone = 'good';
            label = 'Good';
        } else if (passed === 3) {
            tone = 'fair';
            label = 'Fair';
        } else if (passed >= 1) {
            tone = 'weak';
            label = 'Weak';
        }

        return { checks, passed, tone, label, valid: passed === 5 };
    }

    function updatePasswordMeter(prefix, required) {
        const passwordInput = document.getElementById(prefix + 'Password');
        const strength = document.getElementById(prefix + 'PasswordStrength');
        const fill = document.getElementById(prefix + 'PasswordStrengthFill');
        const label = document.getElementById(prefix + 'PasswordStrengthLabel');
        const meta = document.getElementById(prefix + 'PasswordStrengthMeta');
        const rules = document.getElementById(prefix + 'PasswordRules');
        const value = passwordInput.value;

        if (!value) {
            strength.hidden = true;
            rules.hidden = true;
            fill.style.width = '0';
            fill.className = 'strength-fill';
            label.textContent = 'Too weak';
            meta.textContent = '0 of 5 checks passed';
            return;
        }

        const result = passwordScore(value);
        strength.hidden = false;
        rules.hidden = false;
        fill.className = 'strength-fill ' + result.tone;
        fill.style.width = (result.passed / 5) * 100 + '%';
        label.textContent = result.label;
        meta.textContent = result.passed + ' of 5 checks passed';

        rules.querySelectorAll('[data-rule]').forEach(rule => {
            rule.classList.toggle('satisfied', !!result.checks[rule.dataset.rule]);
        });
    }

    function getCheckedValues(containerId) {
        return Array.from(document.querySelectorAll('#' + containerId + ' input[type="checkbox"]:checked'))
            .map(input => String(input.value));
    }

    function renderModuleOptions(prefix, selectedModules = null) {
        const role = document.getElementById(prefix + 'Role').value;
        const container = document.getElementById(prefix + 'ModuleChecks');
        const hint = document.getElementById(prefix + 'ModulesHint');
        const options = moduleOptionsByRole[role] || [];
        const selected = Array.isArray(selectedModules)
            ? selectedModules.map(String)
            : (roleDefaults[role] || []).map(String);

        hint.textContent = 'Every module is selectable. Checked modules are the only modules this account can open.';

        if (!options.length) {
            container.innerHTML = '<div class="module-empty">No modules available for this role.</div>';
            return;
        }

        container.innerHTML = options.map(option => {
            const checked = selected.includes(option.key) ? 'checked' : '';

            return `
                <label class="module-option module-${option.tone}">
                    <input type="checkbox" name="module_permissions[]" value="${option.key}" ${checked}>
                    <span class="module-copy">
                        <span class="module-title">${option.label}</span>
                        <span class="module-description">${option.description}</span>
                        <span class="module-access-note">Selectable</span>
                    </span>
                </label>
            `;
        }).join('');
    }

    function validateName(prefix) {
        const inputId = prefix + 'Name';
        const errorId = inputId + 'Error';
        const value = document.getElementById(inputId).value.trim();

        clearFieldError(inputId, errorId);

        if (value.length < 2) {
            setFieldError(inputId, errorId, 'Name must be at least 2 characters.');
            return false;
        }

        return true;
    }

    function validatePosition(prefix) {
        const inputId = prefix + 'Position';
        const errorId = inputId + 'Error';
        const value = document.getElementById(inputId).value.trim();

        clearFieldError(inputId, errorId);

        if (value.length < 2) {
            setFieldError(inputId, errorId, 'Position must be at least 2 characters.');
            return false;
        }

        return true;
    }

    function validateEmail(prefix) {
        const inputId = prefix + 'Email';
        const errorId = inputId + 'Error';
        const input = document.getElementById(inputId);
        const value = input.value.trim();

        clearFieldError(inputId, errorId);

        if (!value) {
            setFieldError(inputId, errorId, 'Email is required.');
            return false;
        }

        const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        if (!emailOk) {
            setFieldError(inputId, errorId, 'Enter a valid email address.');
            return false;
        }

        return true;
    }

    function validateModules(prefix) {
        const containerId = prefix + 'ModuleChecks';
        const errorId = prefix + 'ModulesError';
        const checkedValues = getCheckedValues(containerId);

        clearFieldError(null, errorId, containerId);

        if (!checkedValues.length) {
            setFieldError(null, errorId, 'Assign at least one module.', containerId);
            return false;
        }

        return true;
    }

    function validatePassword(prefix, required) {
        const inputId = prefix + 'Password';
        const errorId = inputId + 'Error';
        const value = document.getElementById(inputId).value;

        clearFieldError(inputId, errorId);
        updatePasswordMeter(prefix, required);

        if (!value) {
            if (required) {
                setFieldError(inputId, errorId, 'Password is required.');
                return false;
            }

            return true;
        }

        if (!passwordScore(value).valid) {
            setFieldError(inputId, errorId, 'Use 8+ characters with uppercase, lowercase, number, and symbol.');
            return false;
        }

        return true;
    }

    function validatePasswordConfirmation(prefix, required) {
        const passwordId = prefix + 'Password';
        const confirmId = prefix + 'PasswordConfirmation';
        const errorId = confirmId + 'Error';
        const password = document.getElementById(passwordId).value;
        const confirmation = document.getElementById(confirmId).value;

        clearFieldError(confirmId, errorId);

        if (!confirmation && !password && !required) {
            return true;
        }

        if (required && !confirmation) {
            setFieldError(confirmId, errorId, 'Password confirmation is required.');
            return false;
        }

        if (!password && confirmation) {
            setFieldError(confirmId, errorId, 'Enter a password first.');
            return false;
        }

        if (password !== confirmation) {
            setFieldError(confirmId, errorId, 'Password confirmation does not match.');
            return false;
        }

        return true;
    }

    function validateProjects(prefix) {
        const role = document.getElementById(prefix + 'Role').value;
        const workflow = roleWorkflows[role];
        const checksId = prefix + 'ProjectChecks';
        const errorId = prefix + 'ProjectsError';
        const checkboxes = document.querySelectorAll('#' + checksId + ' input[type="checkbox"]');

        clearFieldError(null, errorId, checksId);

        if (workflow !== 'field_staff') {
            return true;
        }

        const hasSelection = Array.from(checkboxes).some(input => input.checked);
        if (!hasSelection) {
            setFieldError(null, errorId, 'Assign at least one project for field staff.', checksId);
            return false;
        }

        return true;
    }

    function validateUserForm(prefix, requiredPassword) {
        const validators = [
            () => validateName(prefix),
            () => validatePosition(prefix),
            () => validateEmail(prefix),
            () => validateModules(prefix),
            () => validateProjects(prefix),
            () => validatePassword(prefix, requiredPassword),
            () => validatePasswordConfirmation(prefix, requiredPassword),
        ];

        const valid = validators.every(run => run());
        const firstInvalid = document.querySelector(
            (prefix === 'add' ? '#addUserForm ' : '#editForm ') + '.is-invalid'
        );

        if (firstInvalid && typeof firstInvalid.focus === 'function') {
            firstInvalid.focus();
        }

        return valid;
    }

    function toggleProjectAssignments(prefix) {
        const role = document.getElementById(prefix + 'Role').value;
        const workflow = roleWorkflows[role];
        const section = document.getElementById(prefix + 'ProjectAssignments');
        section.style.display = workflow === 'field_staff' ? 'block' : 'none';

        if (workflow !== 'field_staff') {
            clearFieldError(null, prefix + 'ProjectsError', prefix + 'ProjectChecks');
        }
    }

    function syncRoleSections(prefix, selectedModules = null) {
        renderModuleOptions(prefix, selectedModules);
        toggleProjectAssignments(prefix);
    }

    function openAddModal() {
        syncRoleSections('add', @json($addOldModulePermissions));
        setModalVisibility('addModal', true);
        document.getElementById('addName').focus();
    }

    function openEditModal(payload) {
        const form = document.getElementById('editForm');
        form.action = '{{ url('/settings/users') }}/' + payload.id;
        document.getElementById('editModalUserId').value = payload.id;
        document.getElementById('editName').value = payload.name || '';
        document.getElementById('editPosition').value = payload.position || '';
        document.getElementById('editEmail').value = payload.email || '';
        document.getElementById('editRole').value = String(payload.accessRoleId || '');
        document.getElementById('editPassword').value = '';
        document.getElementById('editPasswordConfirmation').value = '';

        const assignedProjectIds = Array.isArray(payload.projectIds) ? payload.projectIds.map(Number) : [];
        document.querySelectorAll('[data-edit-project]').forEach(input => {
            input.checked = assignedProjectIds.includes(Number(input.value));
        });

        clearFieldError('editName', 'editNameError');
        clearFieldError('editPosition', 'editPositionError');
        clearFieldError('editEmail', 'editEmailError');
        clearFieldError(null, 'editModulesError', 'editModuleChecks');
        clearFieldError(null, 'editProjectsError', 'editProjectChecks');
        clearFieldError('editPassword', 'editPasswordError');
        clearFieldError('editPasswordConfirmation', 'editPasswordConfirmationError');

        syncRoleSections('edit', payload.modulePermissions || []);
        updatePasswordMeter('edit', false);
        setModalVisibility('editModal', true);
        document.getElementById('editName').focus();
    }

    function openDeleteModal(action, message) {
        document.getElementById('deleteForm').action = action;
        document.getElementById('deleteMessage').textContent = message;
        setModalVisibility('deleteModal', true);
    }

    function closeModals() {
        setModalVisibility('addModal', false);
        setModalVisibility('editModal', false);
        setModalVisibility('deleteModal', false);
    }

    document.querySelectorAll('[data-toggle-target]').forEach(button => {
        button.addEventListener('click', function () {
            const target = document.getElementById(this.dataset.toggleTarget);
            const isPassword = target.type === 'password';
            target.type = isPassword ? 'text' : 'password';
            this.textContent = isPassword ? 'Hide' : 'Show';
        });
    });

    document.querySelectorAll('[data-edit-user]').forEach(button => {
        button.addEventListener('click', function () {
            try {
                openEditModal(JSON.parse(this.dataset.editUser));
            } catch (error) {
                console.error('Unable to open the user editor.', error);
            }
        });
    });

    document.getElementById('addUserForm').addEventListener('submit', function (event) {
        if (!validateUserForm('add', true)) {
            event.preventDefault();
        }
    });

    document.getElementById('editForm').addEventListener('submit', function (event) {
        if (!validateUserForm('edit', false)) {
            event.preventDefault();
        }
    });

    ['add', 'edit'].forEach(prefix => {
        const requiredPassword = prefix === 'add';

        document.getElementById(prefix + 'Name').addEventListener('input', () => validateName(prefix));
        document.getElementById(prefix + 'Position').addEventListener('input', () => validatePosition(prefix));
        document.getElementById(prefix + 'Email').addEventListener('input', () => validateEmail(prefix));
        document.getElementById(prefix + 'Password').addEventListener('input', () => {
            validatePassword(prefix, requiredPassword);
            validatePasswordConfirmation(prefix, requiredPassword);
        });
        document.getElementById(prefix + 'PasswordConfirmation').addEventListener('input', () => {
            validatePasswordConfirmation(prefix, requiredPassword);
        });
        document.getElementById(prefix + 'Role').addEventListener('change', () => {
            syncRoleSections(prefix, null);
            validateModules(prefix);
            validateProjects(prefix);
        });
        document.getElementById(prefix + 'ModuleChecks').addEventListener('change', () => validateModules(prefix));
        document.getElementById(prefix + 'ProjectChecks').addEventListener('change', () => validateProjects(prefix));
    });

    window.addEventListener('click', function (event) {
        if (event.target.classList.contains('modal')) {
            closeModals();
        }
    });

    window.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModals();
        }
    });

    syncRoleSections('add', @json($addOldModulePermissions));
    syncRoleSections('edit', @json($editOldModulePermissions));
    updatePasswordMeter('add', true);
    updatePasswordMeter('edit', false);

    @if($errors->any())
        @if($isEditError && $editModalUser)
            openEditModal(@js([
                'id' => $editModalUser->id,
                'name' => old('name', $editModalUser->name),
                'position' => old('position', $editModalUser->position),
                'email' => old('email', $editModalUser->email),
                'accessRoleId' => old('access_role_id', $editModalUser->access_role_id),
                'projectIds' => $editOldProjectIds,
                'modulePermissions' => $editOldModulePermissions,
            ]));
        @else
            openAddModal();
        @endif
    @endif
</script>
@endsection
