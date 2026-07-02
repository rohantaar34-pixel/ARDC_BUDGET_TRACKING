<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\InventoryAssignment;

class Project extends Model
{
    protected $fillable = ['name', 'description', 'budget', 'status', 'completion_percentage'];
    
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function inventoryAssignments(): HasMany
    {
        return $this->hasMany(InventoryAssignment::class);
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function monitoringReports(): HasMany
    {
        return $this->hasMany(ProjectMonitoringReport::class);
    }
    
    public function budgetAdditions(): HasMany
    {
        return $this->transactions()->where('type', 'budget_addition');
    }
    
    public function expenses(): HasMany
    {
        return $this->transactions()->where('type', 'expense');
    }
    
    public function getTotalBudgetAdditionsAttribute(): float
    {
        return $this->budgetAdditions()->sum('amount');
    }
    
    public function getTotalExpenseAttribute(): float
    {
        return $this->expenses()->sum('amount');
    }
    
    public function getCurrentBudgetAttribute(): float
    {
        return $this->budget + $this->total_budget_additions - $this->total_expense;
    }
    
    public function getBalanceAttribute(): float
    {
        return $this->current_budget;
    }
    
    public function getBudgetUtilizationAttribute(): float
    {
        $totalBudget = $this->budget + $this->total_budget_additions;
        if ($totalBudget <= 0) return 0;
        return round(($this->total_expense / $totalBudget) * 100, 2);
    }

    public function recalculateCompletion(): void
    {
        $completion = (int) $this->monitoringReports()
            ->where('status', ProjectMonitoringReport::STATUS_APPROVED)
            ->sum('estimated_completion_percentage');

        $completion = min($completion, 100);

        $this->forceFill([
            'completion_percentage' => $completion,
            'status' => $this->statusForCompletion($completion),
        ])->save();
    }

    public function statusForCompletion(int $completion): string
    {
        return match (true) {
            $completion <= 0 => 'not_started',
            $completion < 75 => 'in_progress',
            $completion < 100 => 'near_completion',
            default => 'completed',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'not_started' => 'Not Started',
            'in_progress' => 'In Progress',
            'near_completion' => 'Near Completion',
            'completed' => 'Completed',
            default => ucwords(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function getProgressColorAttribute(): string
    {
        return match (true) {
            $this->completion_percentage >= 100 => '#16a34a',
            $this->completion_percentage >= 75 => '#2563eb',
            $this->completion_percentage >= 50 => '#eab308',
            $this->completion_percentage >= 25 => '#f97316',
            default => '#dc2626',
        };
    }
}
