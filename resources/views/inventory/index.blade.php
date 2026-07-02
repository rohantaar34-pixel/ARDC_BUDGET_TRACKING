{{-- resources/views/inventory/index.blade.php --}}
@extends('layouts.app')

@section('content')
<style>
    :root {
        --inv: #0891b2;
        --inv-dark: #0e7490;
        --inv-light: #ecfeff;
        --green: #059669;
        --red: #dc2626;
        --orange: #ea580c;
        --ink: #111827;
        --ink-2: #374151;
        --ink-3: #6b7280;
        --ink-4: #9ca3af;
        --border: #e8e8ed;
        --bg: #f8f8fb;
        --white: #ffffff;
        --radius: 14px;
        --radius-sm: 9px;
    }
    * { box-sizing: border-box; }

    .inv-wrap { font-family: 'Montserrat', sans-serif; background: var(--bg); min-height: 100vh; color: var(--ink); }
    .inv-top { padding: 20px 20px 0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
    .inv-title { font-size: 22px; font-weight: 800; letter-spacing: -.03em; margin: 0; }
    .inv-sub { font-size: 13px; color: var(--ink-4); margin: 3px 0 0; }

    /* Stats strip */
    .stat-strip { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 18px 20px 0; }
    .stat-card { background: var(--white); border: 1px solid var(--border); border-radius: var(--radius); padding: 14px 16px; }
    .stat-lbl { font-size: 10px; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; color: var(--ink-4); margin-bottom: 4px; }
    .stat-val { font-size: 22px; font-weight: 800; letter-spacing: -.03em; }
    .c-inv { color: var(--inv); }
    .c-green { color: var(--green); }
    .c-red { color: var(--red); }
    .c-orange { color: var(--orange); }

    /* FAB */
    .fab-wrap { padding: 18px 20px 0; }
    .fab-toggle { width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 13px; background: var(--inv); color: #fff; border: none; border-radius: var(--radius); font-size: 14px; font-weight: 700; cursor: pointer; transition: background .15s; font-family: 'Montserrat', sans-serif; }
    .fab-toggle:hover { background: var(--inv-dark); }
    .action-row { display: grid; grid-template-columns: 1fr; gap: 10px; }
    .report-actions { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .report-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 42px; padding: 10px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border); background: #fff; color: var(--ink-2); text-decoration: none; font-size: 12px; font-weight: 800; font-family: 'Montserrat', sans-serif; transition: all .15s; }
    .report-btn:hover { border-color: var(--inv); color: var(--inv); box-shadow: 0 4px 12px rgba(8,145,178,.10); }
    .report-btn.excel { color: var(--green); }
    .report-btn.pdf { color: var(--red); }
    .report-btn.word { color: var(--inv); }

    /* Create form slide */
    .create-form-wrap { margin: 10px 20px 0; background: var(--white); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; max-height: 0; opacity: 0; transition: max-height .35s ease, opacity .25s ease; }
    .create-form-wrap.open { max-height: 700px; opacity: 1; }
    .create-form { padding: 20px; display: flex; flex-direction: column; gap: 12px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .field-lbl { display: block; font-size: 12px; font-weight: 600; color: var(--ink-3); margin-bottom: 4px; }
    .field-in { width: 100%; padding: 10px 13px; font-size: 14px; border: 1px solid #e5e7eb; border-radius: var(--radius-sm); background: #fafafa; color: var(--ink); outline: none; transition: border-color .15s; font-family: 'Montserrat', sans-serif; }
    .field-in:focus { border-color: var(--inv); background: #fff; }
    .btn-create { width: 100%; padding: 12px; background: var(--inv); color: #fff; font-size: 14px; font-weight: 700; border: none; border-radius: var(--radius-sm); cursor: pointer; transition: background .15s; font-family: 'Montserrat', sans-serif; }
    .btn-create:hover { background: var(--inv-dark); }
    .form-title { font-size: 15px; font-weight: 700; color: var(--ink); border-bottom: 1px solid var(--border); padding-bottom: 12px; }

    /* Section */
    .section-label { font-size: 10px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--ink-4); padding: 22px 20px 10px; }

    /* Item cards */
    .item-list { padding: 0 20px; display: flex; flex-direction: column; gap: 10px; }
    .item-card { background: var(--white); border: 1px solid var(--border); border-radius: var(--radius); padding: 16px 18px; display: flex; align-items: center; gap: 14px; position: relative; transition: box-shadow .15s; }
    .item-card:hover { box-shadow: 0 4px 16px rgba(8,145,178,.08); }
    .item-icon { width: 44px; height: 44px; background: var(--inv-light); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .item-body { flex: 1; min-width: 0; }
    .item-name { font-size: 14px; font-weight: 700; color: var(--ink); margin-bottom: 3px; }
    .item-meta { display: flex; flex-wrap: wrap; gap: 8px; font-size: 11px; color: var(--ink-4); margin-bottom: 6px; }
    .item-badge { background: var(--inv-light); color: var(--inv); padding: 2px 8px; border-radius: 20px; font-weight: 600; font-size: 10px; }
    .stock-bar { height: 4px; background: #f0f0f5; border-radius: 99px; }
    .stock-fill { height: 4px; border-radius: 99px; transition: width .4s ease; }

    .item-right { text-align: right; flex-shrink: 0; display: flex; flex-direction: column; gap: 8px; align-items: flex-end; }
    .item-cost { font-size: 16px; font-weight: 800; color: var(--inv); letter-spacing: -.02em; }
    .item-cost-lbl { font-size: 10px; color: var(--ink-4); }
    .item-actions { display: flex; gap: 6px; }
    .btn-sm { padding: 6px 12px; font-size: 11px; font-weight: 700; border: none; border-radius: 7px; cursor: pointer; font-family: 'Montserrat', sans-serif; transition: all .15s; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
    .btn-assign { background: var(--inv); color: #fff; }
    .btn-assign:hover { background: var(--inv-dark); }
    .btn-edit { background: #f3f4f6; color: var(--ink-2); }
    .btn-edit:hover { background: #e5e7eb; }
    .btn-del { background: #fef2f2; color: var(--red); }
    .btn-del:hover { background: #fee2e2; }

    /* Low stock badge */
    .badge-low { background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 700; }
    .badge-out { background: #fef2f2; color: var(--red); padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 700; }

    /* Empty state */
    .empty-state { text-align: center; padding: 50px 20px; background: var(--white); border: 1px dashed var(--border); border-radius: var(--radius); }
    .empty-state p { font-size: 14px; color: var(--ink-4); margin: 10px 0 0; }

    /* Edit Modal */
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 999; display: flex; align-items: center; justify-content: center; padding: 20px; opacity: 0; pointer-events: none; transition: opacity .2s; }
    .modal-overlay.open { opacity: 1; pointer-events: auto; }
    .modal-box { background: #fff; border-radius: 18px; padding: 28px; width: 100%; max-width: 520px; transform: translateY(20px); transition: transform .2s; }
    .modal-overlay.open .modal-box { transform: translateY(0); }
    .modal-title { font-size: 17px; font-weight: 800; margin-bottom: 20px; }
    .modal-close { float: right; background: none; border: none; font-size: 22px; cursor: pointer; color: var(--ink-4); line-height: 1; }

    /* Assignments panel */
    .assign-panel { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 10px; padding: 12px 14px; margin-top: 8px; font-size: 12px; display: none; }
    .assign-panel.open { display: block; }
    .assign-row { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid #bae6fd; }
    .assign-row:last-child { border-bottom: none; }

    .page-bottom { height: 32px; }

    @media (min-width: 640px) {
        .inv-top { padding: 28px 32px 0; }
        .stat-strip { padding: 20px 32px 0; grid-template-columns: repeat(4, 1fr); }
        .fab-wrap { padding: 20px 32px 0; }
        .action-row { grid-template-columns: minmax(240px, 1fr) auto; align-items: center; }
        .report-actions { grid-template-columns: repeat(3, 104px); }
        .create-form-wrap { margin: 10px 32px 0; }
        .section-label { padding: 24px 32px 10px; }
        .item-list { padding: 0 32px; }
        .form-row { grid-template-columns: 1fr 1fr 1fr; }
    }
</style>

<div class="inv-wrap">

    {{-- Back to Dashboard --}}
    <a href="{{ route('dashboard') }}" style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:linear-gradient(135deg,var(--inv) 0%,var(--inv-dark) 100%);color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:700;text-decoration:none;transition:all .3s;box-shadow:0 2px 8px rgba(8,145,178,.3);margin:16px 20px 0;" href="{{ route('dashboard') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Dashboard
    </a>

    {{-- Header --}}
    <div class="inv-top">
        <div>
            <h1 class="inv-title">Project Inventory</h1>
            <p class="inv-sub">Manage stock & assign materials to projects</p>
        </div>
        <div style="width:38px;height:38px;background:var(--inv-light);border-radius:11px;display:flex;align-items:center;justify-content:center;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--inv)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.29 7 12 12 20.71 7"/><line x1="12" y1="22" x2="12" y2="12"/></svg>
        </div>
    </div>

    {{-- Stats --}}
    <div class="stat-strip">
        <div class="stat-card">
            <div class="stat-lbl">Total Items</div>
            <div class="stat-val c-inv">{{ $summary['total_items'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-lbl">Stock Value</div>
            <div class="stat-val c-green">₱{{ number_format($summary['total_stock_value'], 0) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-lbl">Total Assigned</div>
            <div class="stat-val c-orange">₱{{ number_format($summary['total_assigned_val'], 0) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-lbl">Low Stock</div>
            <div class="stat-val c-red">{{ $summary['low_stock_count'] }}</div>
        </div>
    </div>

    {{-- Add Item Button --}}
    <div class="fab-wrap">
        <div class="action-row">
            <button class="fab-toggle" onclick="toggleCreate()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Inventory Item
            </button>
            <div class="report-actions">
                <a href="{{ route('inventory.report.excel') }}" class="report-btn excel" title="Download Excel inventory report">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m8 13 4 4 4-4"/><path d="M12 17V9"/></svg>
                    Excel
                </a>
                <a href="{{ route('inventory.report.pdf') }}" class="report-btn pdf" title="Download PDF inventory report">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 15h6"/><path d="M9 18h6"/><path d="M9 12h2"/></svg>
                    PDF
                </a>
                <a href="{{ route('inventory.report.word') }}" class="report-btn word" title="Download Word inventory report">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h8"/></svg>
                    Word
                </a>
            </div>
        </div>
    </div>

    {{-- Create Form --}}
    <div class="create-form-wrap" id="createFormWrap">
        <form class="create-form" method="POST" action="{{ route('inventory.store') }}">
            @csrf
            <div class="form-title">➕ New Inventory Item</div>
            <div class="form-row">
                <div>
                    <label class="field-lbl">Item Name *</label>
                    <input class="field-in" type="text" name="name" placeholder="e.g. Cement Bags" required value="{{ old('name') }}">
                </div>
                <div>
                    <label class="field-lbl">Category</label>
                    <input class="field-in" type="text" name="category" placeholder="e.g. Materials" value="{{ old('category') }}" list="cat-list">
                    <datalist id="cat-list">
                        <option value="Materials">
                        <option value="Equipment">
                        <option value="Supplies">
                        <option value="Tools">
                        <option value="Consumables">
                    </datalist>
                </div>
                <div>
                    <label class="field-lbl">Unit *</label>
                    <input class="field-in" type="text" name="unit" placeholder="pcs / kg / m" required value="{{ old('unit', 'pcs') }}" list="unit-list">
                    <datalist id="unit-list">
                        <option value="pcs"><option value="kg"><option value="m"><option value="liters"><option value="bags"><option value="rolls"><option value="sets">
                    </datalist>
                </div>
            </div>
            <div class="form-row">
                <div>
                    <label class="field-lbl">Unit Cost (₱) *</label>
                    <input class="field-in" type="number" name="unit_cost" placeholder="0.00" step="0.01" min="0" required value="{{ old('unit_cost') }}">
                </div>
                <div>
                    <label class="field-lbl">Quantity *</label>
                    <input class="field-in" type="number" name="quantity" placeholder="0" min="0" required value="{{ old('quantity') }}">
                </div>
                <div>
                    <label class="field-lbl">Description</label>
                    <input class="field-in" type="text" name="description" placeholder="Optional notes" value="{{ old('description') }}">
                </div>
            </div>
            <button type="submit" class="btn-create">Save Item</button>
        </form>
    </div>

    {{-- Items List --}}
    <div class="section-label">All Items ({{ $items->count() }})</div>

    <div class="item-list">
        @forelse($items as $item)
            @php
                $totalQty = $item->quantity;
                $assignedQty = $item->total_assigned_quantity;
                $stockPct = $totalQty > 0 ? min(($totalQty / max($totalQty + $assignedQty, 1)) * 100, 100) : 0;
                $stockColor = $totalQty === 0 ? '#dc2626' : ($totalQty <= 5 ? '#ea580c' : '#059669');
            @endphp
            <div class="item-card" id="card-{{ $item->id }}">
                <div class="item-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--inv)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.29 7 12 12 20.71 7"/><line x1="12" y1="22" x2="12" y2="12"/></svg>
                </div>
                <div class="item-body">
                    <div class="item-name">{{ $item->name }}</div>
                    <div class="item-meta">
                        @if($item->category)
                            <span class="item-badge">{{ $item->category }}</span>
                        @endif
                        <span>{{ number_format($item->quantity) }} {{ $item->unit }} in stock</span>
                        @if($item->quantity === 0)
                            <span class="badge-out">OUT OF STOCK</span>
                        @elseif($item->quantity <= 5)
                            <span class="badge-low">LOW STOCK</span>
                        @endif
                        @if($item->assignments_count > 0)
                            <span style="cursor:pointer;color:var(--inv);text-decoration:underline;" onclick="toggleAssignments({{ $item->id }})">{{ $item->assignments_count }} assignment(s) ▾</span>
                        @endif
                    </div>
                    <div class="stock-bar">
                        <div class="stock-fill" style="width:{{ $stockPct }}%;background:{{ $stockColor }};"></div>
                    </div>
                    {{-- Assignments mini-panel --}}
                    <div class="assign-panel" id="panel-{{ $item->id }}">
                        <div style="font-weight:700;margin-bottom:6px;">Assignment History</div>
                        <div id="panel-content-{{ $item->id }}">Loading…</div>
                    </div>
                </div>
                <div class="item-right">
                    <div>
                        <div class="item-cost">₱{{ number_format($item->unit_cost, 2) }}</div>
                        <div class="item-cost-lbl">per {{ $item->unit }}</div>
                    </div>
                    <div class="item-actions">
                        @if($item->quantity > 0)
                        <a href="{{ route('inventory.assign', $item) }}" class="btn-sm btn-assign">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                            Assign
                        </a>
                        @endif
                        <button class="btn-sm btn-edit" onclick="openEdit({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ addslashes($item->category) }}', '{{ addslashes($item->unit) }}', {{ $item->unit_cost }}, {{ $item->quantity }}, '{{ addslashes($item->description) }}')">Edit</button>
                        <form method="POST" action="{{ route('inventory.destroy', $item) }}" style="display:inline;" onsubmit="return confirm('Delete {{ addslashes($item->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-sm btn-del">Del</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--inv)" stroke-width="1.5" stroke-linecap="round" style="margin:0 auto;display:block;opacity:.4;"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                <p>No inventory items yet — tap <strong>Add Inventory Item</strong> to start.</p>
            </div>
        @endforelse
    </div>

    <div class="page-bottom"></div>
</div>

{{-- Edit Modal --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeEdit()">×</button>
        <div class="modal-title">✏️ Edit Inventory Item</div>
        <form method="POST" id="editForm" action="">
            @csrf @method('PUT')
            <div style="display:flex;flex-direction:column;gap:12px;">
                <div class="form-row" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label class="field-lbl">Item Name *</label>
                        <input class="field-in" type="text" name="name" id="edit_name" required>
                    </div>
                    <div>
                        <label class="field-lbl">Category</label>
                        <input class="field-in" type="text" name="category" id="edit_category" list="cat-list">
                    </div>
                </div>
                <div class="form-row" style="grid-template-columns:1fr 1fr 1fr;">
                    <div>
                        <label class="field-lbl">Unit *</label>
                        <input class="field-in" type="text" name="unit" id="edit_unit" required list="unit-list">
                    </div>
                    <div>
                        <label class="field-lbl">Unit Cost (₱) *</label>
                        <input class="field-in" type="number" name="unit_cost" id="edit_unit_cost" step="0.01" min="0" required>
                    </div>
                    <div>
                        <label class="field-lbl">Quantity *</label>
                        <input class="field-in" type="number" name="quantity" id="edit_quantity" min="0" required>
                    </div>
                </div>
                <div>
                    <label class="field-lbl">Description</label>
                    <input class="field-in" type="text" name="description" id="edit_description">
                </div>
                <button type="submit" class="btn-create" style="background:var(--inv);">Update Item</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleCreate() {
    const wrap = document.getElementById('createFormWrap');
    wrap.classList.toggle('open');
}

@if($errors->any() || old('name'))
document.getElementById('createFormWrap').classList.add('open');
@endif

function openEdit(id, name, category, unit, unitCost, qty, desc) {
    document.getElementById('editForm').action = '/inventory/' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_category').value = category;
    document.getElementById('edit_unit').value = unit;
    document.getElementById('edit_unit_cost').value = unitCost;
    document.getElementById('edit_quantity').value = qty;
    document.getElementById('edit_description').value = desc;
    document.getElementById('editModal').classList.add('open');
}

function closeEdit() {
    document.getElementById('editModal').classList.remove('open');
}

document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEdit();
});

function toggleAssignments(id) {
    const panel = document.getElementById('panel-' + id);
    const content = document.getElementById('panel-content-' + id);
    if (panel.classList.contains('open')) {
        panel.classList.remove('open');
        return;
    }
    panel.classList.add('open');
    fetch('/inventory/' + id + '/assignments')
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                content.innerHTML = '<div style="color:#6b7280;font-style:italic;">No assignments yet.</div>';
                return;
            }
            content.innerHTML = data.map(a =>
                `<div class="assign-row">
                    <span><strong>${a.project_name}</strong> — ${a.quantity} ${''} units on ${a.date}</span>
                    <span style="font-weight:700;color:var(--inv);">₱${parseFloat(a.total_cost).toLocaleString('en-PH', {minimumFractionDigits:2})}</span>
                </div>`
            ).join('');
        });
}
</script>
@endsection
