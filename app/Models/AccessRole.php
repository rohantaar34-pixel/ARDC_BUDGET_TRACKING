<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccessRole extends Model
{
    use HasFactory;

    public const WORKFLOW_ADMINISTRATOR = 'administrator';
    public const WORKFLOW_REVIEWER = 'reviewer';
    public const WORKFLOW_FIELD_STAFF = 'field_staff';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'workflow_type',
        'module_permissions',
        'is_protected',
    ];

    protected function casts(): array
    {
        return [
            'module_permissions' => 'array',
            'is_protected' => 'boolean',
        ];
    }

    public static function workflowOptions(): array
    {
        return [
            self::WORKFLOW_ADMINISTRATOR => [
                'label' => 'Administrator',
                'description' => 'Management workflow with monitoring review and material approval tools.',
            ],
            self::WORKFLOW_REVIEWER => [
                'label' => 'Reviewer / Office Team',
                'description' => 'Office workflow for reviewing monitoring reports and material requests.',
            ],
            self::WORKFLOW_FIELD_STAFF => [
                'label' => 'Field Staff',
                'description' => 'Field workflow for submitting monitoring reports and requesting materials.',
            ],
        ];
    }

    public function systemRole(): string
    {
        return match ($this->workflow_type) {
            self::WORKFLOW_ADMINISTRATOR => 'admin',
            self::WORKFLOW_REVIEWER => 'office_engineer',
            default => 'employee',
        };
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
