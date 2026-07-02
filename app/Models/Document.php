<?php

// app/Models/Document.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// Remove this line: use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    // Remove this line: use SoftDeletes;
    
    protected $table = 'documents';
    
    protected $fillable = [
        'document_number',
        'title',
        'description',
        'document_type',
        'category',
        'file_path',
        'scanned_image_path',
        'thumbnail_path',
        'original_filename',
        'file_size',
        'file_extension',
        'mime_type',
        'document_date',
        'expiry_date',
        'status',
        'version',
        'project_id',
        'uploaded_by',
        'view_count',
        'download_count',
        'date_added'
    ];
    
    protected $casts = [
        'document_date' => 'date',
        'expiry_date' => 'date',
        'date_added' => 'datetime',
        'view_count' => 'integer',
        'download_count' => 'integer',
        'version' => 'integer'
    ];
    
    // Remove this line: protected $dates = ['date_added', 'created_at', 'updated_at', 'deleted_at'];
    protected $dates = ['date_added', 'created_at', 'updated_at'];
    
    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
    
    // Accessors
    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) return 'N/A';
        
        $bytes = (int)$this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    public function getFileUrlAttribute()
    {
        if ($this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return null;
    }
    
    public function getScannedImageUrlAttribute()
    {
        if ($this->scanned_image_path) {
            return asset('storage/' . $this->scanned_image_path);
        }
        return null;
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    public function scopeByType($query, $type)
    {
        return $query->where('document_type', $type);
    }
    
    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }
    
    // Helper Methods
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }
    
    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }
}