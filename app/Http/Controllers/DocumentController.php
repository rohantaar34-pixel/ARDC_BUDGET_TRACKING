<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\Project;
use App\Services\DocumentExpiryNotifier;
use App\Services\NotificationBroadcaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with(['project', 'uploader']);
        
        if ($request->has('type') && $request->type != '') {
            $query->where('document_type', $request->type);
        }
        if ($request->has('category') && $request->category != '') {
            $query->where('category', $request->category);
        }
        if ($request->has('project_id') && $request->project_id != '') {
            $query->where('project_id', $request->project_id);
        }
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('document_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $documents = $query->latest('date_added')->paginate(15);
        $projects  = Project::all();
        
        $stats = [
            'total'    => Document::count(),
            'active'   => Document::where('status', 'active')->count(),
            'archived' => Document::where('status', 'archived')->count(),
            'recent'   => Document::where('date_added', '>=', now()->subDays(30))->count(),
        ];
        
        return view('documents.index', compact('documents', 'projects', 'stats'));
    }
    
    public function create()
    {
        $projects       = Project::all();
        $folders        = DocumentFolder::orderBy('name')->get();
        $documentNumber = $this->generateDocumentNumber();
        
        return view('documents.create', compact('projects', 'folders', 'documentNumber'));
    }

    public function projectFiles(Request $request, Project $project)
    {
        $folders = DocumentFolder::where('project_id', $project->id)
            ->orWhereNull('project_id')
            ->withCount(['documents' => function($q) use ($project) {
                $q->where('project_id', $project->id);
            }])
            ->orderBy('name')
            ->get();

        $query = $project->documents()->with(['uploader', 'folder'])->latest('date_added');

        if ($request->has('folder_id') && $request->folder_id !== '') {
            if ($request->folder_id === 'none') {
                $query->whereNull('folder_id');
            } else {
                $query->where('folder_id', $request->folder_id);
            }
        }

        $documents = $query->get();

        return view('documents.project', compact('project', 'documents', 'folders'));
    }
    
    public function store(
        Request $request,
        NotificationBroadcaster $notifications,
        DocumentExpiryNotifier $expiryNotifier,
    ) {
        $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('documents')->where(function ($query) use ($request) {
                    return $query->where('project_id', $request->project_id)
                                 ->where('folder_id', $request->folder_id ?: null);
                }),
            ],
            'description'   => 'nullable|string',
            'document_type' => 'required|string',
            'category'      => 'nullable|string',
            'document_date' => 'nullable|date',
            'expiry_date'   => 'nullable|date|after:document_date',
            'project_id'    => 'nullable|exists:projects,id',
            'folder_id'     => 'nullable|exists:document_folders,id',
            'document_file' => 'nullable|file|max:10240',
            'scanned_image' => 'nullable|image|max:5120',
        ], [
            'title.unique' => 'A document with this title already exists in the selected project folder.',
        ]);
        
        $document = new Document();
        $document->document_number = $request->document_number ?? $this->generateDocumentNumber();
        $document->title           = $request->title;
        $document->description     = $request->description;
        $document->document_type   = $request->document_type;
        $document->category        = $request->category;
        $document->document_date   = $request->document_date;
        $document->expiry_date     = $request->expiry_date;
        $document->project_id      = $request->project_id;
        $document->folder_id       = $request->folder_id ?: null;
        $document->uploaded_by     = Auth::id();
        $document->status          = 'active';
        $document->date_added      = now();
        
        if ($request->hasFile('document_file')) {
            $file     = $request->file('document_file');
            $filename = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
            $path     = $file->storeAs('documents/files', $filename, 'public');
            $document->file_path         = $path;
            $document->original_filename = $file->getClientOriginalName();
            $document->file_size         = $file->getSize();
            $document->file_extension    = $file->getClientOriginalExtension();
            $document->mime_type         = $file->getMimeType();
        }
        
        if ($request->hasFile('scanned_image')) {
            $image     = $request->file('scanned_image');
            $imageName = time() . '_scan_' . Str::slug($request->title) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('documents/scans', $imageName, 'public');
            $document->scanned_image_path = $imagePath;
        }
        
        $document->save();

        $notifications->documentAdded($document, Auth::user());
        $expiryNotifier->sendForDocument($document);

        if ($document->project_id) {
            $redirectUrl = route('documents.project', $document->project_id);
            $redirectUrl .= '?folder_id=' . ($document->folder_id ?? 'none');
            return redirect($redirectUrl)
                ->with('success', 'Document added successfully!');
        }

        return redirect()->route('documents.index')
            ->with('success', 'Document added successfully!');
    }
    
    public function show(Document $document)
    {
        $document->incrementViewCount();
        $document->load(['project', 'uploader', 'folder']);
        
        return view('documents.show', compact('document'));
    }
    
    public function edit(Document $document)
    {
        $projects = Project::all();
        $folders  = DocumentFolder::with('project')->orderBy('name')->get();
        return view('documents.edit', compact('document', 'projects', 'folders'));
    }
    
    public function update(
        Request $request,
        Document $document,
        DocumentExpiryNotifier $expiryNotifier,
    ) {
        $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('documents')->ignore($document->id)->where(function ($query) use ($request) {
                    return $query->where('project_id', $request->project_id)
                                 ->where('folder_id', $request->folder_id ?: null);
                }),
            ],
            'description'   => 'nullable|string',
            'document_type' => 'required|string',
            'category'      => 'nullable|string',
            'document_date' => 'nullable|date',
            'expiry_date'   => 'nullable|date|after:document_date',
            'project_id'    => 'nullable|exists:projects,id',
            'folder_id'     => 'nullable|exists:document_folders,id',
            'status'        => 'required|in:active,archived,expired',
            'document_file' => 'nullable|file|max:10240',
            'scanned_image' => 'nullable|image|max:5120',
        ], [
            'title.unique' => 'A document with this title already exists in the selected project folder.',
        ]);
        
        $previousExpiryDate    = $document->expiry_date?->toDateString();
        $document->title       = $request->title;
        $document->description = $request->description;
        $document->document_type = $request->document_type;
        $document->category    = $request->category;
        $document->document_date = $request->document_date;
        $document->expiry_date = $request->expiry_date;
        $document->project_id  = $request->project_id;
        $document->folder_id   = $request->folder_id ?: null;
        $document->status      = $request->status;
        
        if ($request->hasFile('document_file')) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            $file     = $request->file('document_file');
            $filename = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
            $path     = $file->storeAs('documents/files', $filename, 'public');
            $document->file_path         = $path;
            $document->original_filename = $file->getClientOriginalName();
            $document->file_size         = $file->getSize();
            $document->file_extension    = $file->getClientOriginalExtension();
            $document->mime_type         = $file->getMimeType();
        }
        
        if ($request->hasFile('scanned_image')) {
            if ($document->scanned_image_path && Storage::disk('public')->exists($document->scanned_image_path)) {
                Storage::disk('public')->delete($document->scanned_image_path);
            }
            $image     = $request->file('scanned_image');
            $imageName = time() . '_scan_' . Str::slug($request->title) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('documents/scans', $imageName, 'public');
            $document->scanned_image_path = $imagePath;
        }
        
        $document->save();

        if ($previousExpiryDate !== $document->expiry_date?->toDateString()) {
            $expiryNotifier->sendForDocument($document);
        }
        
        if ($document->project_id) {
            $redirectUrl = route('documents.project', $document->project_id);
            $redirectUrl .= '?folder_id=' . ($document->folder_id ?? 'none');
            return redirect($redirectUrl)
                ->with('success', 'Document updated successfully!');
        }

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document updated successfully!');
    }
    
    public function destroy(Document $document)
    {
        $projectId = $document->project_id;
        $folderId  = $document->folder_id;

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
        
        if (request()->header('referer') && str_contains(request()->header('referer'), '/documents/project/')) {
            return redirect()->back()->with('success', 'Document deleted successfully!');
        }

        if ($projectId) {
            $url = route('documents.project', $projectId) . '?folder_id=' . ($folderId ?? 'none');
            return redirect($url)->with('success', 'Document deleted successfully!');
        }
        
        return redirect()->route('documents.index')
            ->with('success', 'Document deleted successfully!');
    }
    
    public function download(Document $document)
    {
        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }
        $document->incrementDownloadCount();
        return Storage::disk('public')->download($document->file_path, $document->original_filename);
    }

    public function viewScan(Document $document)
    {
        if (!$document->scanned_image_path || !Storage::disk('public')->exists($document->scanned_image_path)) {
            abort(404, 'Scanned image not found.');
        }
        return Storage::disk('public')->response(
            $document->scanned_image_path,
            basename($document->scanned_image_path),
            ['Content-Disposition' => 'inline'],
        );
    }
    
    public function search(Request $request)
    {
        $search    = $request->get('q');
        $documents = Document::where('title', 'like', "%{$search}%")
            ->orWhere('document_number', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%")
            ->limit(10)->get();
        
        if ($request->ajax()) {
            return response()->json($documents);
        }
        return redirect()->route('documents.index', ['search' => $search]);
    }
    
    public function byCategory($category)
    {
        $documents = Document::where('category', $category)
            ->with(['project', 'uploader'])
            ->latest('date_added')->paginate(15);
        $projects       = Project::all();
        $stats          = [
            'total'    => Document::count(),
            'active'   => Document::where('status', 'active')->count(),
            'archived' => Document::where('status', 'archived')->count(),
            'recent'   => Document::where('date_added', '>=', now()->subDays(30))->count(),
        ];
        $currentCategory = $category;
        return view('documents.index', compact('documents', 'projects', 'stats', 'currentCategory'));
    }

    public function bulkMoveToCategory(Request $request)
    {
        $request->validate([
            'document_ids'   => 'required|array',
            'document_ids.*' => 'exists:documents,id',
            'category'       => 'required|string|in:financial,legal,technical,administrative,war,dar,other',
        ]);

        Document::whereIn('id', $request->document_ids)
            ->update(['category' => $request->category]);

        return redirect()->route('documents.index')
            ->with('success', count($request->document_ids) . ' document(s) moved to ' . strtoupper($request->category) . ' folder.');
    }
    
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'document_ids'   => 'required|array',
            'document_ids.*' => 'exists:documents,id',
        ]);
        
        $documents = Document::whereIn('id', $request->document_ids)->get();
        foreach ($documents as $document) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            if ($document->scanned_image_path && Storage::disk('public')->exists($document->scanned_image_path)) {
                Storage::disk('public')->delete($document->scanned_image_path);
            }
            $document->delete();
        }
        
        return redirect()->route('documents.index')
            ->with('success', count($documents) . ' documents deleted successfully!');
    }
    
    public function bulkMove(Request $request)
    {
        $request->validate([
            'document_ids'   => 'required|array',
            'document_ids.*' => 'exists:documents,id',
            'project_id'     => 'required|exists:projects,id',
        ]);
        Document::whereIn('id', $request->document_ids)
            ->update(['project_id' => $request->project_id]);
        return redirect()->route('documents.index')
            ->with('success', 'Documents moved successfully!');
    }
    
    public function statistics()
    {
        $stats = [
            'total'       => Document::count(),
            'by_type'     => Document::selectRaw('document_type, count(*) as count')->groupBy('document_type')->get(),
            'by_category' => Document::selectRaw('category, count(*) as count')->whereNotNull('category')->groupBy('category')->get(),
            'by_status'   => Document::selectRaw('status, count(*) as count')->groupBy('status')->get(),
            'by_month'    => Document::selectRaw('DATE_FORMAT(date_added, "%Y-%m") as month, count(*) as count')
                ->groupBy('month')->orderBy('month', 'desc')->limit(6)->get(),
        ];
        return view('documents.statistics', compact('stats'));
    }
    
    public function exportAll()
    {
        $documents = Document::with(['project', 'uploader'])->get();
        $filename  = 'documents_export_' . date('Y-m-d_His') . '.csv';
        $handle    = fopen('php://temp', 'w+');
        fputcsv($handle, [
            'Document Number', 'Title', 'Description', 'Type', 'Category',
            'Status', 'Project', 'Folder', 'Uploaded By', 'Document Date',
            'Expiry Date', 'Date Added', 'View Count', 'Download Count',
        ]);
        foreach ($documents as $doc) {
            fputcsv($handle, [
                $doc->document_number,
                $doc->title,
                $doc->description,
                $doc->document_type,
                $doc->category,
                $doc->status,
                $doc->project->name ?? 'N/A',
                $doc->folder->name  ?? 'N/A',
                $doc->uploader->name ?? 'N/A',
                $doc->document_date,
                $doc->expiry_date,
                $doc->date_added,
                $doc->view_count,
                $doc->download_count,
            ]);
        }
        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);
        return response($csvContent)->withHeaders([
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    public function recent()
    {
        $documents = Document::with(['project', 'uploader'])->latest('date_added')->limit(10)->get();
        return response()->json($documents);
    }
    
    public function versionHistory(Document $document)
    {
        return view('documents.versions', compact('document'));
    }
    
    public function restore($id)
    {
        $document = Document::withTrashed()->findOrFail($id);
        $document->restore();
        return redirect()->route('documents.index')->with('success', 'Document restored successfully!');
    }
    
    // API Methods
    public function apiSearch(Request $request)
    {
        $search    = $request->get('q');
        $documents = Document::where('title', 'like', "%{$search}%")
            ->orWhere('document_number', 'like', "%{$search}%")
            ->limit(10)->get(['id', 'document_number', 'title', 'document_type']);
        return response()->json($documents);
    }
    
    public function apiUpload(Request $request)
    {
        $request->validate([
            'file'        => 'required|file|max:10240',
            'document_id' => 'nullable|exists:documents,id',
        ]);
        $file     = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path     = $file->storeAs('documents/uploads', $filename, 'public');
        if ($request->has('document_id')) {
            $document           = Document::find($request->document_id);
            $document->file_path = $path;
            $document->save();
        }
        return response()->json([
            'success' => true,
            'path'    => $path,
            'url'     => Storage::disk('public')->url($path),
        ]);
    }
    
    public function apiStats()
    {
        $stats = [
            'total'    => Document::count(),
            'active'   => Document::where('status', 'active')->count(),
            'archived' => Document::where('status', 'archived')->count(),
            'expired'  => Document::where('status', 'expired')->count(),
            'recent'   => Document::where('date_added', '>=', now()->subDays(7))->count(),
            'by_type'  => Document::selectRaw('document_type, count(*) as count')->groupBy('document_type')->get(),
        ];
        return response()->json($stats);
    }
    
    public function bulkStatusUpdate(Request $request)
    {
        $request->validate([
            'document_ids'   => 'required|array',
            'document_ids.*' => 'exists:documents,id',
            'status'         => 'required|in:active,archived,expired',
        ]);
        Document::whereIn('id', $request->document_ids)->update(['status' => $request->status]);
        return response()->json([
            'success' => true,
            'message' => count($request->document_ids) . ' documents updated successfully!',
        ]);
    }

    /**
     * AJAX: move a single document to a folder (or unassign with folder_id=null).
     */
    public function setFolder(Request $request, Document $document)
    {
        $request->validate([
            'folder_id' => 'nullable|exists:document_folders,id',
        ]);

        $document->update(['folder_id' => $request->folder_id ?: null]);

        return response()->json([
            'success'   => true,
            'folder_id' => $document->folder_id,
        ]);
    }
    
    private function generateDocumentNumber()
    {
        $prefix = 'DOC';
        $year   = date('Y');
        $month  = date('m');
        
        $lastDocument = Document::whereYear('date_added', $year)
            ->whereMonth('date_added', $month)
            ->orderBy('id', 'desc')->first();
        
        if ($lastDocument) {
            $lastNumber = intval(substr($lastDocument->document_number, -4));
            $newNumber  = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return "{$prefix}-{$year}{$month}-{$newNumber}";
    }
}
