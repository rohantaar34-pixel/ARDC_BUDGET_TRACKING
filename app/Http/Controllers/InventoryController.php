<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryAssignment;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    //  INDEX — List all inventory items
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        $items = InventoryItem::withTrashed(false)
            ->withCount('assignments')
            ->orderBy('name')
            ->get();

        $projects = Project::orderBy('name')->get();

        $summary = [
            'total_items'        => $items->count(),
            'total_stock_value'  => $items->sum(fn($i) => $i->quantity * $i->unit_cost),
            'total_assigned_val' => InventoryAssignment::sum('total_cost'),
            'low_stock_count'    => $items->where('quantity', '<=', 5)->count(),
        ];

        return view('inventory.index', compact('items', 'projects', 'summary'));
    }

    // ─────────────────────────────────────────────────────────────
    //  STORE — Create a new inventory item
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'nullable|string|max:100',
            'unit'        => 'required|string|max:50',
            'unit_cost'   => 'required|numeric|min:0',
            'quantity'    => 'required|integer|min:0',
        ]);

        InventoryItem::create($validated);

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory item "' . $validated['name'] . '" added successfully.');
    }

    // ─────────────────────────────────────────────────────────────
    //  EDIT — Show edit form (returns JSON for modal)
    // ─────────────────────────────────────────────────────────────
    public function edit(InventoryItem $inventory)
    {
        return response()->json($inventory);
    }

    // ─────────────────────────────────────────────────────────────
    //  UPDATE — Save edits
    // ─────────────────────────────────────────────────────────────
    public function update(Request $request, InventoryItem $inventory)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'nullable|string|max:100',
            'unit'        => 'required|string|max:50',
            'unit_cost'   => 'required|numeric|min:0',
            'quantity'    => 'required|integer|min:0',
        ]);

        $inventory->update($validated);

        return redirect()->route('inventory.index')
            ->with('success', 'Item "' . $inventory->name . '" updated.');
    }

    // ─────────────────────────────────────────────────────────────
    //  DESTROY — Soft delete
    // ─────────────────────────────────────────────────────────────
    public function destroy(InventoryItem $inventory)
    {
        $inventory->delete();

        return redirect()->route('inventory.index')
            ->with('success', 'Item "' . $inventory->name . '" removed from inventory.');
    }

    // ─────────────────────────────────────────────────────────────
    //  ASSIGN — Show assign-to-project form
    // ─────────────────────────────────────────────────────────────
    public function assign(InventoryItem $inventory)
    {
        $projects = Project::orderBy('name')->get();

        return view('inventory.assign', compact('inventory', 'projects'));
    }

    // ─────────────────────────────────────────────────────────────
    //  DO ASSIGN — Core logic: deduct stock, create expense, log assignment
    // ─────────────────────────────────────────────────────────────
    public function doAssign(Request $request, InventoryItem $inventory)
    {
        $validated = $request->validate([
            'project_id'        => 'required|exists:projects,id',
            'quantity_assigned'  => 'required|integer|min:1|max:' . $inventory->quantity,
            'notes'             => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($validated, $inventory) {
            $qty       = $validated['quantity_assigned'];
            $unitCost  = $inventory->unit_cost;
            $totalCost = $qty * $unitCost;
            $project   = Project::findOrFail($validated['project_id']);

            // 1. Get or create the "Inventory" expense category
            $category = ExpenseCategory::firstOrCreate(
                ['name' => 'Inventory'],
                ['name' => 'Inventory']
            );

            // 2. Auto-create expense transaction on the target project
            $transaction = Transaction::create([
                'project_id'          => $project->id,
                'type'                => 'expense',
                'expense_category_id' => $category->id,
                'expense_name'        => '[INVENTORY] ' . $inventory->name . ' ×' . $qty,
                'category'            => 'Inventory',
                'amount'              => $totalCost,
                'description'         => 'Auto-deducted from inventory assignment. ' .
                                         $qty . ' ' . $inventory->unit . ' × ₱' . number_format($unitCost, 2) .
                                         ($validated['notes'] ? ' | Notes: ' . $validated['notes'] : ''),
                'transaction_date'    => now()->toDateString(),
                'invoice_ref'         => null,
                'client_name'         => null,
            ]);

            // 3. Deduct from inventory stock
            $inventory->decrement('quantity', $qty);

            // 4. Log the assignment
            InventoryAssignment::create([
                'inventory_item_id'      => $inventory->id,
                'project_id'             => $project->id,
                'transaction_id'         => $transaction->id,
                'quantity_assigned'      => $qty,
                'unit_cost_at_assignment'=> $unitCost,
                'total_cost'             => $totalCost,
                'assigned_by'            => Auth::user()->name ?? 'System',
                'notes'                  => $validated['notes'] ?? null,
            ]);
        });

        $project = Project::findOrFail($validated['project_id']);

        return redirect()->route('inventory.index')
            ->with('success',
                $validated['quantity_assigned'] . ' unit(s) of "' . $inventory->name .
                '" assigned to "' . $project->name . '" — ₱' .
                number_format($validated['quantity_assigned'] * $inventory->unit_cost, 2) .
                ' auto-deducted from project budget.'
            );
    }

    // ─────────────────────────────────────────────────────────────
    //  ASSIGNMENTS — View all assignments for an item (JSON)
    // ─────────────────────────────────────────────────────────────
    public function assignments(InventoryItem $inventory)
    {
        $assignments = $inventory->assignments()
            ->with('project')
            ->latest()
            ->get()
            ->map(fn($a) => [
                'id'               => $a->id,
                'project_name'     => $a->project->name,
                'quantity'         => $a->quantity_assigned,
                'unit_cost'        => $a->unit_cost_at_assignment,
                'total_cost'       => $a->total_cost,
                'assigned_by'      => $a->assigned_by,
                'notes'            => $a->notes,
                'date'             => $a->created_at->format('M d, Y'),
            ]);

        return response()->json($assignments);
    }
}
