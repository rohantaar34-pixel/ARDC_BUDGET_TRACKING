<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMonitoringReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class MonitoringController extends Controller
{
    public function index()
    {
        $projects = Project::with(['monitoringReports.user', 'monitoringReports.photos'])
            ->withCount([
                'monitoringReports',
                'monitoringReports as pending_reports_count' => fn ($query) => $query->where('status', ProjectMonitoringReport::STATUS_PENDING),
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $reports = ProjectMonitoringReport::with(['project', 'user', 'photos', 'reviewer'])
            ->latest()
            ->get();

        $stats = [
            'projects' => $projects->count(),
            'pending' => $reports->where('status', ProjectMonitoringReport::STATUS_PENDING)->count(),
            'approved' => $reports->where('status', ProjectMonitoringReport::STATUS_APPROVED)->count(),
            'average_completion' => round($projects->avg('completion_percentage') ?? 0),
        ];

        return view('monitoring.index', compact('projects', 'reports', 'stats'));
    }

    public function submit()
    {
        $user = Auth::user();

        $projects = $user->assignedProjects()
            ->withCount([
                'monitoringReports as pending_reports_count' => fn ($query) => $query->where('status', ProjectMonitoringReport::STATUS_PENDING),
            ])
            ->withSum([
                'monitoringReports as pending_completion_total' => fn ($query) => $query->where('status', ProjectMonitoringReport::STATUS_PENDING),
            ], 'estimated_completion_percentage')
            ->orderBy('name')
            ->get();

        $reports = ProjectMonitoringReport::with(['project', 'photos', 'reviewer'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('monitoring.submit', compact('projects', 'reports'));
    }

    public function adminPulse()
    {
        $latestReportUpdate = ProjectMonitoringReport::max('updated_at');
        $latestProjectUpdate = Project::max('updated_at');

        $payload = [
            'projects' => Project::count(),
            'reports' => ProjectMonitoringReport::count(),
            'pending' => ProjectMonitoringReport::where('status', ProjectMonitoringReport::STATUS_PENDING)->count(),
            'approved' => ProjectMonitoringReport::where('status', ProjectMonitoringReport::STATUS_APPROVED)->count(),
            'rejected' => ProjectMonitoringReport::where('status', ProjectMonitoringReport::STATUS_REJECTED)->count(),
            'latest_report_update' => (string) $latestReportUpdate,
            'latest_project_update' => (string) $latestProjectUpdate,
            'project_completion_sum' => Project::sum('completion_percentage'),
        ];

        return response()->json([
            'signature' => sha1(json_encode($payload)),
            'checked_at' => now()->toIso8601String(),
            'pending' => $payload['pending'],
        ]);
    }

    public function employeePulse()
    {
        $user = Auth::user();
        $assignedProjectIds = $user->assignedProjects()->pluck('projects.id');
        $reports = ProjectMonitoringReport::where('user_id', $user->id);

        $payload = [
            'assigned_projects' => $assignedProjectIds->sort()->values()->all(),
            'reports' => (clone $reports)->count(),
            'pending' => (clone $reports)->where('status', ProjectMonitoringReport::STATUS_PENDING)->count(),
            'approved' => (clone $reports)->where('status', ProjectMonitoringReport::STATUS_APPROVED)->count(),
            'rejected' => (clone $reports)->where('status', ProjectMonitoringReport::STATUS_REJECTED)->count(),
            'latest_report_update' => (string) (clone $reports)->max('updated_at'),
            'latest_project_update' => (string) Project::whereIn('id', $assignedProjectIds)->max('updated_at'),
        ];

        return response()->json([
            'signature' => sha1(json_encode($payload)),
            'checked_at' => now()->toIso8601String(),
            'pending' => $payload['pending'],
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $assignedProjectIds = $user->assignedProjects()->pluck('projects.id')->all();

        $validated = $request->validate([
            'project_id' => ['required', Rule::in($assignedProjectIds)],
            'accomplishment_details' => ['required', 'string', 'max:5000'],
            'estimated_completion_percentage' => ['required', 'integer', 'min:1', 'max:100'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'max:10240'],
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $reservedCompletion = $this->reservedCompletionForProject($project);
        $remainingCompletion = max(100 - $reservedCompletion, 0);

        if ($validated['estimated_completion_percentage'] > $remainingCompletion) {
            throw ValidationException::withMessages([
                'estimated_completion_percentage' => "This project only has {$remainingCompletion}% remaining after approved and pending reports.",
            ]);
        }

        $report = ProjectMonitoringReport::create([
            'project_id' => $validated['project_id'],
            'user_id' => $user->id,
            'accomplishment_details' => $validated['accomplishment_details'],
            'estimated_completion_percentage' => $validated['estimated_completion_percentage'],
            'status' => ProjectMonitoringReport::STATUS_PENDING,
        ]);

        foreach ($request->file('photos', []) as $photo) {
            $report->photos()->create([
                'path' => $photo->store('monitoring_photos', 'public'),
                'original_name' => $photo->getClientOriginalName(),
            ]);
        }

        return redirect()
            ->route('monitoring.submit')
            ->with('success', 'Progress report submitted for admin approval.');
    }

    public function approve(ProjectMonitoringReport $report)
    {
        $approvedTotal = $report->project->monitoringReports()
            ->where('status', ProjectMonitoringReport::STATUS_APPROVED)
            ->whereKeyNot($report->id)
            ->sum('estimated_completion_percentage');

        $remainingCompletion = max(100 - $approvedTotal, 0);

        if ($report->estimated_completion_percentage > $remainingCompletion) {
            return redirect()
                ->route('monitoring.index')
                ->with('error', "This report cannot be approved because only {$remainingCompletion}% completion remains for the project.");
        }

        $report->update([
            'status' => ProjectMonitoringReport::STATUS_APPROVED,
            'admin_remarks' => request('admin_remarks'),
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $report->project->recalculateCompletion();

        return redirect()
            ->route('monitoring.index')
            ->with('success', 'Report approved and project progress updated.');
    }

    public function reject(Request $request, ProjectMonitoringReport $report)
    {
        $validated = $request->validate([
            'admin_remarks' => ['required', 'string', 'max:2000'],
        ]);

        $report->update([
            'status' => ProjectMonitoringReport::STATUS_REJECTED,
            'admin_remarks' => $validated['admin_remarks'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $report->project->recalculateCompletion();

        return redirect()
            ->route('monitoring.index')
            ->with('success', 'Report rejected with remarks.');
    }

    public function photo(ProjectMonitoringReport $report, int $photo)
    {
        $photo = $report->photos()->whereKey($photo)->firstOrFail();
        $user = Auth::user();

        if ($user->isEmployee() && $report->user_id !== $user->id) {
            abort(403);
        }

        if (!Storage::disk('public')->exists($photo->path)) {
            abort(404);
        }

        return response(Storage::disk('public')->get($photo->path), 200)
            ->header('Content-Type', Storage::disk('public')->mimeType($photo->path));
    }

    private function reservedCompletionForProject(Project $project): int
    {
        return (int) $project->monitoringReports()
            ->whereIn('status', [
                ProjectMonitoringReport::STATUS_APPROVED,
                ProjectMonitoringReport::STATUS_PENDING,
            ])
            ->sum('estimated_completion_percentage');
    }
}
