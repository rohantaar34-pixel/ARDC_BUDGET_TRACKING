<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::orderBy('created_at', 'desc')->get();
        return view('settings.projects.index', compact('projects'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'budget'      => 'nullable|numeric|min:0',
                'status'      => 'nullable|in:not_started,in_progress,near_completion,completed',
            ]);

            $validated['status'] = $validated['status'] ?? 'not_started';
            $project = Project::create($validated);

            // Generate document number
            $prefix = 'DOC';
            $year = date('Y');
            $month = date('m');
            
            $lastDocument = Document::whereYear('date_added', $year)
                ->whereMonth('date_added', $month)
                ->orderBy('id', 'desc')
                ->first();
            
            if ($lastDocument) {
                $lastNumber = intval(substr($lastDocument->document_number, -4));
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }
            $documentNumber = "{$prefix}-{$year}{$month}-{$newNumber}";

            // Automatically create a Document Tracker entry
            Document::create([
                'document_number' => $documentNumber,
                'title' => $project->name . ' - Initialization',
                'description' => 'Automatically generated folder/entry for project: ' . $project->name,
                'document_type' => 'other',
                'category' => 'project_initiation',
                'project_id' => $project->id,
                'uploaded_by' => Auth::id(),
                'status' => 'active',
                'date_added' => now()
            ]);

            return redirect()->route('settings.projects.index')
                ->with('success', 'Project created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()
                ->with('error', 'Validation failed. Please check the form.');
        } catch (\Exception $e) {
            Log::error('Error in Settings\ProjectController@store: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Error creating project: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Project $project)
    {
        try {
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'budget'      => 'nullable|numeric|min:0',
                'status'      => 'nullable|in:not_started,in_progress,near_completion,completed',
            ]);

            $project->update($validated);

            return redirect()->route('settings.projects.index')
                ->with('success', 'Project updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()
                ->with('error', 'Validation failed. Please check the form.');
        } catch (\Exception $e) {
            Log::error('Error in Settings\ProjectController@update: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Error updating project: ' . $e->getMessage());
        }
    }

    public function destroy(Project $project)
    {
        try {
            $projectName = $project->name;
            
            // Delete associated documents in the document tracker
            $documents = Document::where('project_id', $project->id)->get();
            foreach ($documents as $document) {
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }
                if ($document->scanned_image_path && Storage::disk('public')->exists($document->scanned_image_path)) {
                    Storage::disk('public')->delete($document->scanned_image_path);
                }
                if ($document->thumbnail_path && Storage::disk('public')->exists($document->thumbnail_path)) {
                    Storage::disk('public')->delete($document->thumbnail_path);
                }
                $document->delete();
            }

            $project->delete();

            return redirect()->route('settings.projects.index')
                ->with('success', 'Project "' . $projectName . '" and its associated documents deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error in Settings\ProjectController@destroy: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error deleting project: ' . $e->getMessage());
        }
    }
}
