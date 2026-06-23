@extends('layouts.app')

@section('content')
<style>
    .monitoring-wrap { background:#f8f8fb; min-height:calc(100vh - 80px); padding:24px; font-family:'Inter',system-ui,-apple-system,sans-serif; color:#111827; }
    .monitoring-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:20px; }
    .page-title { font-size:26px; font-weight:800; margin:0; }
    .page-sub { color:#6b7280; margin-top:4px; font-size:14px; }
    .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; border:0; border-radius:8px; padding:10px 14px; font-weight:800; font-size:13px; cursor:pointer; text-decoration:none; }
    .btn-dashboard { background:#eef2ff; color:#4f46e5; }
    .btn-approve { background:#dcfce7; color:#166534; }
    .btn-reject { background:#fee2e2; color:#991b1b; }
    .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; margin-bottom:18px; }
    .stat-card, .panel, .project-card, .report-card { background:#fff; border:1px solid #e5e7eb; border-radius:8px; }
    .stat-card { padding:16px; }
    .stat-label { color:#6b7280; font-size:11px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; }
    .stat-value { font-size:26px; font-weight:900; margin-top:6px; }
    .grid { display:grid; grid-template-columns:minmax(280px,.9fr) minmax(360px,1.4fr); gap:18px; align-items:start; }
    .panel { overflow:hidden; }
    .panel-title { padding:16px; border-bottom:1px solid #e5e7eb; font-size:14px; font-weight:900; letter-spacing:.03em; text-transform:uppercase; color:#374151; }
    .filter-bar { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:10px; padding:14px; border-bottom:1px solid #e5e7eb; background:#f9fafb; }
    .filter-field label { display:block; color:#6b7280; font-size:10px; font-weight:900; letter-spacing:.06em; text-transform:uppercase; margin-bottom:5px; }
    .filter-field select, .filter-field input { width:100%; border:1px solid #d1d5db; border-radius:8px; padding:9px 10px; background:#fff; color:#111827; font-family:inherit; font-size:13px; }
    .filter-count { padding:0 14px 12px; background:#f9fafb; color:#6b7280; font-size:12px; font-weight:800; }
    .project-list, .report-list { display:flex; flex-direction:column; gap:10px; padding:14px; }
    .project-card { padding:14px; }
    .project-row { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; }
    .project-name { font-weight:900; }
    .muted { color:#6b7280; font-size:13px; }
    .progress-track { height:8px; background:#f3f4f6; border-radius:999px; overflow:hidden; margin-top:10px; }
    .progress-fill { height:100%; border-radius:999px; }
    .status-pill { display:inline-flex; padding:4px 8px; border-radius:999px; background:#f3f4f6; color:#374151; font-size:11px; font-weight:900; white-space:nowrap; }
    .pending-pill { background:#fef3c7; color:#92400e; }
    .approved-pill { background:#dcfce7; color:#166534; }
    .rejected-pill { background:#fee2e2; color:#991b1b; }
    .report-card { padding:16px; }
    .no-report-card { padding:16px; border:1px dashed #d1d5db; border-radius:8px; background:#fff; }
    .report-top { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; margin-bottom:10px; }
    .report-title { font-weight:900; }
    .details { color:#374151; font-size:14px; line-height:1.55; white-space:pre-line; }
    .photos { display:flex; gap:8px; flex-wrap:wrap; margin-top:12px; }
    .photo-link img { width:92px; height:72px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb; display:block; }
    .review-actions { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:12px; }
    .review-form { display:grid; grid-template-columns:1fr auto; gap:8px; align-items:end; }
    .review-form textarea { width:100%; min-height:42px; resize:vertical; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit; font-size:13px; }
    .remarks { margin-top:10px; padding:10px; border-radius:8px; background:#f9fafb; color:#374151; font-size:13px; }
    .live-pill { display:inline-flex; align-items:center; gap:8px; padding:8px 10px; border:1px solid #dbeafe; background:#eff6ff; color:#1d4ed8; border-radius:999px; font-size:12px; font-weight:900; }
    .live-dot { width:8px; height:8px; border-radius:50%; background:#22c55e; box-shadow:0 0 0 4px rgba(34,197,94,.14); }
    @media(max-width:900px){ .grid, .review-actions, .review-form { grid-template-columns:1fr; } }
</style>

<div class="monitoring-wrap">
    <div class="monitoring-head">
        <div>
            <h1 class="page-title">Project Monitoring</h1>
            <div class="page-sub">Review submissions, approve progress, and track project completion.</div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <span class="live-pill"><span class="live-dot"></span><span id="liveStatus">Live</span></span>
            <a href="{{ route('dashboard') }}" class="btn btn-dashboard">Dashboard</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><div class="stat-label">Projects</div><div class="stat-value">{{ $stats['projects'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Pending Approval</div><div class="stat-value">{{ $stats['pending'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Approved Reports</div><div class="stat-value">{{ $stats['approved'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Average Completion</div><div class="stat-value">{{ $stats['average_completion'] }}%</div></div>
    </div>

    <div class="grid">
        <section class="panel">
            <div class="panel-title">Projects</div>
            <div class="filter-bar">
                <div class="filter-field">
                    <label for="projectStatusFilter">Project Status</label>
                    <select id="projectStatusFilter">
                        <option value="all">All statuses</option>
                        <option value="not_started">Not Started</option>
                        <option value="in_progress">In Progress</option>
                        <option value="near_completion">Near Completion</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="filter-field">
                    <label for="projectSearchFilter">Project</label>
                    <input id="projectSearchFilter" type="search" placeholder="Search project">
                </div>
            </div>
            <div id="projectFilterCount" class="filter-count"></div>
            <div class="project-list">
                @forelse($projects as $project)
                    <div class="project-card" data-project-card data-status="{{ $project->status }}" data-project-name="{{ Str::lower($project->name) }}">
                        <div class="project-row">
                            <div>
                                <div class="project-name">{{ $project->name }}</div>
                                <div class="muted">{{ $project->monitoring_reports_count }} reports, {{ $project->pending_reports_count }} pending</div>
                            </div>
                            <span class="status-pill">{{ $project->status_label }}</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width:{{ $project->completion_percentage }}%; background:{{ $project->progress_color }}"></div>
                        </div>
                        <div class="muted" style="margin-top:6px;">{{ $project->completion_percentage }}% complete</div>
                    </div>
                @empty
                    <div class="muted" style="padding:18px;">No projects found.</div>
                @endforelse
                <div id="projectNoMatches" class="muted" style="display:none;padding:18px;">No projects match the selected filters.</div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-title">Submitted Progress Reports</div>
            <div class="filter-bar">
                <div class="filter-field">
                    <label for="reportProjectFilter">Project</label>
                    <select id="reportProjectFilter">
                        <option value="all">All projects</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label for="reportProjectStatusFilter">Project Status</label>
                    <select id="reportProjectStatusFilter">
                        <option value="all">All statuses</option>
                        <option value="not_started">Not Started</option>
                        <option value="in_progress">In Progress</option>
                        <option value="near_completion">Near Completion</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="filter-field">
                    <label for="reportStatusFilter">Report Status</label>
                    <select id="reportStatusFilter">
                        <option value="all">All reports</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>
            <div id="reportFilterCount" class="filter-count"></div>
            <div class="report-list">
                @forelse($reports as $report)
                    <article class="report-card" data-report-card data-project-id="{{ $report->project_id }}" data-project-status="{{ $report->project->status }}" data-report-status="{{ $report->status }}">
                        <div class="report-top">
                            <div>
                                <div class="report-title">{{ $report->project->name }}</div>
                                <div class="muted">Submitted by {{ $report->user->name }} on {{ $report->created_at->format('M d, Y h:i A') }}</div>
                            </div>
                            <span class="status-pill {{ $report->status }}-pill">{{ ucfirst($report->status) }}</span>
                        </div>
                        <div class="muted">Estimated contribution: {{ $report->estimated_completion_percentage }}%</div>
                        <div class="details">{{ $report->accomplishment_details }}</div>

                        @if($report->photos->isNotEmpty())
                            <div class="photos">
                                @foreach($report->photos as $photo)
                                    <a class="photo-link" href="{{ route('monitoring.photos.show', [$report, $photo->id]) }}" target="_blank">
                                        <img src="{{ route('monitoring.photos.show', [$report, $photo->id]) }}" alt="Progress photo">
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        @if($report->admin_remarks)
                            <div class="remarks"><strong>Remarks:</strong> {{ $report->admin_remarks }}</div>
                        @endif

                        @if($report->status === 'pending')
                            <div class="review-actions">
                                <form class="review-form" action="{{ route('monitoring.reports.approve', $report) }}" method="POST">
                                    @csrf
                                    <textarea name="admin_remarks" placeholder="Optional approval remarks"></textarea>
                                    <button type="submit" class="btn btn-approve">Approve</button>
                                </form>
                                <form class="review-form" action="{{ route('monitoring.reports.reject', $report) }}" method="POST">
                                    @csrf
                                    <textarea name="admin_remarks" placeholder="Required rejection remarks" required></textarea>
                                    <button type="submit" class="btn btn-reject">Reject</button>
                                </form>
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="muted" style="padding:18px;">No monitoring reports submitted yet.</div>
                @endforelse
                @foreach($projects->where('monitoring_reports_count', 0) as $project)
                    <article class="no-report-card" data-report-card data-project-id="{{ $project->id }}" data-project-status="{{ $project->status }}" data-report-status="none">
                        <div class="report-top">
                            <div>
                                <div class="report-title">{{ $project->name }}</div>
                                <div class="muted">No progress reports submitted yet.</div>
                            </div>
                            <span class="status-pill">{{ $project->status_label }}</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width:{{ $project->completion_percentage }}%; background:{{ $project->progress_color }}"></div>
                        </div>
                        <div class="muted" style="margin-top:6px;">{{ $project->completion_percentage }}% complete</div>
                    </article>
                @endforeach
                <div id="reportNoMatches" class="muted" style="display:none;padding:18px;">No reports match the selected filters.</div>
            </div>
        </section>
    </div>
</div>

<script>
    (() => {
        const pulseUrl = @js(route('monitoring.pulse'));
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
        const projectStatus = document.getElementById('projectStatusFilter');
        const projectSearch = document.getElementById('projectSearchFilter');
        const projectCount = document.getElementById('projectFilterCount');
        const projectNoMatches = document.getElementById('projectNoMatches');
        const projectCards = Array.from(document.querySelectorAll('[data-project-card]'));

        const reportProject = document.getElementById('reportProjectFilter');
        const reportProjectStatus = document.getElementById('reportProjectStatusFilter');
        const reportStatus = document.getElementById('reportStatusFilter');
        const reportCount = document.getElementById('reportFilterCount');
        const reportNoMatches = document.getElementById('reportNoMatches');
        const reportCards = Array.from(document.querySelectorAll('[data-report-card]'));
        const realReportCards = reportCards.filter(card => card.dataset.reportStatus !== 'none');

        function applyProjectFilters() {
            const status = projectStatus.value;
            const term = projectSearch.value.trim().toLowerCase();
            let visible = 0;

            projectCards.forEach(card => {
                const matchesStatus = status === 'all' || card.dataset.status === status;
                const matchesSearch = !term || card.dataset.projectName.includes(term);
                const shouldShow = matchesStatus && matchesSearch;
                card.style.display = shouldShow ? '' : 'none';
                if (shouldShow) visible++;
            });

            projectCount.textContent = `${visible} of ${projectCards.length} projects shown`;
            projectNoMatches.style.display = visible === 0 && projectCards.length ? 'block' : 'none';
        }

        function applyReportFilters() {
            const projectId = reportProject.value;
            const projectStatusValue = reportProjectStatus.value;
            const status = reportStatus.value;
            let visible = 0;

            reportCards.forEach(card => {
                const matchesProject = projectId === 'all' || card.dataset.projectId === projectId;
                const matchesProjectStatus = projectStatusValue === 'all' || card.dataset.projectStatus === projectStatusValue;
                const matchesReportStatus = status === 'all'
                    ? true
                    : card.dataset.reportStatus !== 'none' && card.dataset.reportStatus === status;
                const shouldShow = matchesProject && matchesProjectStatus && matchesReportStatus;
                card.style.display = shouldShow ? '' : 'none';
                if (shouldShow) visible++;
            });

            reportCount.textContent = `${visible} items shown (${realReportCards.length} submitted reports)`;
            reportNoMatches.style.display = visible === 0 && reportCards.length ? 'block' : 'none';
        }

        [projectStatus, projectSearch].forEach(input => input?.addEventListener('input', applyProjectFilters));
        [reportProject, reportProjectStatus, reportStatus].forEach(input => input?.addEventListener('change', applyReportFilters));
        applyProjectFilters();
        applyReportFilters();
    })();
</script>
@endsection
