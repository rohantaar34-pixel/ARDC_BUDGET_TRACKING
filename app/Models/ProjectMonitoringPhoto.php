<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMonitoringPhoto extends Model
{
    protected $fillable = [
        'project_monitoring_report_id',
        'path',
        'original_name',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(ProjectMonitoringReport::class, 'project_monitoring_report_id');
    }
}
