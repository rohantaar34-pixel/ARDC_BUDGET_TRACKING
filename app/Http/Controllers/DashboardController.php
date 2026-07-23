<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isEmployee() && $user->landingRouteName() !== 'dashboard') {
            return redirect()->route($user->landingRouteName());
        }

        $projectQuery = Project::query();
        $canSeeAllProjects = $user->hasAnyModuleAccess([
            User::MODULE_LEDGER,
            User::MODULE_DOCUMENTS,
            User::MODULE_MONITORING_REVIEW,
            User::MODULE_INVENTORY,
            User::MODULE_MATERIAL_APPROVALS,
            User::MODULE_SETTINGS_PROJECTS,
            User::MODULE_SETTINGS_USERS,
        ]);

        if (!$canSeeAllProjects) {
            $projectQuery->whereIn('id', $user->assignedProjects()->pluck('projects.id'));
        }

        $stats = [
            'total_projects' => (clone $projectQuery)->count(),
            'total_documents' => $user->hasModuleAccess(User::MODULE_DOCUMENTS) ? Document::count() : 0,
            'total_budget' => $user->hasModuleAccess(User::MODULE_LEDGER) ? (clone $projectQuery)->sum('budget') : 0,
        ];

        $projects = $user->hasModuleAccess(User::MODULE_LEDGER)
            ? $projectQuery->orderBy('created_at', 'desc')->get()
            : collect();

        return view('dashboard', compact('stats', 'projects'));
    }
}
