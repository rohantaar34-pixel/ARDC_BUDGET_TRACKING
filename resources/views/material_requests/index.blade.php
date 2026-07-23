@extends('layouts.app')

@section('content')
<style>
    .mr-wrap { background:#f8f8fb; min-height:calc(100vh - 80px); padding:24px; color:#111827; }
    .mr-head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:20px; }
    .mr-title { font-size:26px; font-weight:800; margin:0; }
    .mr-sub, .muted { color:#6b7280; font-size:14px; }
    .btn { display:inline-flex; align-items:center; justify-content:center; min-height:42px; border:0; border-radius:8px; padding:9px 13px; font-weight:800; font-size:12px; cursor:pointer; text-decoration:none; }
    .btn-soft { background:#ecfdf8; color:#0f766e; }
    .btn-approve { background:#0891b2; color:#fff; }
    .btn-reject { background:#fee2e2; color:#991b1b; }
    .btn:disabled { background:#94a3b8; opacity:.65; cursor:not-allowed; }
    .live-pill { display:inline-flex; align-items:center; gap:8px; min-height:42px; padding:9px 12px; border:1px solid #bae6fd; background:#ecfeff; color:#0e7490; border-radius:999px; font-size:12px; font-weight:900; }
    .live-dot { width:8px; height:8px; border-radius:50%; background:#22c55e; box-shadow:0 0 0 4px rgba(34,197,94,.14); }
    .filters, .panel { background:#fff; border:1px solid #e5e7eb; border-radius:8px; }
    .filters { padding:14px; display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:10px; margin-bottom:16px; }
    label { display:block; margin-bottom:5px; color:#6b7280; font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:.04em; }
    input, select, textarea { width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font:inherit; font-size:13px; background:#fff; }
    textarea { min-height:76px; resize:vertical; }
    .readonly-action { width:100%; min-height:42px; display:flex; align-items:center; border:1px solid #d1d5db; border-radius:8px; padding:10px; background:#f9fafb; color:#111827; font-size:13px; }
    .table-wrap { overflow:auto; }
    table { width:100%; border-collapse:collapse; min-width:1120px; }
    th { background:#f9fafb; color:#6b7280; text-align:left; padding:12px; font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:.05em; }
    td { border-top:1px solid #e5e7eb; padding:12px; font-size:13px; vertical-align:top; }
    .status { display:inline-flex; padding:4px 8px; border-radius:999px; font-size:11px; font-weight:900; white-space:nowrap; }
    .status-pending { background:#fef3c7; color:#92400e; }
    .status-rejected { background:#fee2e2; color:#991b1b; }
    .status-issued, .status-completed, .status-approved { background:#dcfce7; color:#166534; }
    .status-waiting_for_procurement, .status-partially_approved { background:#e0f2fe; color:#075985; }
    .stock-ok { color:#166534; font-weight:900; }
    .stock-low { color:#92400e; font-weight:900; }
    .stock-none { color:#991b1b; font-weight:900; }
    .actions { display:flex; gap:8px; align-items:center; flex-wrap:wrap; min-width:170px; }
    .action-form { border:1px solid #e5e7eb; border-radius:8px; padding:10px; display:grid; gap:8px; background:#fff; }
    .inline { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
    .pagination-wrap { padding:14px; border-top:1px solid #e5e7eb; }
    .modal-backdrop { position:fixed; inset:0; z-index:90; display:none; align-items:center; justify-content:center; padding:18px; background:rgba(15,23,42,.58); }
    .modal-backdrop.open { display:flex; }
    .review-modal { width:min(780px,100%); max-height:92vh; overflow:auto; background:#fff; border:1px solid #e5e7eb; border-radius:8px; box-shadow:0 24px 70px rgba(15,23,42,.28); }
    .modal-head { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; padding:18px; border-bottom:1px solid #e5e7eb; }
    .modal-title { margin:0; font-size:18px; font-weight:900; color:#111827; }
    .modal-close { width:42px; height:42px; border:0; border-radius:8px; background:#f3f4f6; color:#374151; cursor:pointer; font-size:22px; line-height:1; }
    .modal-body { padding:18px; display:grid; gap:12px; }
    .request-summary { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; padding:12px; border:1px solid #e5e7eb; border-radius:8px; background:#f9fafb; }
    .summary-item span { display:block; color:#6b7280; font-size:10px; font-weight:900; letter-spacing:.05em; text-transform:uppercase; margin-bottom:3px; }
    .summary-item strong { display:block; color:#111827; font-size:13px; }
    .commitment-note { padding:10px; border:1px solid #bae6fd; border-radius:8px; background:#ecfeff; color:#155e75; font-size:12px; line-height:1.5; }
    .commitment-note strong { display:block; color:#0e7490; font-size:14px; }
    .inventory-fields { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; }
    .inventory-fields .field-wide { grid-column:span 2; }
    .modal-section { border:1px solid #e5e7eb; border-radius:8px; padding:12px; display:grid; gap:10px; background:#fff; }
    .modal-section-title { color:#6b7280; font-size:12px; font-weight:900; letter-spacing:.04em; text-transform:uppercase; }
    @media(max-width:760px){ .mr-wrap { padding:16px; } .inline, .request-summary, .inventory-fields { grid-template-columns:1fr; } .inventory-fields .field-wide { grid-column:auto; } }
</style>

<div class="mr-wrap">
    <div class="mr-head">
        <div>
            <h1 class="mr-title">Material Request Approvals</h1>
            <div class="mr-sub">Review employee requests, check stock, issue materials, or mark procurement needs.</div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <span class="live-pill"><span class="live-dot"></span><span id="approvalLiveStatus">Live</span></span>
            <a class="btn btn-soft" href="{{ route('dashboard') }}">Dashboard</a>
        </div>
    </div>

    <form class="filters" method="GET" action="{{ route('material-requests.index') }}">
        <div>
            <label for="search">Search</label>
            <input id="search" name="search" value="{{ request('search') }}" placeholder="Request, material, employee">
        </div>
        <div>
            <label for="project_id">Project</label>
            <select id="project_id" name="project_id">
                <option value="">All projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" @selected(request('project_id') == $project->id)>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="employee_id">Employee</label>
            <select id="employee_id" name="employee_id">
                <option value="">All employees</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" @selected(request('employee_id') == $employee->id)>{{ $employee->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="date_from">Date From</label>
            <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}">
        </div>
        <div>
            <label for="date_to">Date To</label>
            <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}">
        </div>
        <div style="display:flex;align-items:end;">
            <button class="btn btn-approve" type="submit" style="width:100%;">Apply Filters</button>
        </div>
    </form>

    <section class="panel">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Request</th>
                        <th>Employee</th>
                        <th>Project</th>
                        <th>Material</th>
                        <th>Quantity</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Inventory Availability</th>
                        <th>Review</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                        @php
                            $item = $request->inventoryItem;
                            $stock = $item?->quantity;
                            $canAct = !in_array($request->status, ['rejected', 'issued', 'completed'], true);
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('material-requests.show', $request) }}" style="font-weight:900;color:#0e7490;text-decoration:none;">{{ $request->request_number }}</a>
                                <div class="muted">{{ $request->created_at->format('M d, Y h:i A') }}</div>
                            </td>
                            <td>{{ $request->requester->name }}</td>
                            <td>{{ $request->project->name }}</td>
                            <td>
                                <strong>{{ $request->material_name }}</strong>
                                <div class="muted">{{ $item ? 'Linked to inventory' : 'New material / not yet linked' }}</div>
                            </td>
                            <td>{{ number_format($request->quantity) }} {{ $request->unit }}</td>
                            <td>{{ $request->date_requested->format('M d, Y') }}</td>
                            <td><span class="status status-{{ $request->status }}">{{ $request->status_label }}</span></td>
                            <td>
                                @if($item)
                                    @if($stock >= $request->quantity)
                                        <span class="stock-ok">{{ number_format($stock) }} {{ $item->unit }} available</span>
                                    @elseif($stock > 0)
                                        <span class="stock-low">{{ number_format($stock) }} {{ $item->unit }} available for partial issue</span>
                                    @else
                                        <span class="stock-none">Out of stock</span>
                                    @endif
                                @else
                                    <span class="stock-none">Not in inventory</span>
                                @endif
                                @if($request->purpose)
                                    <div class="muted" style="margin-top:6px;">{{ $request->purpose }}</div>
                                @endif
                                @if($request->request_note)
                                    <div class="muted" style="margin-top:6px;"><strong>Request note:</strong> {{ $request->request_note }}</div>
                                @endif
                                @if($request->rejection_reason || $request->procurement_note)
                                    <div class="muted" style="margin-top:6px;">{{ $request->rejection_reason ?: $request->procurement_note }}</div>
                                @endif
                                @if($request->budget_commitment_status === \App\Models\MaterialRequest::COMMITMENT_RESERVED)
                                    <div class="muted" style="margin-top:6px;">
                                        Budget reserved: PHP {{ number_format((float) $request->estimated_total_cost, 2) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($canAct)
                                    <div class="actions">
                                        <button class="btn btn-approve" type="button" onclick="openReviewModal('review-modal-{{ $request->id }}')">Review</button>
                                        <a class="btn btn-soft" href="{{ route('material-requests.show', $request) }}">Details</a>
                                    </div>
                                @else
                                    <div class="muted">
                                        Reviewed by {{ $request->reviewer->name ?? '-' }}<br>
                                        {{ $request->reviewed_at?->format('M d, Y h:i A') ?? '-' }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="muted" style="text-align:center;padding:22px;">No material requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $requests->links() }}</div>
    </section>

    @foreach($requests as $modalRequest)
        @php
            $modalItem = $modalRequest->inventoryItem;
            $modalStock = $modalItem?->quantity;
            $modalPreviouslyIssued = (int) ($modalRequest->approved_quantity ?? 0);
            $modalRemainingQuantity = max((int) $modalRequest->quantity - $modalPreviouslyIssued, 0);
            $modalProcurementQuantity = $modalItem
                ? max($modalRemainingQuantity - (int) $modalStock, 0)
                : $modalRemainingQuantity;
            $modalDecision = (!$modalItem || $modalStock <= 0) ? 'procure_issue' : 'issue';
            $modalActionLabel = !$modalItem
                ? 'Procured - create item and issue'
                : ($modalStock <= 0 ? 'Procured - add stock and issue' : 'Approve and issue');
            $modalCanAct = !in_array($modalRequest->status, ['rejected', 'issued', 'completed'], true);
        @endphp

        @if($modalCanAct)
            <div class="modal-backdrop" id="review-modal-{{ $modalRequest->id }}" role="dialog" aria-modal="true" aria-labelledby="review-modal-title-{{ $modalRequest->id }}">
                <div class="review-modal">
                    <div class="modal-head">
                        <div>
                            <h2 class="modal-title" id="review-modal-title-{{ $modalRequest->id }}">Review {{ $modalRequest->request_number }}</h2>
                            <div class="muted">{{ $modalRequest->requester->name }} - {{ $modalRequest->project->name }}</div>
                        </div>
                        <button class="modal-close" type="button" onclick="closeReviewModal('review-modal-{{ $modalRequest->id }}')" aria-label="Close review modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="request-summary">
                            <div class="summary-item">
                                <span>Material</span>
                                <strong>{{ $modalRequest->material_name }}</strong>
                            </div>
                            <div class="summary-item">
                                <span>Category</span>
                                <strong>{{ $modalRequest->material_category ?: 'Uncategorized' }}</strong>
                            </div>
                            <div class="summary-item">
                                <span>Requested Qty</span>
                                <strong>{{ number_format($modalRequest->quantity) }} {{ $modalRequest->unit }}</strong>
                            </div>
                            @if($modalPreviouslyIssued > 0)
                                <div class="summary-item">
                                    <span>Remaining Qty</span>
                                    <strong>{{ number_format($modalRemainingQuantity) }} {{ $modalRequest->unit }}</strong>
                                </div>
                            @endif
                            <div class="summary-item">
                                <span>Date Requested</span>
                                <strong>{{ $modalRequest->date_requested->format('M d, Y') }}</strong>
                            </div>
                            <div class="summary-item">
                                <span>Inventory</span>
                                <strong>
                                    @if($modalItem)
                                        {{ number_format($modalStock) }} {{ $modalItem->unit }} available
                                    @else
                                        Not linked
                                    @endif
                                </strong>
                            </div>
                            <div class="summary-item">
                                <span>Project Available Budget</span>
                                <strong>PHP {{ number_format($modalRequest->project->current_budget, 2) }}</strong>
                            </div>
                            <div class="summary-item">
                                <span>Request Reason / Note</span>
                                <strong>{{ $modalRequest->request_note ?: '-' }}</strong>
                            </div>
                        </div>

                        <form
                            class="modal-section"
                            method="POST"
                            action="{{ route('material-requests.approve', $modalRequest) }}"
                            data-approve-form
                            data-requested-quantity="{{ $modalRemainingQuantity }}"
                            data-linked-stock="{{ $modalItem ? $modalStock : '' }}"
                            data-linked-unit-cost="{{ $modalItem ? $modalItem->unit_cost : '' }}"
                            data-existing-estimate="{{ $modalRequest->estimated_unit_cost }}"
                            data-has-existing-link="{{ $modalItem ? '1' : '0' }}"
                        >
                            @csrf
                            <div class="modal-section-title">Approve / Process</div>

                            <div class="inline">
                                <div>
                                    <label>Action</label>
                                    <div class="readonly-action">{{ $modalActionLabel }}</div>
                                    <input type="hidden" name="decision" value="{{ $modalDecision }}" data-decision-select>
                                </div>
                                <div>
                                    <label>Approved Qty</label>
                                    <input type="number" name="approved_quantity" min="1" max="{{ $modalRemainingQuantity }}" placeholder="{{ $modalRemainingQuantity }}" data-approved-quantity>
                                </div>
                            </div>

                            <div data-new-inventory-section style="display:none;">
                                <div class="modal-section-title" style="margin-bottom:10px;">
                                    {{ $modalItem ? 'Restock Existing Inventory Item' : 'New Inventory Item' }}
                                </div>
                                <div class="inventory-fields">
                                    <div>
                                        <label>Item Name *</label>
                                        <input name="inventory_name" value="{{ $modalItem?->name ?? $modalRequest->material_name }}" maxlength="255" data-inventory-name>
                                    </div>
                                    <div>
                                        <label>Category</label>
                                        <select name="inventory_category" data-inventory-category>
                                            <option value="">Select category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category }}" @selected(($modalItem?->category ?? $modalRequest->material_category) === $category)>{{ $category }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label>Unit *</label>
                                        <input name="inventory_unit" value="{{ $modalItem?->unit ?? $modalRequest->unit }}" maxlength="50" data-inventory-unit>
                                    </div>
                                    <div>
                                        <label>Quantity</label>
                                        <input type="number" value="{{ $modalProcurementQuantity }}" readonly>
                                    </div>
                                    <div class="field-wide">
                                        <label>Description</label>
                                        <input name="inventory_description" value="{{ $modalItem?->description ?? $modalRequest->purpose }}" maxlength="2000">
                                    </div>
                                </div>
                            </div>

                            <div data-estimated-cost-wrap style="display:none;">
                                <label data-unit-cost-label>Estimated Unit Cost (PHP)</label>
                                <input type="number" name="estimated_unit_cost" min="0.01" step="0.01" value="{{ $modalRequest->estimated_unit_cost }}" data-estimated-unit-cost>
                                <div class="muted" style="margin-top:5px;" data-unit-cost-help>Required to reserve this procurement against the project budget.</div>
                            </div>

                            <div class="commitment-note" data-commitment-preview style="display:none;">
                                <span data-preview-label>Estimated project budget reservation</span>
                                <strong>PHP 0.00</strong>
                            </div>

                            <div>
                                <label>Reason / Note</label>
                                <textarea name="procurement_note" placeholder="Optional approval or procurement note" data-procurement-note></textarea>
                            </div>

                            <div class="muted" data-approve-hint style="display:none;">
                                Select an existing inventory item before issuing.
                            </div>
                            <button class="btn btn-approve" type="submit" data-approve-submit>Approve / Process</button>
                        </form>

                        <form class="modal-section" method="POST" action="{{ route('material-requests.reject', $modalRequest) }}">
                            @csrf
                            <div class="modal-section-title">Reject Request</div>
                            <div>
                                <label>Rejection Reason</label>
                                <textarea name="rejection_reason" required></textarea>
                            </div>
                            <button class="btn btn-reject" type="submit">Reject Request</button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>

<script>
    (() => {
        const pulseUrl = @js(route('material-requests.pulse'));
        const liveStatus = document.getElementById('approvalLiveStatus');
        let signature = null;
        let hasLoadedBaseline = false;

        const userIsEditing = () => {
            if (document.querySelector('.modal-backdrop.open')) return true;

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
                        liveStatus.textContent = 'Updates paused';
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

    function openReviewModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;

        modal.classList.add('open');
        const closeButton = modal.querySelector('.modal-close');
        if (closeButton) closeButton.focus();
    }

    function closeReviewModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;

        modal.classList.remove('open');
    }

    document.addEventListener('click', event => {
        if (event.target.classList.contains('modal-backdrop')) {
            event.target.classList.remove('open');
        }
    });

    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape') return;

        document.querySelectorAll('.modal-backdrop.open').forEach(modal => {
            modal.classList.remove('open');
        });
    });

    document.querySelectorAll('[data-approve-form]').forEach(form => {
        const decision = form.querySelector('[data-decision-select]');
        const quantity = form.querySelector('[data-approved-quantity]');
        const procurementNote = form.querySelector('[data-procurement-note]');
        const submit = form.querySelector('[data-approve-submit]');
        const approveHint = form.querySelector('[data-approve-hint]');
        const estimatedCostWrap = form.querySelector('[data-estimated-cost-wrap]');
        const estimatedUnitCost = form.querySelector('[data-estimated-unit-cost]');
        const commitmentPreview = form.querySelector('[data-commitment-preview]');
        const previewLabel = form.querySelector('[data-preview-label]');
        const unitCostLabel = form.querySelector('[data-unit-cost-label]');
        const unitCostHelp = form.querySelector('[data-unit-cost-help]');
        const newInventorySection = form.querySelector('[data-new-inventory-section]');
        const inventoryName = form.querySelector('[data-inventory-name]');
        const inventoryUnit = form.querySelector('[data-inventory-unit]');
        const requestedQuantity = Number(form.dataset.requestedQuantity || 0);

        function hasLinkedInventory() {
            return form.dataset.linkedStock !== '';
        }

        function selectedStock() {
            const stock = form.dataset.linkedStock;
            return stock === '' || stock === undefined ? null : Number(stock);
        }

        function selectedUnitCost() {
            const cost = form.dataset.linkedUnitCost;
            return cost === '' || cost === undefined ? null : Number(cost);
        }

        function updateCommitmentPreview() {
            if (!estimatedUnitCost || !commitmentPreview) return;

            const unitCost = Number(estimatedUnitCost.value || 0);
            const total = unitCost * requestedQuantity;
            commitmentPreview.querySelector('strong').textContent = `PHP ${total.toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}`;
        }

        function autofillProcurementNote(action, stock, approvedQuantity) {
            if (!procurementNote) return;

            if (action === 'waiting') {
                procurementNote.value = stock === null
                    ? 'Material is not linked to inventory. Waiting for procurement before issuance.'
                    : 'Available stock is insufficient. Waiting for procurement before issuance.';
                return;
            }

            if (action === 'partial') {
                procurementNote.value = approvedQuantity
                    ? `Partially approved ${approvedQuantity} of ${requestedQuantity}. Remaining quantity requires procurement.`
                    : 'No available stock for partial issuance. Waiting for procurement.';
                return;
            }

            if (action === 'procure_issue') {
                procurementNote.value = form.dataset.hasExistingLink === '1'
                    ? 'Procured quantity will be added to the linked inventory item and issued directly to the requested project.'
                    : 'Procured inventory item will be created and issued directly to the requested project.';
                return;
            }

            procurementNote.value = 'Approved and issued from available inventory.';
        }

        function autofillApprovedQuantity() {
            if (!decision || !quantity) return;

            const action = decision.value;
            const stock = selectedStock();
            let approvedQuantity = '';
            const isProcureAndIssue = action === 'procure_issue';
            const needsProcurementCost = action === 'waiting' || isProcureAndIssue;
            const canProcess = action === 'waiting'
                || isProcureAndIssue
                || (!isProcureAndIssue && hasLinkedInventory());

            quantity.disabled = action === 'waiting' || isProcureAndIssue;
            submit.disabled = !canProcess;
            submit.title = canProcess ? '' : 'Link an inventory item or choose a procurement action.';
            approveHint.style.display = canProcess ? 'none' : 'block';
            estimatedCostWrap.style.display = needsProcurementCost ? 'block' : 'none';
            commitmentPreview.style.display = needsProcurementCost ? 'block' : 'none';
            estimatedUnitCost.required = needsProcurementCost;

            if (newInventorySection) {
                newInventorySection.style.display = isProcureAndIssue ? 'block' : 'none';
            }
            if (inventoryName) inventoryName.required = isProcureAndIssue;
            if (inventoryUnit) inventoryUnit.required = isProcureAndIssue;

            if (unitCostLabel) {
                unitCostLabel.textContent = isProcureAndIssue ? 'Actual Unit Cost (PHP) *' : 'Estimated Unit Cost (PHP) *';
            }
            if (unitCostHelp) {
                unitCostHelp.textContent = isProcureAndIssue
                    ? 'Used to create the inventory assignment and project ledger expense.'
                    : 'Reserves this procurement against the project budget without creating an expense.';
            }
            if (previewLabel) {
                previewLabel.textContent = isProcureAndIssue
                    ? 'Project ledger expense after issuance'
                    : 'Estimated project budget reservation';
            }

            if (needsProcurementCost) {
                if (!estimatedUnitCost.value) {
                    estimatedUnitCost.value = form.dataset.existingEstimate || selectedUnitCost() || '';
                }
                quantity.value = isProcureAndIssue ? requestedQuantity : '';
                autofillProcurementNote(action, stock, null);
                updateCommitmentPreview();
                return;
            }

            if (action === 'partial') {
                if (stock !== null && stock > 0) {
                    approvedQuantity = Math.min(stock, requestedQuantity);
                    quantity.value = approvedQuantity;
                    autofillProcurementNote(action, stock, approvedQuantity);
                    return;
                }

                quantity.value = '';
                autofillProcurementNote(action, stock, null);
                return;
            }

            approvedQuantity = requestedQuantity || '';
            quantity.value = approvedQuantity;
            autofillProcurementNote(action, stock, approvedQuantity);
        }

        decision?.addEventListener('change', autofillApprovedQuantity);
        estimatedUnitCost?.addEventListener('input', updateCommitmentPreview);
        autofillApprovedQuantity();
    });
</script>
@endsection
