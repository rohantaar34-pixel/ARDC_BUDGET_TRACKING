<?php

namespace App\Http\Controllers;

use App\Models\DocumentFolder;
use App\Models\Document;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DocumentFolderController extends Controller
{
    // ── LIST ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $folders   = DocumentFolder::with(['project', 'creator'])
            ->withCount('documents')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $projects  = Project::all();

        // Load all documents for the file-explorer drag-drop panel
        $documents = Document::with(['project', 'folder', 'uploader'])
            ->latest('date_added')
            ->get();

        return view('documents.folders.index', compact('folders', 'projects', 'documents'));
    }

    // ── STORE ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'color'       => 'nullable|string|max:20',
            'project_id'  => 'nullable|exists:projects,id',
        ]);

        // Generate unique slug
        $slug = Str::slug($data['name']);
        $base = $slug;
        $i    = 1;
        while (DocumentFolder::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        DocumentFolder::create([
            'name'        => $data['name'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'color'       => $data['color'] ?? '#0f9f8f',
            'project_id'  => $data['project_id'] ?? null,
            'created_by'  => Auth::id(),
            'sort_order'  => DocumentFolder::max('sort_order') + 1,
        ]);

        return redirect()->back()
            ->with('success', "Folder \"{$data['name']}\" created!");
    }

    // ── SHOW (folder contents) ────────────────────────────────────────────────

    public function show(Request $request, DocumentFolder $documentFolder)
    {
        $query = $documentFolder->documents()->with(['project', 'uploader']);

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sq) use ($q) {
                $sq->where('title', 'like', "%{$q}%")
                   ->orWhere('document_number', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $documents = $query->latest('date_added')->paginate(20);

        return view('documents.folders.show', compact('documentFolder', 'documents'));
    }

    // ── UPDATE ────────────────────────────────────────────────────────────────

    public function update(Request $request, DocumentFolder $documentFolder)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'color'       => 'nullable|string|max:20',
            'project_id'  => 'nullable|exists:projects,id',
        ]);

        $documentFolder->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'color'       => $data['color'] ?? $documentFolder->color,
            'project_id'  => $data['project_id'] ?? null,
        ]);

        return redirect()->back()
            ->with('success', "Folder \"{$documentFolder->name}\" updated!");
    }

    // ── DESTROY ───────────────────────────────────────────────────────────────

    public function destroy(Request $request, DocumentFolder $documentFolder)
    {
        $name = $documentFolder->name;
        $count = $documentFolder->documents()->count();

        if ($count > 0 && !$request->boolean('force')) {
            return back()->with('error',
                "Cannot delete \"{$name}\" — it contains {$count} document(s). " .
                "Move or delete them first, or use force-delete."
            );
        }

        // Nullify folder_id on all documents inside before deleting
        $documentFolder->documents()->update(['folder_id' => null]);
        $documentFolder->delete();

        return redirect()->back()
            ->with('success', "Folder \"{$name}\" deleted.");
    }

    // ── REORDER ───────────────────────────────────────────────────────────────

    public function reorder(Request $request)
    {
        $request->validate([
            'order'   => 'required|array',
            'order.*' => 'exists:document_folders,id',
        ]);

        foreach ($request->order as $position => $id) {
            DocumentFolder::where('id', $id)->update(['sort_order' => $position]);
        }

        return response()->json(['success' => true]);
    }

    // ── ASSIGN documents to folder ────────────────────────────────────────────

    public function assignDocuments(Request $request, DocumentFolder $documentFolder)
    {
        $request->validate([
            'document_ids'   => 'required|array',
            'document_ids.*' => 'exists:documents,id',
        ]);

        Document::whereIn('id', $request->document_ids)
            ->update(['folder_id' => $documentFolder->id]);

        return back()->with('success',
            count($request->document_ids) . ' document(s) moved to "' . $documentFolder->name . '".'
        );
    }
}
