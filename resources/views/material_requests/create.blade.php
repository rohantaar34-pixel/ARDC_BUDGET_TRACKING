@extends('layouts.app')

@section('content')
<style>
    .mr-wrap { background:#f8f8fb; min-height:calc(100vh - 80px); padding:24px; color:#111827; }
    .mr-head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:20px; }
    .mr-title { font-size:26px; font-weight:800; margin:0; }
    .mr-sub, .muted { color:#6b7280; font-size:14px; }
    .btn { display:inline-flex; align-items:center; justify-content:center; min-height:44px; border:0; border-radius:8px; padding:10px 14px; font-weight:800; font-size:13px; cursor:pointer; text-decoration:none; }
    .btn-soft { background:#ecfdf8; color:#0f766e; }
    .btn-primary { background:#0891b2; color:#fff; width:100%; }
    .live-pill { display:inline-flex; align-items:center; gap:8px; min-height:44px; padding:10px 12px; border:1px solid #bae6fd; background:#ecfeff; color:#0e7490; border-radius:999px; font-size:12px; font-weight:900; }
    .live-dot { width:8px; height:8px; border-radius:50%; background:#22c55e; box-shadow:0 0 0 4px rgba(34,197,94,.14); }
    .grid { display:grid; grid-template-columns:1fr; gap:18px; align-items:start; }
    .panel { background:#fff; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; }
    .panel-title { padding:16px; border-bottom:1px solid #e5e7eb; font-size:13px; font-weight:900; letter-spacing:.04em; text-transform:uppercase; color:#374151; }
    .form { padding:16px; display:grid; gap:14px; }
    label { display:block; margin-bottom:6px; font-size:13px; font-weight:800; color:#374151; }
    input, select, textarea { width:100%; border:1px solid #d1d5db; border-radius:8px; padding:11px; font:inherit; font-size:14px; background:#fff; }
    textarea { min-height:110px; resize:vertical; }
    .readonly-box { border:1px solid #d1d5db; border-radius:8px; padding:11px; background:#f9fafb; font-weight:800; color:#374151; }
    .two { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .inventory-fields { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
    .field-wide { grid-column:span 2; }
    .table-wrap { overflow:auto; }
    table { width:100%; border-collapse:collapse; min-width:860px; }
    th { background:#f9fafb; color:#6b7280; text-align:left; padding:12px; font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:.05em; }
    td { border-top:1px solid #e5e7eb; padding:12px; font-size:13px; vertical-align:top; }
    .status { display:inline-flex; padding:4px 8px; border-radius:999px; font-size:11px; font-weight:900; white-space:nowrap; }
    .status-pending { background:#fef3c7; color:#92400e; }
    .status-rejected { background:#fee2e2; color:#991b1b; }
    .status-issued, .status-completed, .status-approved { background:#dcfce7; color:#166534; }
    .status-waiting_for_procurement, .status-partially_approved { background:#e0f2fe; color:#075985; }
    @media(max-width:760px){ .two, .inventory-fields { grid-template-columns:1fr; } .field-wide { grid-column:auto; } }
</style>

<div class="mr-wrap">
    <div class="mr-head">
        <div>
            <h1 class="mr-title">Material Requests</h1>
            <div class="mr-sub">Request project materials and track each approval step.</div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <span class="live-pill"><span class="live-dot"></span><span id="materialLiveStatus">Live</span></span>
            <a class="btn btn-soft" href="{{ route('monitoring.submit') }}">Back to Employee Portal</a>
        </div>
    </div>

    <div class="grid">
        <section class="panel">
            <div class="panel-title">New Request</div>
            @if($projects->isEmpty())
                <div class="muted" style="padding:16px;">No assigned projects yet. Ask an administrator to assign a project to your account.</div>
            @else
                <form class="form" method="POST" action="{{ route('material-requests.store') }}">
                    @csrf
                    <div>
                        <label for="project_id">Assigned Project</label>
                        @if($projects->count() === 1)
                            <div class="readonly-box">{{ $projects->first()->name }}</div>
                            <input type="hidden" name="project_id" value="{{ $projects->first()->id }}">
                        @else
                            <select id="project_id" name="project_id" required>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>{{ $project->name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div>
                        <label for="inventory_item_id">Existing Inventory Item <span class="muted">(Optional)</span></label>
                        <select id="inventory_item_id" name="inventory_item_id">
                            <option value="">Request a new material</option>
                            @foreach($items as $item)
                                <option
                                    value="{{ $item->id }}"
                                    data-name="{{ $item->name }}"
                                    data-category="{{ $item->category }}"
                                    data-unit="{{ $item->unit }}"
                                    data-description="{{ $item->description }}"
                                    @selected(old('inventory_item_id') == $item->id)
                                >{{ $item->name }} - {{ $item->quantity }} {{ $item->unit }} available</option>
                            @endforeach
                        </select>
                        <div class="muted" style="margin-top:6px;">Select an item to autofill its details, or leave this blank to request a new material.</div>
                    </div>

                    <div class="inventory-fields">
                        <div>
                            <label for="material_name">Item Name *</label>
                            <input id="material_name" name="material_name" value="{{ old('material_name') }}" maxlength="255" placeholder="e.g. Cement Bags" required>
                        </div>
                        <div>
                            <label for="material_category">Category</label>
                            <select id="material_category" name="material_category">
                                <option value="">Select category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" @selected(old('material_category') === $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="unit">Unit *</label>
                            <input id="unit" name="unit" value="{{ old('unit', 'pcs') }}" list="unitList" required>
                            <datalist id="unitList">
                                <option value="pcs"><option value="bags"><option value="kg"><option value="m"><option value="liters"><option value="sets">
                            </datalist>
                        </div>
                        <div>
                            <label for="quantity">Quantity *</label>
                            <input id="quantity" type="number" name="quantity" min="1" value="{{ old('quantity') }}" required>
                        </div>
                        <div class="field-wide">
                            <label for="purpose">Description</label>
                            <input id="purpose" name="purpose" value="{{ old('purpose') }}" maxlength="2000" placeholder="Optional notes">
                        </div>
                    </div>

                    <div>
                        <label for="request_note">Reason / Note</label>
                        <textarea id="request_note" name="request_note" maxlength="2000" placeholder="Why is this material needed?">{{ old('request_note') }}</textarea>
                    </div>

                    <div>
                        <label for="date_requested">Date Requested</label>
                        <input id="date_requested" type="date" name="date_requested" value="{{ old('date_requested', now()->toDateString()) }}" required>
                    </div>

                    <button class="btn btn-primary" type="submit">Submit Material Request</button>
                </form>
            @endif
        </section>

        <section class="panel">
            <div class="panel-title">My Requests</div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Request No.</th>
                            <th>Project</th>
                            <th>Material</th>
                            <th>Quantity</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Budget Impact</th>
                            <th>Request Reason/Note</th>
                            <th>Reviewed By</th>
                            <th>Review Date</th>
                            <th>Review Reason/Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            <tr>
                                <td><strong>{{ $request->request_number }}</strong></td>
                                <td>{{ $request->project->name }}</td>
                                <td>{{ $request->material_name }}</td>
                                <td>{{ number_format($request->quantity) }} {{ $request->unit }}</td>
                                <td>{{ $request->date_requested->format('M d, Y') }}</td>
                                <td><span class="status status-{{ $request->status }}">{{ $request->status_label }}</span></td>
                                <td>
                                    @if($request->budget_commitment_status === \App\Models\MaterialRequest::COMMITMENT_RESERVED)
                                        Reserved PHP {{ number_format((float) $request->estimated_total_cost, 2) }}
                                    @elseif($request->actual_total_cost !== null)
                                        Actual PHP {{ number_format((float) $request->actual_total_cost, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $request->request_note ?: '-' }}</td>
                                <td>{{ $request->reviewer->name ?? '-' }}</td>
                                <td>{{ $request->reviewed_at?->format('M d, Y h:i A') ?? '-' }}</td>
                                <td>{{ $request->rejection_reason ?: ($request->procurement_note ?: '-') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="muted" style="text-align:center;padding:20px;">No material requests yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<script>
    (() => {
        const pulseUrl = @js(route('material-requests.create.pulse'));
        const liveStatus = document.getElementById('materialLiveStatus');
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

                if (signature !== data.signature) {
                    if (userIsEditing()) {
                        liveStatus.textContent = 'Updates available';
                        return;
                    }

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

    const itemSelect = document.getElementById('inventory_item_id');
    const materialName = document.getElementById('material_name');
    const categoryInput = document.getElementById('material_category');
    const unitInput = document.getElementById('unit');
    const descriptionInput = document.getElementById('purpose');

    function autofillInventoryDetails() {
        const selected = itemSelect.options[itemSelect.selectedIndex];
        if (!selected?.value) return;

        materialName.value = selected.dataset.name || '';
        categoryInput.value = selected.dataset.category || '';
        unitInput.value = selected.dataset.unit || 'pcs';
        descriptionInput.value = selected.dataset.description || '';
    }

    itemSelect?.addEventListener('change', autofillInventoryDetails);
    if (itemSelect?.value && !materialName.value) autofillInventoryDetails();
</script>
@endsection
