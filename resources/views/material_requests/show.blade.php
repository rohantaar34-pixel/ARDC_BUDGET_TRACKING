@extends('layouts.app')

@section('content')
<style>
    .mr-wrap { background:#f8f8fb; min-height:calc(100vh - 80px); padding:24px; color:#111827; }
    .head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:18px; }
    .title { font-size:26px; font-weight:800; margin:0; }
    .muted { color:#6b7280; font-size:14px; }
    .btn { display:inline-flex; align-items:center; justify-content:center; min-height:42px; border:0; border-radius:8px; padding:9px 13px; font-weight:800; font-size:12px; cursor:pointer; text-decoration:none; background:#eff6ff; color:#1d4ed8; }
    .grid { display:grid; grid-template-columns:minmax(280px,.8fr) minmax(360px,1.2fr); gap:18px; align-items:start; }
    .panel { background:#fff; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; }
    .panel-title { padding:16px; border-bottom:1px solid #e5e7eb; font-size:12px; font-weight:900; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; }
    .body { padding:16px; display:grid; gap:12px; }
    .row { display:grid; grid-template-columns:150px 1fr; gap:12px; font-size:14px; }
    .label { color:#6b7280; font-weight:800; }
    .status { display:inline-flex; padding:4px 8px; border-radius:999px; font-size:11px; font-weight:900; white-space:nowrap; }
    .status-pending { background:#fef3c7; color:#92400e; }
    .status-rejected { background:#fee2e2; color:#991b1b; }
    .status-issued, .status-completed, .status-approved { background:#dcfce7; color:#166534; }
    .status-waiting_for_procurement, .status-partially_approved { background:#e0f2fe; color:#075985; }
    table { width:100%; border-collapse:collapse; }
    th { background:#f9fafb; color:#6b7280; text-align:left; padding:12px; font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:.05em; }
    td { border-top:1px solid #e5e7eb; padding:12px; font-size:13px; vertical-align:top; }
    @media(max-width:860px){ .grid { grid-template-columns:1fr; } .row { grid-template-columns:1fr; gap:4px; } }
</style>

<div class="mr-wrap">
    <div class="head">
        <div>
            <h1 class="title">{{ $materialRequest->request_number }}</h1>
            <div class="muted">Material request details and audit trail.</div>
        </div>
        <a class="btn" href="{{ route('material-requests.index') }}">Back to Requests</a>
    </div>

    <div class="grid">
        <section class="panel">
            <div class="panel-title">Request Details</div>
            <div class="body">
                <div class="row"><div class="label">Status</div><div><span class="status status-{{ $materialRequest->status }}">{{ $materialRequest->status_label }}</span></div></div>
                <div class="row"><div class="label">Employee</div><div>{{ $materialRequest->requester->name }}</div></div>
                <div class="row"><div class="label">Project</div><div>{{ $materialRequest->project->name }}</div></div>
                <div class="row"><div class="label">Material</div><div>{{ $materialRequest->material_name }}</div></div>
                <div class="row"><div class="label">Category</div><div>{{ $materialRequest->material_category ?: '-' }}</div></div>
                <div class="row"><div class="label">Inventory Link</div><div>{{ $materialRequest->inventoryItem->name ?? 'Not linked / procurement item' }}</div></div>
                <div class="row"><div class="label">Requested Qty</div><div>{{ number_format($materialRequest->quantity) }} {{ $materialRequest->unit }}</div></div>
                <div class="row"><div class="label">Approved Qty</div><div>{{ $materialRequest->approved_quantity ? number_format($materialRequest->approved_quantity) . ' ' . $materialRequest->unit : '-' }}</div></div>
                <div class="row"><div class="label">Date Requested</div><div>{{ $materialRequest->date_requested->format('M d, Y') }}</div></div>
                <div class="row"><div class="label">Description</div><div>{{ $materialRequest->purpose ?: '-' }}</div></div>
                <div class="row"><div class="label">Request Reason/Note</div><div>{{ $materialRequest->request_note ?: '-' }}</div></div>
                <div class="row"><div class="label">Reviewed By</div><div>{{ $materialRequest->reviewer->name ?? '-' }}</div></div>
                <div class="row"><div class="label">Review Date</div><div>{{ $materialRequest->reviewed_at?->format('M d, Y h:i A') ?? '-' }}</div></div>
                <div class="row"><div class="label">Budget Status</div><div>{{ $materialRequest->budget_commitment_status ? ucwords($materialRequest->budget_commitment_status) : '-' }}</div></div>
                <div class="row"><div class="label">Estimated Cost</div><div>{{ $materialRequest->estimated_total_cost !== null ? 'PHP ' . number_format((float) $materialRequest->estimated_total_cost, 2) : '-' }}</div></div>
                <div class="row"><div class="label">Actual Issued Cost</div><div>{{ $materialRequest->actual_total_cost !== null ? 'PHP ' . number_format((float) $materialRequest->actual_total_cost, 2) : '-' }}</div></div>
                <div class="row"><div class="label">Review Reason/Note</div><div>{{ $materialRequest->rejection_reason ?: ($materialRequest->procurement_note ?: '-') }}</div></div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-title">Audit Trail</div>
            <table>
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Quantity</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materialRequest->audits as $audit)
                        <tr>
                            <td><strong>{{ ucwords(str_replace('_', ' ', $audit->action)) }}</strong><div class="muted">{{ $audit->notes }}</div></td>
                            <td>{{ $audit->user->name ?? 'System' }}</td>
                            <td>{{ $audit->role ? ucwords(str_replace('_', ' ', $audit->role)) : '-' }}</td>
                            <td>{{ $audit->quantity ? number_format($audit->quantity) : '-' }}</td>
                            <td>{{ $audit->created_at->format('M d, Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="muted" style="text-align:center;padding:20px;">No audit entries yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
</div>
@endsection
