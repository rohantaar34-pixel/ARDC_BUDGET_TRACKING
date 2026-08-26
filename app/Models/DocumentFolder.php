<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocumentFolder extends Model
{
    protected $table = 'document_folders';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'project_id',
        'created_by',
        'sort_order',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function documents()
    {
        return $this->hasMany(Document::class, 'folder_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Total number of documents in this folder.
     */
    public function getDocumentCountAttribute(): int
    {
        return $this->documents()->count();
    }

    // ── Boot ──────────────────────────────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from name on create
        static::creating(function (DocumentFolder $folder) {
            if (empty($folder->slug)) {
                $folder->slug = Str::slug($folder->name);
            }
        });

        // Regenerate slug if name changed and slug wasn't set manually
        static::updating(function (DocumentFolder $folder) {
            if ($folder->isDirty('name') && !$folder->isDirty('slug')) {
                $folder->slug = Str::slug($folder->name);
            }
        });
    }
}
