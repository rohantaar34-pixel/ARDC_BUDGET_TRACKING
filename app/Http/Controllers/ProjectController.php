<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Transaction;
use App\Models\ExpenseCategory;
use App\Models\Document;
use App\Services\TransactionReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        try {
            $query = Project::withCount(['transactions']);

            if (Auth::user()->isEmployee()) {
                $assignedIds = Auth::user()->assignedProjects()->pluck('projects.id');
                $query->whereIn('id', $assignedIds);
            }

            $projects = $query
                ->orderBy('created_at', 'desc')
                ->get();

            $summary = [
                'total_projects' => $projects->count(),
                'total_budget' => $projects->sum('budget'),
                'total_budget_additions' => Transaction::where('type', 'budget_addition')->sum('amount'),
                'total_expense'  => Transaction::where('type', 'expense')->sum('amount'),
                'total_balance'  => $projects->sum('budget') 
                                  + Transaction::where('type', 'budget_addition')->sum('amount')
                                  - Transaction::where('type', 'expense')->sum('amount'),
            ];

            return view('projects.index', compact('projects', 'summary'));
        } catch (\Exception $e) {
            Log::error('Error in ProjectController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading projects: ' . $e->getMessage());
        }
    }

    public function show(Project $project)
    {
        try {
            if (Auth::user()->isEmployee() && !Auth::user()->assignedProjects()->whereKey($project->id)->exists()) {
                abort(403);
            }

            $expenseCategories = ExpenseCategory::orderBy('name')->get();

            $transactions = $project->transactions()
                ->with('expenseCategory')
                ->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            $expenses = $transactions->where('type', 'expense');
            $budgetAdditions = $transactions->where('type', 'budget_addition');

            // Group expenses by category NAME
            $transactionsByCategory = $expenses
                ->groupBy(fn($t) => $t->expenseCategory?->name ?? $t->category ?? 'Uncategorized')
                ->map(fn($items) => $items->sortBy('transaction_date'))
                ->sortByDesc(fn($items) => $items->sum('amount'));

            // Category totals keyed by name
            $categorySummary = $expenses
                ->groupBy(fn($t) => $t->expenseCategory?->name ?? $t->category ?? 'Uncategorized')
                ->map(fn($items) => $items->sum('amount'))
                ->sortDesc();

            // Monthly summary for expenses only
            $monthlySummary = $expenses
                ->groupBy(fn($t) => $t->transaction_date->format('Y-m'))
                ->map(fn($items) => [
                    'expense' => $items->sum('amount'),
                ])
                ->sortKeys();

            return view('projects.show', compact(
                'project',
                'transactions',
                'expenses',
                'budgetAdditions',
                'transactionsByCategory',
                'categorySummary',
                'expenseCategories',
                'monthlySummary'
            ));
        } catch (\Exception $e) {
            Log::error('Error in ProjectController@show: ' . $e->getMessage());
            return redirect()->route('projects.index')->with('error', 'Error loading project: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Project $project)
    {
        try {
            if (!Auth::user()->isAdmin()) {
                abort(403);
            }

            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'budget'      => 'nullable|numeric|min:0',
                'status'      => 'nullable|string|max:50',
            ]);

            $project->update($validated);

            return redirect()->route('projects.show', $project)
                ->with('success', 'Project updated successfully');
        } catch (\Exception $e) {
            Log::error('Error in ProjectController@update: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error updating project: ' . $e->getMessage());
        }
    }

    public function addTransaction(Request $request, Project $project)
{
    try {
        $request->merge(['amount' => str_replace(['₱', ','], '', $request->amount)]);
        
        $validated = $request->validate([
            'type'                    => 'required|in:expense,budget_addition',
            'expense_category_search' => 'nullable|string|max:100',
            'new_category'            => 'nullable|string|max:100',
            'expense_name'            => 'required|string|max:255',
            'amount'                  => 'required|numeric|min:0.01',
            'description'             => 'nullable|string|max:500',
            'transaction_date'        => 'required|date',
            'invoice_ref'             => 'nullable|string|max:100',
            'proof_image'             => 'nullable|image|max:10240',
            'client_name'             => 'nullable|string|max:255',
        ]);

        // Handle category
        if ($validated['type'] === 'expense') {
            // Check if it's a new category from the "other" option
            if ($request->input('expense_category_search') === 'other' && !empty($request->input('new_category'))) {
                $categoryName = trim($request->input('new_category'));
                $category = ExpenseCategory::firstOrCreate(['name' => $categoryName]);
                $validated['expense_category_id'] = $category->id;
                $validated['category'] = $category->name;
            } elseif (!empty($request->input('expense_category_search')) && $request->input('expense_category_search') !== 'other') {
                // Existing category selected
                $categoryName = trim($request->input('expense_category_search'));
                $category = ExpenseCategory::firstOrCreate(['name' => $categoryName]);
                $validated['expense_category_id'] = $category->id;
                $validated['category'] = $category->name;
            } else {
                $validated['category'] = 'Uncategorized';
            }
        } else {
            $validated['category'] = 'Budget Addition';
        }

        // Remove non-database fields
        unset($validated['expense_category_search']);
        unset($validated['new_category']);

        // Store proof image
        if ($request->hasFile('proof_image')) {
            $validated['proof_image'] = $request->file('proof_image')->store('proof_images', 'public');
        }

        $project->transactions()->create($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Transaction recorded successfully');
            
    } catch (\Exception $e) {
        Log::error('Error: ' . $e->getMessage());
        return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
    }
}

    public function deleteTransaction(Project $project, Transaction $transaction)
    {
        try {
            if ($transaction->project_id !== $project->id) {
                return redirect()->route('projects.show', $project)
                    ->with('error', 'Transaction not found for this project');
            }

            $transaction->delete();

            return redirect()->route('projects.show', $project)
                ->with('success', 'Transaction deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error in ProjectController@deleteTransaction: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error deleting transaction: ' . $e->getMessage());
        }
    }

    public function addBudget(Request $request, Project $project)
    {
        try {
            $validated = $request->validate([
                'amount'      => 'required|numeric|min:0.01',
                'client_name' => 'nullable|string|max:255',
                'notes'       => 'nullable|string|max:500',
                'date'        => 'required|date',
            ]);

            $project->transactions()->create([
                'type' => 'budget_addition',
                'expense_name' => $validated['client_name'] ? "Payment from {$validated['client_name']}" : 'Client Payment',
                'amount' => $validated['amount'],
                'description' => $validated['notes'],
                'transaction_date' => $validated['date'],
                'client_name' => $validated['client_name'],
                'category' => 'Budget Addition',
            ]);

            return redirect()->route('projects.show', $project)
                ->with('success', 'Budget increased successfully');
        } catch (\Exception $e) {
            Log::error('Error in ProjectController@addBudget: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error adding budget: ' . $e->getMessage());
        }
    }

    /**
     * Export project transactions to various formats
     */
    public function exportReport(Project $project, $format, Request $request, TransactionReportService $reportService)
    {
        try {
            $filters = $request->only(['type', 'category', 'date_from', 'date_to']);
            
            switch ($format) {
                case 'excel':
                    $filepath = $reportService->generateExcel($project, $filters);
                    return Response::download($filepath)->deleteFileAfterSend(true);
                case 'pdf':
                    $filepath = $reportService->generatePDF($project, $filters);
                    return Response::download($filepath)->deleteFileAfterSend(true);
                case 'word':
                    $filepath = $reportService->generateWord($project, $filters);
                    return Response::download($filepath)->deleteFileAfterSend(true);
                default:
                    return back()->with('error', 'Invalid format');
            }
        } catch (\Exception $e) {
            Log::error('Error exporting report: ' . $e->getMessage());
            return back()->with('error', 'Error generating report: ' . $e->getMessage());
        }
    }

    /**
     * View proof image for a transaction
     */
    public function viewProof(Project $project, Transaction $transaction)
    {
        try {
            if ($transaction->project_id !== $project->id) {
                return response()->json(['error' => 'Transaction not found'], 404);
            }
            
            if (!$transaction->proof_image || !Storage::disk('public')->exists($transaction->proof_image)) {
                return response()->json(['error' => 'No proof image found'], 404);
            }
            
            $file = Storage::disk('public')->get($transaction->proof_image);
            $mime = Storage::disk('public')->mimeType($transaction->proof_image);
            
            return response($file, 200)->header('Content-Type', $mime);
        } catch (\Exception $e) {
            Log::error('Error viewing proof: ' . $e->getMessage());
            return response()->json(['error' => 'Error loading image'], 500);
        }
    }

    /**
     * Get transactions as JSON for AJAX requests
     */
    public function getTransactionsJson(Project $project, Request $request)
    {
        try {
            $query = $project->transactions()->with('expenseCategory');
            
            // Apply filters
            if ($request->has('type') && $request->type !== 'all') {
                $query->where('type', $request->type);
            }
            
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('expense_name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('category', 'like', "%{$search}%")
                      ->orWhere('client_name', 'like', "%{$search}%");
                });
            }
            
            if ($request->has('category') && $request->category) {
                $query->where('category', $request->category);
            }
            
            if ($request->has('date_from')) {
                $query->whereDate('transaction_date', '>=', $request->date_from);
            }
            
            if ($request->has('date_to')) {
                $query->whereDate('transaction_date', '<=', $request->date_to);
            }
            
            $perPage = $request->get('per_page', 10);
            $transactions = $query->orderBy('transaction_date', 'desc')->paginate($perPage);
            
            return response()->json($transactions);
        } catch (\Exception $e) {
            Log::error('Error in getTransactionsJson: ' . $e->getMessage());
            return response()->json(['error' => 'Error loading transactions'], 500);
        }
    }

    /**
     * Get category summary for AJAX requests
     */
    public function getCategorySummary(Project $project)
    {
        try {
            $summary = $project->transactions()
                ->where('type', 'expense')
                ->with('expenseCategory')
                ->get()
                ->groupBy(fn($t) => $t->expenseCategory?->name ?? $t->category ?? 'Uncategorized')
                ->map(fn($items) => [
                    'category' => $items->first()->category ?? 'Uncategorized',
                    'total' => $items->sum('amount'),
                    'count' => $items->count(),
                    'percentage' => $project->transactions()->where('type', 'expense')->sum('amount') > 0 
                        ? round(($items->sum('amount') / $project->transactions()->where('type', 'expense')->sum('amount')) * 100, 1)
                        : 0
                ])
                ->values();

            return response()->json($summary);
        } catch (\Exception $e) {
            Log::error('Error in getCategorySummary: ' . $e->getMessage());
            return response()->json(['error' => 'Error loading category summary'], 500);
        }
    }
}
