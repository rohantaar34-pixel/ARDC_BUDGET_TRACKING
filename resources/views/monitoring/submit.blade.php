@extends('layouts.app')

@section('content')
<style>
    .submit-wrap { background:#f8f8fb; min-height:calc(100vh - 80px); padding:24px; font-family:'Inter',system-ui,-apple-system,sans-serif; color:#111827; }
    .submit-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:20px; }
    .page-title { font-size:26px; font-weight:800; margin:0; }
    .page-sub, .muted { color:#6b7280; font-size:14px; }
    .btn { display:inline-flex; align-items:center; justify-content:center; border:0; border-radius:8px; padding:11px 14px; font-weight:800; font-size:13px; cursor:pointer; text-decoration:none; }
    .btn-dashboard { background:#eef2ff; color:#4f46e5; }
    .btn-submit { background:#4f46e5; color:#fff; width:100%; }
    .grid { display:grid; grid-template-columns:minmax(300px,.8fr) minmax(360px,1.2fr); gap:18px; align-items:start; }
    .panel, .history-card { background:#fff; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; }
    .no-history-card { background:#fff; border:1px dashed #d1d5db; border-radius:8px; padding:16px; }
    .panel-title { padding:16px; border-bottom:1px solid #e5e7eb; font-size:14px; font-weight:900; letter-spacing:.03em; text-transform:uppercase; color:#374151; }
    .filter-bar { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:10px; padding:14px; border-bottom:1px solid #e5e7eb; background:#f9fafb; }
    .filter-field label { display:block; color:#6b7280; font-size:10px; font-weight:900; letter-spacing:.06em; text-transform:uppercase; margin-bottom:5px; }
    .filter-field select { width:100%; border:1px solid #d1d5db; border-radius:8px; padding:9px 10px; background:#fff; color:#111827; font-family:inherit; font-size:13px; }
    .filter-count { padding:0 14px 12px; background:#f9fafb; color:#6b7280; font-size:12px; font-weight:800; }
    .form { padding:16px; display:flex; flex-direction:column; gap:14px; }
    label { display:block; font-size:13px; font-weight:800; color:#374151; margin-bottom:6px; }
    select, textarea, input { width:100%; border:1px solid #d1d5db; border-radius:8px; padding:11px; font-family:inherit; font-size:14px; }
    textarea { min-height:140px; resize:vertical; }
    .project-progress-box { border:1px solid #e5e7eb; border-radius:8px; padding:12px; background:#f9fafb; }
    .project-progress-title { font-size:13px; font-weight:900; color:#111827; margin-bottom:8px; }
    .progress-track { height:8px; background:#e5e7eb; border-radius:999px; overflow:hidden; }
    .progress-fill { height:100%; border-radius:999px; background:#2563eb; }
    .progress-meta { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-top:10px; }
    .progress-stat { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:8px; }
    .progress-stat-value { font-size:16px; font-weight:900; color:#111827; }
    .progress-stat-label { font-size:10px; font-weight:900; letter-spacing:.04em; text-transform:uppercase; color:#6b7280; margin-top:2px; }
    .history-list { display:flex; flex-direction:column; gap:10px; padding:14px; }
    .history-card { padding:16px; }
    .history-top { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; margin-bottom:10px; }
    .history-title { font-weight:900; }
    .status-pill { display:inline-flex; padding:4px 8px; border-radius:999px; font-size:11px; font-weight:900; white-space:nowrap; }
    .pending-pill { background:#fef3c7; color:#92400e; }
    .approved-pill { background:#dcfce7; color:#166534; }
    .rejected-pill { background:#fee2e2; color:#991b1b; }
    .details { color:#374151; font-size:14px; line-height:1.55; white-space:pre-line; }
    .remarks { margin-top:10px; padding:10px; border-radius:8px; background:#f9fafb; color:#374151; font-size:13px; }
    .photos { display:flex; gap:8px; flex-wrap:wrap; margin-top:12px; }
    .photos img { width:82px; height:64px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb; display:block; cursor:pointer; }
    .photo-preview { display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
    .photo-preview-item { position:relative; width:82px; height:64px; }
    .photo-preview img { width:82px; height:64px; object-fit:cover; border-radius:8px; border:1px solid #d1d5db; display:block; cursor:pointer; }
    .photo-remove { position:absolute; top:4px; right:4px; width:22px; height:22px; border:0; border-radius:50%; background:rgba(153,27,27,.92); color:#fff; font-size:14px; font-weight:900; line-height:1; cursor:pointer; display:flex; align-items:center; justify-content:center; }
    .photo-count { margin-top:6px; color:#6b7280; font-size:12px; font-weight:700; }
    .image-viewer { display:none; position:fixed; inset:0; z-index:80; background:rgba(15,23,42,.78); align-items:center; justify-content:center; padding:24px; }
    .image-viewer.open { display:flex; }
    .image-viewer-box { width:min(960px, 100%); max-height:92vh; background:#fff; border-radius:8px; padding:14px; display:flex; flex-direction:column; gap:12px; }
    .image-viewer-head { display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .image-viewer-title { font-size:14px; font-weight:900; color:#111827; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .image-viewer-close { border:0; border-radius:8px; background:#fee2e2; color:#991b1b; padding:8px 12px; font-weight:900; cursor:pointer; }
    .image-viewer img { max-width:100%; max-height:76vh; object-fit:contain; border-radius:8px; background:#f8fafc; }
    .live-pill { display:inline-flex; align-items:center; gap:8px; padding:8px 10px; border:1px solid #dbeafe; background:#eff6ff; color:#1d4ed8; border-radius:999px; font-size:12px; font-weight:900; }
    .live-dot { width:8px; height:8px; border-radius:50%; background:#22c55e; box-shadow:0 0 0 4px rgba(34,197,94,.14); }
    @media(max-width:900px){ .grid { grid-template-columns:1fr; } }
</style>

<div class="submit-wrap">
    <div class="submit-head">
        <div>
            <h1 class="page-title">Submit Progress Report</h1>
            <div class="page-sub">Send project accomplishments and photos for admin approval.</div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <span class="live-pill"><span class="live-dot"></span><span id="liveStatus">Live</span></span>
            <a href="{{ route('dashboard') }}" class="btn btn-dashboard">Dashboard</a>
        </div>
    </div>

    <div class="grid">
        <section class="panel">
            <div class="panel-title">New Submission</div>
            @if($projects->isEmpty())
                <div class="muted" style="padding:16px;">No assigned projects yet. Ask an administrator to assign a project to your account.</div>
            @else
                <form class="form" action="{{ route('monitoring.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <label for="project_id">Assigned Project</label>
                        <select name="project_id" id="project_id" required>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="projectProgressBox" class="project-progress-box">
                        <div id="projectProgressTitle" class="project-progress-title"></div>
                        <div class="progress-track">
                            <div id="projectProgressFill" class="progress-fill" style="width:0%"></div>
                        </div>
                        <div class="progress-meta">
                            <div class="progress-stat">
                                <div id="approvedProgress" class="progress-stat-value">0%</div>
                                <div class="progress-stat-label">Approved</div>
                            </div>
                            <div class="progress-stat">
                                <div id="pendingProgress" class="progress-stat-value">0%</div>
                                <div class="progress-stat-label">Pending</div>
                            </div>
                            <div class="progress-stat">
                                <div id="remainingProgress" class="progress-stat-value">100%</div>
                                <div class="progress-stat-label">Remaining</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="accomplishment_details">Accomplishment Details</label>
                        <textarea name="accomplishment_details" id="accomplishment_details" required>{{ old('accomplishment_details') }}</textarea>
                    </div>
                    <div>
                        <label for="estimated_completion_percentage">Estimated Completion Contribution (%)</label>
                        <input type="number" name="estimated_completion_percentage" id="estimated_completion_percentage" min="1" max="100" value="{{ old('estimated_completion_percentage') }}" required>
                        <div class="muted" style="margin-top:6px;">Only approved reports are added to the project completion percentage.</div>
                    </div>
                    <div>
                        <label for="photos">Progress Photos</label>
                        <input type="file" name="photos[]" id="photos" accept="image/*" multiple>
                        <div class="muted" style="margin-top:6px;">You can select multiple photos for one report. Maximum 10 photos, 10 MB each.</div>
                        <div id="photoCount" class="photo-count"></div>
                        <div id="photoPreview" class="photo-preview"></div>
                    </div>
                    <button class="btn btn-submit" type="submit">Submit for Approval</button>
                </form>
            @endif
        </section>

        <section class="panel">
            <div class="panel-title">My Submission History</div>
            <div class="filter-bar">
                <div class="filter-field">
                    <label for="historyProjectFilter">Project</label>
                    <select id="historyProjectFilter">
                        <option value="all">All projects</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label for="historyStatusFilter">Approval Status</label>
                    <select id="historyStatusFilter">
                        <option value="all">All statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="filter-field">
                    <label for="historyProjectStatusFilter">Project Status</label>
                    <select id="historyProjectStatusFilter">
                        <option value="all">All project statuses</option>
                        <option value="not_started">Not Started</option>
                        <option value="in_progress">In Progress</option>
                        <option value="near_completion">Near Completion</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
            <div id="historyFilterCount" class="filter-count"></div>
            <div class="history-list">
                @forelse($reports as $report)
                    <article class="history-card" data-history-card data-project-id="{{ $report->project_id }}" data-report-status="{{ $report->status }}" data-project-status="{{ $report->project->status }}">
                        <div class="history-top">
                            <div>
                                <div class="history-title">{{ $report->project->name }}</div>
                                <div class="muted">{{ $report->created_at->format('M d, Y h:i A') }} - {{ $report->estimated_completion_percentage }}%</div>
                            </div>
                            <span class="status-pill {{ $report->status }}-pill">{{ ucfirst($report->status) }}</span>
                        </div>
                        <div class="details">{{ $report->accomplishment_details }}</div>
                        @if($report->photos->isNotEmpty())
                            <div class="photos">
                                @foreach($report->photos as $photo)
                                    <a href="{{ route('monitoring.photos.show', [$report, $photo->id]) }}" target="_blank">
                                        <img src="{{ route('monitoring.photos.show', [$report, $photo->id]) }}" alt="Progress photo" data-previewable data-preview-title="{{ $photo->original_name ?? 'Progress photo' }}">
                                    </a>
                                @endforeach
                            </div>
                        @endif
                        @if($report->admin_remarks)
                            <div class="remarks"><strong>Admin remarks:</strong> {{ $report->admin_remarks }}</div>
                        @endif
                        @if($report->status === 'rejected')
                            <div class="muted" style="margin-top:10px;">You may submit a corrected report using the form.</div>
                        @endif
                    </article>
                @empty
                    <div class="muted" style="padding:18px;">No submissions yet.</div>
                @endforelse
                @foreach($projects as $project)
                    @if($reports->where('project_id', $project->id)->isEmpty())
                        <article class="no-history-card" data-history-card data-project-id="{{ $project->id }}" data-report-status="none" data-project-status="{{ $project->status }}">
                            <div class="history-top">
                                <div>
                                    <div class="history-title">{{ $project->name }}</div>
                                    <div class="muted">No submission yet for this project.</div>
                                </div>
                                <span class="status-pill">{{ $project->status_label }}</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill" style="width:{{ $project->completion_percentage }}%"></div>
                            </div>
                            <div class="muted" style="margin-top:6px;">{{ $project->completion_percentage }}% approved accomplishment</div>
                        </article>
                    @endif
                @endforeach
                <div id="historyNoMatches" class="muted" style="display:none;padding:18px;">No submissions match the selected filters.</div>
            </div>
        </section>
    </div>
</div>

<div id="imageViewer" class="image-viewer" onclick="if(event.target===this)closeImageViewer()">
    <div class="image-viewer-box">
        <div class="image-viewer-head">
            <div id="imageViewerTitle" class="image-viewer-title">Photo preview</div>
            <button type="button" class="image-viewer-close" onclick="closeImageViewer()">Close</button>
        </div>
        <img id="imageViewerImage" src="" alt="Photo preview">
    </div>
</div>

<script>
    (() => {
        const pulseUrl = @js(route('monitoring.submit.pulse'));
        const liveStatus = document.getElementById('liveStatus');
        let signature = null;
        let hasLoadedBaseline = false;

        const userIsEditing = () => {
            const active = document.activeElement;
            return active && ['INPUT', 'TEXTAREA', 'SELECT'].includes(active.tagName);
        };

        async function checkForUpdates() {
            try {
                const response = await fetch(pulseUrl, {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                });

                if (!response.ok) return;

                const data = await response.json();
                liveStatus.textContent = `Live - ${data.pending} pending`;

                if (!hasLoadedBaseline) {
                    signature = data.signature;
                    hasLoadedBaseline = true;
                    return;
                }

                if (signature !== data.signature && !userIsEditing()) {
                    liveStatus.textContent = 'Updating...';
                    window.location.reload();
                }
            } catch (error) {
                liveStatus.textContent = 'Reconnecting';
            }
        }

        checkForUpdates();
        setInterval(checkForUpdates, 5000);
    })();

    (() => {
        const projects = @js($projects->map(fn($project) => [
            'id' => $project->id,
            'name' => $project->name,
            'approved' => (int) $project->completion_percentage,
            'pending' => (int) ($project->pending_completion_total ?? 0),
        ])->values());
        const select = document.getElementById('project_id');
        const input = document.getElementById('estimated_completion_percentage');
        const title = document.getElementById('projectProgressTitle');
        const fill = document.getElementById('projectProgressFill');
        const approved = document.getElementById('approvedProgress');
        const pending = document.getElementById('pendingProgress');
        const remaining = document.getElementById('remainingProgress');

        if (!select || !input || !projects.length) return;

        function updateProjectProgress() {
            const project = projects.find(item => String(item.id) === String(select.value));
            if (!project) return;

            const reserved = Math.min(project.approved + project.pending, 100);
            const remainingValue = Math.max(100 - reserved, 0);

            title.textContent = `${project.name} accomplishment`;
            fill.style.width = `${project.approved}%`;
            approved.textContent = `${project.approved}%`;
            pending.textContent = `${project.pending}%`;
            remaining.textContent = `${remainingValue}%`;
            input.max = remainingValue;
            input.placeholder = `Max ${remainingValue}%`;

            if (Number(input.value) > remainingValue) {
                input.value = remainingValue || '';
            }
        }

        select.addEventListener('change', updateProjectProgress);
        updateProjectProgress();
    })();

    (() => {
        const project = document.getElementById('historyProjectFilter');
        const status = document.getElementById('historyStatusFilter');
        const projectStatus = document.getElementById('historyProjectStatusFilter');
        const count = document.getElementById('historyFilterCount');
        const noMatches = document.getElementById('historyNoMatches');
        const cards = Array.from(document.querySelectorAll('[data-history-card]'));
        const submittedCards = cards.filter(card => card.dataset.reportStatus !== 'none');

        function applyHistoryFilters() {
            const projectValue = project.value;
            const statusValue = status.value;
            const projectStatusValue = projectStatus.value;
            let visible = 0;

            cards.forEach(card => {
                const matchesProject = projectValue === 'all' || card.dataset.projectId === projectValue;
                const matchesStatus = statusValue === 'all'
                    ? true
                    : card.dataset.reportStatus !== 'none' && card.dataset.reportStatus === statusValue;
                const matchesProjectStatus = projectStatusValue === 'all' || card.dataset.projectStatus === projectStatusValue;
                const shouldShow = matchesProject && matchesStatus && matchesProjectStatus;
                card.style.display = shouldShow ? '' : 'none';
                if (shouldShow) visible++;
            });

            count.textContent = `${visible} items shown (${submittedCards.length} submitted reports)`;
            noMatches.style.display = visible === 0 && cards.length ? 'block' : 'none';
        }

        [project, status, projectStatus].forEach(input => input?.addEventListener('change', applyHistoryFilters));
        applyHistoryFilters();
    })();

    (() => {
        const input = document.getElementById('photos');
        const preview = document.getElementById('photoPreview');
        const count = document.getElementById('photoCount');
        let selectedFiles = [];

        if (!input || !preview || !count) return;

        function syncInputFiles() {
            const transfer = new DataTransfer();
            selectedFiles.forEach(file => transfer.items.add(file));
            input.files = transfer.files;
        }

        function renderPreview() {
            preview.innerHTML = '';
            count.textContent = selectedFiles.length
                ? `${selectedFiles.length} photo${selectedFiles.length === 1 ? '' : 's'} selected`
                : '';

            selectedFiles.slice(0, 10).forEach((file, index) => {
                if (!file.type.startsWith('image/')) return;

                const item = document.createElement('div');
                item.className = 'photo-preview-item';

                const image = document.createElement('img');
                image.alt = file.name;
                image.src = URL.createObjectURL(file);
                image.dataset.previewable = 'true';
                image.dataset.previewTitle = file.name;

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'photo-remove';
                remove.setAttribute('aria-label', `Remove ${file.name}`);
                remove.textContent = 'x';
                remove.addEventListener('click', event => {
                    event.preventDefault();
                    event.stopPropagation();
                    selectedFiles.splice(index, 1);
                    syncInputFiles();
                    renderPreview();
                });

                item.appendChild(image);
                item.appendChild(remove);
                preview.appendChild(item);
            });
        }

        input.addEventListener('change', () => {
            selectedFiles = Array.from(input.files || []).slice(0, 10);
            syncInputFiles();
            renderPreview();
        });
    })();

    function openImageViewer(src, title) {
        document.getElementById('imageViewerImage').src = src;
        document.getElementById('imageViewerTitle').textContent = title || 'Photo preview';
        document.getElementById('imageViewer').classList.add('open');
    }

    function closeImageViewer() {
        document.getElementById('imageViewer').classList.remove('open');
        document.getElementById('imageViewerImage').src = '';
    }

    document.addEventListener('click', event => {
        const image = event.target.closest('[data-previewable]');
        if (!image) return;

        event.preventDefault();
        openImageViewer(image.src, image.dataset.previewTitle || image.alt);
    });
</script>
@endsection
