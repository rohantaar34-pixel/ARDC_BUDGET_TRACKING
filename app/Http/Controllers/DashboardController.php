<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isEmployee()) {
            return redirect()->route('monitoring.submit');
        }

        $projectQuery = Project::query();

        if ($user->isEmployee()) {
            $projectQuery->whereIn('id', $user->assignedProjects()->pluck('projects.id'));
        }

        // Get statistics for the dashboard
        $stats = [
            'total_projects' => (clone $projectQuery)->count(),
            'total_documents' => $user->isAdmin() ? Document::count() : 0,
            'total_budget' => (clone $projectQuery)->sum('budget'),
        ];
        
        // Get all projects for the dashboard to display their current budgets
        $projects = $projectQuery->orderBy('created_at', 'desc')->get();

        return view('dashboard', compact('stats', 'projects'));
    }
}
