@extends('layouts.app')

@php
    $isEditError = old('_method') === 'PUT' && old('modal_role_id');
    $errorRole = $isEditError ? $roles->firstWhere('id', (int) old('modal_role_id')) : null;
    $moduleCatalog = \App\Models\User::moduleCatalog();
@endphp

@section('content')
<style>
    :root {
        --role-ink: #111827;
        --role-muted: #64748b;
        --role-line: #e2e8f0;
        --role-panel: #ffffff;
        --role-soft: #f8fafc;
        --role-primary: #1d4ed8;
        --role-primary-dark: #153e75;
        --role-danger: #dc2626;
    }

    .roles-page {
        width: min(1180px, 100%);
        margin: 0 auto;
        padding: 8px 0 36px;
        color: var(--role-ink);
    }

    .settings-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 24px;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .settings-tabs::-webkit-scrollbar { display: none; }

    .settings-tab {
        flex: 0 0 auto;
        padding: 10px 18px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        background: #fff;
        color: #1f2937;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
    }

    .settings-tab.active {
        background: var(--role-primary);
        color: #fff;
        border-color: var(--role-primary);
    }

    .page-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 20px;
    }

    .page-kicker {
        margin-bottom: 7px;
        color: var(--role-primary);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .page-title {
        margin: 0;
        font-size: clamp(26px, 4vw, 36px);
        line-height: 1.1;
        letter-spacing: -.035em;
    }

    .page-copy {
        max-width: 700px;
        margin: 9px 0 0;
        color: var(--role-muted);
        font-size: 14px;
        line-height: 1.65;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        padding: 10px 16px;
        border: 0;
        border-radius: 11px;
        font: inherit;
        font-size: 13px;
        font-weight: 850;
        cursor: pointer;
        transition: transform .18s ease, background .18s ease, opacity .18s ease;
    }

    .btn:hover:not(:disabled) { transform: translateY(-1px); }
    .btn:disabled { cursor: not-allowed; opacity: .48; }
    .btn-primary { color: #fff; background: var(--role-primary); box-shadow: 0 8px 18px rgba(29, 78, 216, .2); }
    .btn-primary:hover { background: #153e75; }
    .btn-secondary { color: #334155; background: #eef2f7; }
    .btn-danger { color: #b91c1c; background: #fee2e2; }
    .btn-quiet { min-height: 36px; padding: 8px 12px; color: #475569; background: #f1f5f9; }

    .guide {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1px;
        margin-bottom: 24px;
        overflow: hidden;
        border: 1px solid var(--role-line);
        border-radius: 16px;
        background: var(--role-line);
    }

    .guide-step {
        display: grid;
        grid-template-columns: 30px 1fr;
        gap: 10px;
        padding: 15px;
        background: #fff;
    }

    .guide-number {
        display: grid;
        width: 30px;
        height: 30px;
        place-items: center;
        border-radius: 9px;
        background: #eef2ff;
        color: var(--role-primary-dark);
        font-size: 12px;
        font-weight: 900;
    }

    .guide-title { margin-bottom: 2px; font-size: 13px; font-weight: 900; }
    .guide-copy { color: var(--role-muted); font-size: 11px; line-height: 1.45; }

    .roles-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
    }

    .search-shell {
        position: relative;
        width: min(390px, 100%);
    }

    .search-shell svg {
        position: absolute;
        top: 50%;
        left: 13px;
        color: #94a3b8;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .role-search {
        width: 100%;
        min-height: 42px;
        padding: 10px 12px 10px 40px;
        border: 1px solid #cbd5e1;
        border-radius: 11px;
        outline: none;
        background: #fff;
        font: inherit;
        font-size: 13px;
    }

    .role-search:focus { border-color: var(--role-primary); box-shadow: 0 0 0 3px rgba(29, 78, 216, .1); }

    .roles-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .role-card {
        display: flex;
        min-width: 0;
        flex-direction: column;
        padding: 20px;
        border: 1px solid var(--role-line);
        border-radius: 17px;
        background: var(--role-panel);
        box-shadow: 0 8px 24px rgba(15, 23, 42, .045);
    }

    .role-card[hidden] { display: none; }

    .role-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
    }

    .role-name { margin: 0; font-size: 18px; line-height: 1.25; }
    .role-description { min-height: 42px; margin: 9px 0 15px; color: var(--role-muted); font-size: 12px; line-height: 1.55; }
    .role-meta { display: flex; flex-wrap: wrap; gap: 7px; margin-top: 7px; }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .045em;
        text-transform: uppercase;
    }

    .badge-workflow { color: #155e75; background: #cffafe; }
    .badge-count { color: #1d4ed8; background: #dbeafe; }
    .badge-protected { color: #991b1b; background: #fee2e2; }

    .module-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        padding: 13px;
        border: 1px solid #e8edf3;
        border-radius: 12px;
        background: var(--role-soft);
    }

    .module-chip {
        display: inline-flex;
        padding: 5px 8px;
        border: 1px solid #dbe3ed;
        border-radius: 8px;
        background: #fff;
        color: #475569;
        font-size: 10px;
        font-weight: 750;
    }

    .role-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: auto;
        padding-top: 16px;
    }

    .empty-state {
        display: none;
        padding: 30px;
        border: 1px dashed #cbd5e1;
        border-radius: 16px;
        color: var(--role-muted);
        text-align: center;
        background: #fff;
    }

    .empty-state.visible { display: block; }

    .modal {
        display: none;
        position: fixed;
        z-index: 100;
        inset: 0;
        align-items: center;
        justify-content: center;
        padding: 20px;
        overflow-y: auto;
        background: rgba(15, 23, 42, .62);
        backdrop-filter: blur(3px);
        overscroll-behavior: contain;
    }

    .modal.open { display: flex; }

    .role-modal-card {
        display: flex;
        width: min(900px, 100%);
        height: auto;
        max-height: calc(100vh - 40px);
        max-height: calc(100dvh - 40px);
        flex-direction: column;
        overflow: hidden;
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 30px 80px rgba(15, 23, 42, .3);
    }

    #roleForm {
        display: flex;
        min-height: 0;
        flex: 1 1 auto;
        flex-direction: column;
        overflow: hidden;
    }

    .modal-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        padding: 21px 24px 17px;
        border-bottom: 1px solid var(--role-line);
    }

    .modal-title { margin: 0; font-size: 22px; }
    .modal-subtitle { margin: 5px 0 0; color: var(--role-muted); font-size: 12px; line-height: 1.5; }
    .modal-close { display: grid; width: 38px; height: 38px; flex: 0 0 auto; place-items: center; border: 0; border-radius: 10px; background: #f1f5f9; color: #475569; font-size: 22px; cursor: pointer; }
    .modal-body {
        min-height: 0;
        padding: 20px 24px;
        flex: 1 1 auto;
        overflow-x: hidden;
        overflow-y: auto;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
        scrollbar-width: thin;
        -webkit-overflow-scrolling: touch;
    }

    .modal-body::-webkit-scrollbar { width: 9px; }
    .modal-body::-webkit-scrollbar-track { background: #f1f5f9; }
    .modal-body::-webkit-scrollbar-thumb { border: 2px solid #f1f5f9; border-radius: 999px; background: #94a3b8; }
    .role-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .field { display: grid; gap: 7px; }
    .field.full { grid-column: 1 / -1; }
    .field label { color: #334155; font-size: 12px; font-weight: 850; }

    .field-input {
        width: 100%;
        min-height: 44px;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        outline: none;
        background: #fff;
        color: var(--role-ink);
        font: inherit;
        font-size: 13px;
    }

    textarea.field-input { min-height: 82px; resize: vertical; }
    .field-input:focus { border-color: var(--role-primary); box-shadow: 0 0 0 3px rgba(29, 78, 216, .1); }
    .field-hint { color: var(--role-muted); font-size: 11px; line-height: 1.5; }
    .field-error { min-height: 16px; color: #b91c1c; font-size: 11px; font-weight: 750; }

    .module-field-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 2px;
    }

    .module-tools { display: flex; gap: 7px; }
    .module-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }

    .module-option {
        display: flex;
        min-width: 0;
        min-height: 96px;
        gap: 10px;
        padding: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: var(--role-soft);
        cursor: pointer;
        transition: border-color .18s ease, background .18s ease;
    }

    .module-option:hover { border-color: #c7d2fe; }
    .module-option:has(input:checked) { border-color: var(--role-primary); background: #eef2ff; }
    .module-option input { width: 18px; height: 18px; margin-top: 2px; flex: 0 0 auto; accent-color: var(--role-primary); }
    .module-option input[type="checkbox"] {
        display: block;
        appearance: auto;
        -webkit-appearance: checkbox;
        opacity: 1;
    }
    .module-copy { display: grid; min-width: 0; align-content: start; gap: 4px; }
    .module-title { font-size: 12px; font-weight: 900; }
    .module-description { color: var(--role-muted); font-size: 10px; line-height: 1.45; }

    .modal-footer {
        display: flex;
        flex: 0 0 auto;
        justify-content: flex-end;
        gap: 10px;
        padding: 15px 24px;
        border-top: 1px solid var(--role-line);
        background: #fff;
        box-shadow: 0 -8px 22px rgba(15, 23, 42, .05);
    }

    .delete-card { width: min(450px, 100%); padding: 24px; border-radius: 18px; background: #fff; }
    .delete-card h2 { margin: 0 0 8px; }
    .delete-card p { margin: 0; color: var(--role-muted); font-size: 13px; line-height: 1.6; }

    @media (max-width: 820px) {
        .guide { grid-template-columns: 1fr; }
        .roles-grid { grid-template-columns: 1fr; }
        .role-form-grid, .module-grid { grid-template-columns: 1fr; }
        .field.full { grid-column: auto; }
    }

    @media (max-width: 640px) {
        .roles-page { padding-top: 0; }
        .page-head { display: block; }
        .page-head > .btn { width: 100%; margin-top: 16px; }
        .guide { margin-bottom: 18px; }
        .guide-step { padding: 12px; }
        .roles-toolbar { align-items: stretch; flex-direction: column; }
        .search-shell { width: 100%; }
        .roles-toolbar .btn { width: 100%; }
        .role-card { padding: 16px; }
        .role-actions .btn { flex: 1; }
        .modal { align-items: stretch; padding: 0; }
        .role-modal-card {
            width: 100%;
            height: 100vh;
            height: 100dvh;
            max-height: 100vh;
            max-height: 100dvh;
            border-radius: 0;
        }
        .modal-head { padding: 17px 16px 14px; }
        .modal-body { padding: 16px; }
        .modal-footer { padding: 12px 16px; }
        .modal-footer .btn { flex: 1; }
        .module-field-head { align-items: flex-start; flex-direction: column; }
        .module-tools { width: 100%; }
        .module-tools .btn { flex: 1; }
    }

    @media (max-height: 700px) and (min-width: 641px) {
        .modal { padding: 10px; }
        .role-modal-card { max-height: calc(100vh - 20px); }
        .role-modal-card { max-height: calc(100dvh - 20px); }
        .modal-head { padding: 15px 20px 12px; }
        .modal-body { padding: 15px 20px; }
        .modal-footer { padding: 11px 20px; }
        .module-option { min-height: 80px; }
    }
</style>

<div class="roles-page">
    <div class="settings-tabs" aria-label="Settings sections">
        @if(Auth::user()->canManageProjectsSettings())
            <a href="{{ route('settings.projects.index') }}" class="settings-tab">Projects</a>
        @endif
        <a href="{{ route('settings.users.index') }}" class="settings-tab">Users</a>
        <a href="{{ route('settings.roles.index') }}" class="settings-tab active">Roles</a>
    </div>

    <header class="page-head">
        <div>
            <div class="page-kicker">Access Control</div>
            <h1 class="page-title">Roles and responsibilities</h1>
            <p class="page-copy">Create roles that match your organization, define their default dashboard modules, then fine-tune access for individual users.</p>
        </div>
        <button type="button" class="btn btn-primary" data-open-create>
            <span aria-hidden="true">+</span> Add New Role
        </button>
    </header>

    <section class="guide" aria-label="How role access works">
        <div class="guide-step">
            <span class="guide-number">1</span>
            <div><div class="guide-title">Name the team</div><div class="guide-copy">Planning Team, Site Engineer, Accountant, Procurement, or any role you need.</div></div>
        </div>
        <div class="guide-step">
            <span class="guide-number">2</span>
            <div><div class="guide-title">Choose workflow</div><div class="guide-copy">This decides whether Monitoring and Materials use review tools or field submission tools.</div></div>
        </div>
        <div class="guide-step">
            <span class="guide-number">3</span>
            <div><div class="guide-title">Set module defaults</div><div class="guide-copy">Every module is selectable. User-specific access can still be changed in User Management.</div></div>
        </div>
    </section>

    <div class="roles-toolbar">
        <div class="search-shell">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>
            <input type="search" class="role-search" id="roleSearch" placeholder="Search roles, workflows, or modules" autocomplete="off">
        </div>
        <button type="button" class="btn btn-secondary" data-open-create>+ Add Role</button>
    </div>

    <div class="roles-grid" id="rolesGrid">
        @foreach($roles as $role)
            @php
                $workflow = $workflowOptions[$role->workflow_type] ?? ['label' => Str::headline($role->workflow_type)];
                $moduleLabels = collect($role->module_permissions ?? [])
                    ->map(fn ($module) => $moduleCatalog[$module]['label'] ?? Str::headline($module))
                    ->unique()
                    ->values();
                $searchText = Str::lower($role->name . ' ' . ($role->description ?? '') . ' ' . $workflow['label'] . ' ' . $moduleLabels->join(' '));
            @endphp
            <article class="role-card" data-role-card data-search="{{ $searchText }}">
                <div class="role-card-head">
                    <div>
                        <h2 class="role-name">{{ $role->name }}</h2>
                        <div class="role-meta">
                            <span class="badge badge-workflow">{{ $workflow['label'] }}</span>
                            <span class="badge badge-count">{{ $role->users_count }} {{ Str::plural('user', $role->users_count) }}</span>
                            @if($role->is_protected)<span class="badge badge-protected">Protected</span>@endif
                        </div>
                    </div>
                </div>

                <p class="role-description">{{ $role->description ?: 'No description added yet.' }}</p>

                <div class="module-summary" aria-label="Default modules">
                    @forelse($moduleLabels as $label)
                        <span class="module-chip">{{ $label }}</span>
                    @empty
                        <span class="module-chip">No default modules</span>
                    @endforelse
                </div>

                <div class="role-actions">
                    @unless($role->is_protected)
                        <button
                            type="button"
                            class="btn btn-danger"
                            data-delete-role
                            data-delete-action="{{ route('settings.roles.destroy', $role) }}"
                            data-delete-name="{{ $role->name }}"
                            data-user-count="{{ $role->users_count }}"
                            @disabled($role->users_count > 0)
                            title="{{ $role->users_count > 0 ? 'Move users to another role before deleting.' : 'Delete role' }}"
                        >Delete</button>
                    @endunless
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-edit-role="{{ json_encode([
                            'id' => $role->id,
                            'name' => $role->name,
                            'description' => $role->description,
                            'workflowType' => $role->workflow_type,
                            'modulePermissions' => $role->module_permissions ?? [],
                            'isProtected' => $role->is_protected,
                            'updateUrl' => route('settings.roles.update', $role),
                        ]) }}"
                    >Edit Role</button>
                </div>
            </article>
        @endforeach
    </div>

    <div class="empty-state" id="roleEmptyState">No roles match your search.</div>
</div>

<div class="modal" id="roleModal" aria-hidden="true">
    <div class="role-modal-card" role="dialog" aria-modal="true" aria-labelledby="roleModalTitle">
        <div class="modal-head">
            <div>
                <h2 class="modal-title" id="roleModalTitle">Add New Role</h2>
                <p class="modal-subtitle" id="roleModalSubtitle">Create a role and choose its default access.</p>
            </div>
            <button type="button" class="modal-close" data-close-role aria-label="Close role editor">&times;</button>
        </div>

        <form id="roleForm" method="POST" novalidate>
            @csrf
            <input type="hidden" name="_method" id="roleFormMethod" value="PUT" disabled>
            <input type="hidden" name="modal_role_id" id="modalRoleId">
            <input type="hidden" name="workflow_type" id="protectedWorkflow" disabled>

            <div class="modal-body">
                <div class="role-form-grid">
                    <div class="field">
                        <label for="roleName">Role Name *</label>
                        <input class="field-input" id="roleName" name="name" maxlength="100" placeholder="Planning Team" required>
                        <div class="field-error" id="roleNameError">@error('name'){{ $message }}@enderror</div>
                    </div>

                    <div class="field">
                        <label for="roleWorkflow">Workflow *</label>
                        <select class="field-input" id="roleWorkflow" name="workflow_type" required>
                            @foreach($workflowOptions as $key => $workflow)
                                <option value="{{ $key }}">{{ $workflow['label'] }}</option>
                            @endforeach
                        </select>
                        <div class="field-hint" id="workflowHint"></div>
                        <div class="field-error" id="roleWorkflowError">@error('workflow_type'){{ $message }}@enderror</div>
                    </div>

                    <div class="field full">
                        <label for="roleDescription">Description</label>
                        <textarea class="field-input" id="roleDescription" name="description" maxlength="500" placeholder="Describe what this team handles"></textarea>
                        <div class="field-hint"><span id="descriptionCount">0</span>/500 characters</div>
                        <div class="field-error">@error('description'){{ $message }}@enderror</div>
                    </div>

                    <div class="field full">
                        <div class="module-field-head">
                            <label>Default Module Access *</label>
                            <div class="module-tools">
                                <button type="button" class="btn btn-quiet" data-select-modules>Check All</button>
                                <button type="button" class="btn btn-quiet" data-clear-modules>Clear</button>
                            </div>
                        </div>
                        <div class="module-grid" id="roleModuleGrid"></div>
                        <div class="field-hint">These defaults are applied when this role is selected for a user. You can override them per account.</div>
                        <div class="field-error" id="roleModulesError">@error('module_permissions'){{ $message }}@enderror</div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-close-role>Cancel</button>
                <button type="submit" class="btn btn-primary" id="roleSubmitButton">Create Role</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="deleteRoleModal" aria-hidden="true">
    <div class="delete-card" role="dialog" aria-modal="true" aria-labelledby="deleteRoleTitle">
        <h2 id="deleteRoleTitle">Delete Role</h2>
        <p id="deleteRoleMessage"></p>
        <form id="deleteRoleForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-footer" style="padding:18px 0 0;border:0;">
                <button type="button" class="btn btn-secondary" data-close-delete>Cancel</button>
                <button type="submit" class="btn btn-danger">Delete Role</button>
            </div>
        </form>
    </div>
</div>

<script>
    const moduleOptionsByWorkflow = @json($moduleOptionsByWorkflow);
    const workflowOptions = @json($workflowOptions);
    const roleStoreUrl = @json(route('settings.roles.store'));

    const roleModal = document.getElementById('roleModal');
    const roleForm = document.getElementById('roleForm');
    const roleWorkflow = document.getElementById('roleWorkflow');
    const roleModuleGrid = document.getElementById('roleModuleGrid');
    const protectedWorkflow = document.getElementById('protectedWorkflow');
    const roleDescription = document.getElementById('roleDescription');

    function setModal(modal, open) {
        modal.classList.toggle('open', open);
        modal.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.style.overflow = document.querySelector('.modal.open') ? 'hidden' : '';
    }

    function checkedModules() {
        return Array.from(roleModuleGrid.querySelectorAll('input:checked')).map(input => input.value);
    }

    function remapModules(modules, workflow) {
        const fieldStaff = workflow === 'field_staff';
        const replacements = fieldStaff
            ? { monitoring_review: 'monitoring_submit', material_approvals: 'material_requests' }
            : { monitoring_submit: 'monitoring_review', material_requests: 'material_approvals' };

        return modules.map(module => replacements[module] || module);
    }

    function renderModules(selectedModules = []) {
        const workflow = roleWorkflow.value;
        const selected = remapModules(selectedModules.map(String), workflow);
        const options = moduleOptionsByWorkflow[workflow] || [];

        roleModuleGrid.innerHTML = options.map(option => `
            <label class="module-option">
                <input type="checkbox" name="module_permissions[]" value="${option.key}" ${selected.includes(option.key) ? 'checked' : ''}>
                <span class="module-copy">
                    <span class="module-title">${option.label}</span>
                    <span class="module-description">${option.description}</span>
                </span>
            </label>
        `).join('');

        document.getElementById('workflowHint').textContent = workflowOptions[workflow]?.description || '';
    }

    function updateDescriptionCount() {
        document.getElementById('descriptionCount').textContent = roleDescription.value.length;
    }

    function clearErrors() {
        document.getElementById('roleNameError').textContent = '';
        document.getElementById('roleWorkflowError').textContent = '';
        document.getElementById('roleModulesError').textContent = '';
    }

    function openCreateRole(values = {}, preserveErrors = false) {
        roleForm.action = roleStoreUrl;
        document.getElementById('roleFormMethod').disabled = true;
        document.getElementById('modalRoleId').value = '';
        document.getElementById('roleModalTitle').textContent = 'Add New Role';
        document.getElementById('roleModalSubtitle').textContent = 'Create a role and choose its default access.';
        document.getElementById('roleSubmitButton').textContent = 'Create Role';
        document.getElementById('roleName').value = values.name || '';
        roleDescription.value = values.description || '';
        roleWorkflow.disabled = false;
        protectedWorkflow.disabled = true;
        roleWorkflow.value = values.workflowType || 'reviewer';
        if (!preserveErrors) clearErrors();
        renderModules(values.modulePermissions || []);
        updateDescriptionCount();
        setModal(roleModal, true);
        document.getElementById('roleName').focus();
    }

    function openEditRole(payload, preserveErrors = false) {
        roleForm.action = payload.updateUrl;
        document.getElementById('roleFormMethod').disabled = false;
        document.getElementById('modalRoleId').value = payload.id;
        document.getElementById('roleModalTitle').textContent = 'Edit ' + payload.name;
        document.getElementById('roleModalSubtitle').textContent = payload.isProtected
            ? 'This protected role must remain an Administrator workflow.'
            : 'Update the role workflow and default module access.';
        document.getElementById('roleSubmitButton').textContent = 'Save Changes';
        document.getElementById('roleName').value = payload.name || '';
        roleDescription.value = payload.description || '';
        roleWorkflow.value = payload.workflowType || 'reviewer';
        roleWorkflow.disabled = !!payload.isProtected;
        protectedWorkflow.disabled = !payload.isProtected;
        protectedWorkflow.value = payload.workflowType || '';
        if (!preserveErrors) clearErrors();
        renderModules(payload.modulePermissions || []);
        updateDescriptionCount();
        setModal(roleModal, true);
        document.getElementById('roleName').focus();
    }

    document.querySelectorAll('[data-open-create]').forEach(button => {
        button.addEventListener('click', () => openCreateRole());
    });

    document.querySelectorAll('[data-edit-role]').forEach(button => {
        button.addEventListener('click', function () {
            try {
                openEditRole(JSON.parse(this.dataset.editRole));
            } catch (error) {
                console.error('Unable to open the role editor.', error);
            }
        });
    });

    document.querySelectorAll('[data-close-role]').forEach(button => {
        button.addEventListener('click', () => setModal(roleModal, false));
    });

    roleWorkflow.addEventListener('change', () => renderModules(checkedModules()));
    roleDescription.addEventListener('input', updateDescriptionCount);

    document.querySelector('[data-select-modules]').addEventListener('click', () => {
        roleModuleGrid.querySelectorAll('input[type="checkbox"]').forEach(input => input.checked = true);
        document.getElementById('roleModulesError').textContent = '';
    });

    document.querySelector('[data-clear-modules]').addEventListener('click', () => {
        roleModuleGrid.querySelectorAll('input[type="checkbox"]').forEach(input => input.checked = false);
    });

    roleForm.addEventListener('submit', function (event) {
        let valid = true;
        const name = document.getElementById('roleName').value.trim();

        clearErrors();

        if (name.length < 2) {
            document.getElementById('roleNameError').textContent = 'Role name must be at least 2 characters.';
            valid = false;
        }

        if (!checkedModules().length) {
            document.getElementById('roleModulesError').textContent = 'Select at least one default module.';
            valid = false;
        }

        if (!valid) event.preventDefault();
    });

    const deleteModal = document.getElementById('deleteRoleModal');
    document.querySelectorAll('[data-delete-role]').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('deleteRoleForm').action = this.dataset.deleteAction;
            document.getElementById('deleteRoleMessage').textContent = `Delete "${this.dataset.deleteName}"? This cannot be undone.`;
            setModal(deleteModal, true);
        });
    });

    document.querySelector('[data-close-delete]').addEventListener('click', () => setModal(deleteModal, false));

    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', event => {
            if (event.target === modal) setModal(modal, false);
        });
    });

    window.addEventListener('keydown', event => {
        if (event.key === 'Escape') document.querySelectorAll('.modal.open').forEach(modal => setModal(modal, false));
    });

    const roleSearch = document.getElementById('roleSearch');
    roleSearch.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        let visible = 0;

        document.querySelectorAll('[data-role-card]').forEach(card => {
            const matches = !query || card.dataset.search.includes(query);
            card.hidden = !matches;
            if (matches) visible++;
        });

        document.getElementById('roleEmptyState').classList.toggle('visible', visible === 0);
    });

    @if($errors->any())
        @if($isEditError && $errorRole)
            openEditRole(@js([
                'id' => $errorRole->id,
                'name' => old('name', $errorRole->name),
                'description' => old('description', $errorRole->description),
                'workflowType' => old('workflow_type', $errorRole->workflow_type),
                'modulePermissions' => old('module_permissions', $errorRole->module_permissions ?? []),
                'isProtected' => $errorRole->is_protected,
                'updateUrl' => route('settings.roles.update', $errorRole),
            ]), true);
        @else
            openCreateRole(@js([
                'name' => old('name'),
                'description' => old('description'),
                'workflowType' => old('workflow_type', 'reviewer'),
                'modulePermissions' => old('module_permissions', []),
            ]), true);
        @endif
    @endif
</script>
@endsection
