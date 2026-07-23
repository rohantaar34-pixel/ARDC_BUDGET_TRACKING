<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const MODULE_LEDGER = 'ledger';
    public const MODULE_DOCUMENTS = 'documents';
    public const MODULE_MONITORING_REVIEW = 'monitoring_review';
    public const MODULE_MONITORING_SUBMIT = 'monitoring_submit';
    public const MODULE_INVENTORY = 'inventory';
    public const MODULE_MATERIAL_REQUESTS = 'material_requests';
    public const MODULE_MATERIAL_APPROVALS = 'material_approvals';
    public const MODULE_SETTINGS_PROJECTS = 'settings_projects';
    public const MODULE_SETTINGS_USERS = 'settings_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'position',
        'role',
        'access_role_id',
        'module_permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'module_permissions' => 'array',
            'password' => 'hashed',
        ];
    }

    public static function moduleCatalog(): array
    {
        return [
            self::MODULE_LEDGER => [
                'label' => 'Ledger',
                'description' => 'Budget tracking, project balances, and financial exports.',
            ],
            self::MODULE_DOCUMENTS => [
                'label' => 'Document Tracker',
                'description' => 'Document uploads, records, and file downloads.',
            ],
            self::MODULE_MONITORING_REVIEW => [
                'label' => 'Project Monitoring',
                'description' => 'Review, approve, and reject project monitoring reports.',
            ],
            self::MODULE_MONITORING_SUBMIT => [
                'label' => 'Project Monitoring',
                'description' => 'Submit accomplishment reports and upload progress photos.',
            ],
            self::MODULE_INVENTORY => [
                'label' => 'Project Inventory',
                'description' => 'Inventory records, assignments, and stock exports.',
            ],
            self::MODULE_MATERIAL_REQUESTS => [
                'label' => 'Material Requests',
                'description' => 'Create employee material requests for assigned projects.',
            ],
            self::MODULE_MATERIAL_APPROVALS => [
                'label' => 'Material Approvals',
                'description' => 'Review requests, reserve budget, and issue materials.',
            ],
            self::MODULE_SETTINGS_PROJECTS => [
                'label' => 'Project Settings',
                'description' => 'Create, edit, and remove project master records.',
            ],
            self::MODULE_SETTINGS_USERS => [
                'label' => 'User Management',
                'description' => 'Create users, edit access, and manage user accounts.',
            ],
        ];
    }

    public static function modulesForRole(string $role): array
    {
        return match ($role) {
            'admin' => [
                self::MODULE_LEDGER,
                self::MODULE_DOCUMENTS,
                self::MODULE_MONITORING_REVIEW,
                self::MODULE_INVENTORY,
                self::MODULE_MATERIAL_APPROVALS,
                self::MODULE_SETTINGS_PROJECTS,
                self::MODULE_SETTINGS_USERS,
            ],
            'office_engineer' => [
                self::MODULE_DOCUMENTS,
                self::MODULE_MONITORING_REVIEW,
                self::MODULE_INVENTORY,
                self::MODULE_MATERIAL_APPROVALS,
            ],
            'employee' => [
                self::MODULE_MONITORING_SUBMIT,
                self::MODULE_MATERIAL_REQUESTS,
            ],
            default => [],
        };
    }

    public static function moduleOptionsByRole(): array
    {
        return collect(['admin', 'office_engineer', 'employee'])
            ->mapWithKeys(fn (string $role) => [$role => self::dashboardModuleOptions($role)])
            ->all();
    }

    /**
     * Return the dashboard module cards in the same order used on the home page.
     * Workflow cards resolve to review or submission access according to the role.
     */
    public static function dashboardModuleOptions(string $role): array
    {
        $workflowType = match ($role) {
            'admin', AccessRole::WORKFLOW_ADMINISTRATOR => AccessRole::WORKFLOW_ADMINISTRATOR,
            'office_engineer', AccessRole::WORKFLOW_REVIEWER => AccessRole::WORKFLOW_REVIEWER,
            default => AccessRole::WORKFLOW_FIELD_STAFF,
        };

        return self::dashboardModuleOptionsForWorkflow($workflowType);
    }

    public static function dashboardModuleOptionsForWorkflow(string $workflowType): array
    {
        $isFieldStaff = $workflowType === AccessRole::WORKFLOW_FIELD_STAFF;
        $monitoringModule = $isFieldStaff
            ? self::MODULE_MONITORING_SUBMIT
            : self::MODULE_MONITORING_REVIEW;
        $materialsModule = $isFieldStaff
            ? self::MODULE_MATERIAL_REQUESTS
            : self::MODULE_MATERIAL_APPROVALS;

        $options = [
            [
                'key' => self::MODULE_LEDGER,
                'label' => 'Ledger',
                'description' => 'Manage project finances, budgets, expenses, and financial reports.',
                'tone' => 'ledger',
            ],
            [
                'key' => self::MODULE_DOCUMENTS,
                'label' => 'Document Tracker',
                'description' => 'Organize documents, manage files, and maintain document records.',
                'tone' => 'documents',
            ],
            [
                'key' => $monitoringModule,
                'label' => 'Project Monitoring',
                'description' => $isFieldStaff
                    ? 'Submit accomplishments, upload photos, and track approval status.'
                    : 'Review reports, approve progress, and monitor completion metrics.',
                'tone' => 'monitoring',
            ],
            [
                'key' => self::MODULE_INVENTORY,
                'label' => 'Project Inventory',
                'description' => 'Manage materials, equipment, supplies, assignments, and reports.',
                'tone' => 'inventory',
            ],
            [
                'key' => $materialsModule,
                'label' => $isFieldStaff ? 'Material Requests' : 'Material Approvals',
                'description' => $isFieldStaff
                    ? 'Request materials for assigned projects and track request status.'
                    : 'Review requests, reserve budget, issue stock, and handle procurement.',
                'tone' => 'materials',
            ],
            [
                'key' => self::MODULE_SETTINGS_PROJECTS,
                'label' => 'Project Settings',
                'description' => 'Create, update, and maintain project master records.',
                'tone' => 'settings',
            ],
            [
                'key' => self::MODULE_SETTINGS_USERS,
                'label' => 'User Management',
                'description' => 'Create accounts, assign module access, and manage users.',
                'tone' => 'users',
            ],
        ];

        return array_map(function (array $option): array {
            $option['available'] = true;

            return $option;
        }, $options);
    }

    public static function moduleKeysForWorkflow(string $workflowType): array
    {
        return array_column(self::dashboardModuleOptionsForWorkflow($workflowType), 'key');
    }

    public function workflowType(): string
    {
        if ($this->accessRole) {
            return $this->accessRole->workflow_type;
        }

        return match ($this->role) {
            'admin' => AccessRole::WORKFLOW_ADMINISTRATOR,
            'office_engineer' => AccessRole::WORKFLOW_REVIEWER,
            default => AccessRole::WORKFLOW_FIELD_STAFF,
        };
    }

    public function allowedModules(): array
    {
        return self::moduleKeysForWorkflow($this->workflowType());
    }

    public function resolvedModulePermissions(): array
    {
        $allowedModules = $this->allowedModules();

        if ($this->module_permissions === null) {
            $roleDefaults = $this->accessRole?->module_permissions;

            if (is_array($roleDefaults)) {
                return array_values(array_intersect($allowedModules, array_map('strval', $roleDefaults)));
            }

            return array_values(array_intersect($allowedModules, self::modulesForRole($this->role)));
        }

        if (!is_array($this->module_permissions)) {
            return [];
        }

        return array_values(array_intersect($allowedModules, array_map('strval', $this->module_permissions)));
    }

    public function hasModuleAccess(string $module): bool
    {
        return in_array($module, $this->resolvedModulePermissions(), true);
    }

    public function hasAnyModuleAccess(array $modules): bool
    {
        foreach ($modules as $module) {
            if ($this->hasModuleAccess($module)) {
                return true;
            }
        }

        return false;
    }

    public function canManageUsers(): bool
    {
        return $this->hasModuleAccess(self::MODULE_SETTINGS_USERS);
    }

    public function canManageProjectsSettings(): bool
    {
        return $this->hasModuleAccess(self::MODULE_SETTINGS_PROJECTS);
    }

    public function displayPosition(): string
    {
        if (!empty($this->position)) {
            return $this->position;
        }

        return (string) str($this->role)->replace('_', ' ')->title();
    }

    public function displayRole(): string
    {
        return $this->accessRole?->name
            ?? (string) str($this->role)->replace('_', ' ')->title();
    }

    public function landingRouteName(): string
    {
        $modules = $this->resolvedModulePermissions();

        if (count($modules) !== 1) {
            return 'dashboard';
        }

        $routeMap = [
            self::MODULE_LEDGER => 'projects.index',
            self::MODULE_DOCUMENTS => 'documents.index',
            self::MODULE_MONITORING_REVIEW => 'monitoring.index',
            self::MODULE_MONITORING_SUBMIT => 'monitoring.submit',
            self::MODULE_INVENTORY => 'inventory.index',
            self::MODULE_MATERIAL_REQUESTS => 'material-requests.create',
            self::MODULE_MATERIAL_APPROVALS => 'material-requests.index',
            self::MODULE_SETTINGS_PROJECTS => 'settings.projects.index',
            self::MODULE_SETTINGS_USERS => 'settings.users.index',
        ];

        return $routeMap[$modules[0]] ?? 'dashboard';
    }

    public function moduleSummary(): string
    {
        $catalog = self::moduleCatalog();
        $labels = collect($this->resolvedModulePermissions())
            ->map(fn (string $module) => $catalog[$module]['label'] ?? $module)
            ->values();

        return $labels->isNotEmpty() ? $labels->join(', ') : 'No modules assigned';
    }

    public static function usersWithUserManagementAccess(Collection $users): Collection
    {
        return $users->filter(fn (self $user) => $user->canManageUsers())->values();
    }

    public function isAdmin(): bool
    {
        return $this->workflowType() === AccessRole::WORKFLOW_ADMINISTRATOR;
    }

    public function isEmployee(): bool
    {
        return $this->workflowType() === AccessRole::WORKFLOW_FIELD_STAFF;
    }

    public function isOfficeEngineer(): bool
    {
        return $this->workflowType() === AccessRole::WORKFLOW_REVIEWER;
    }

    public function canManageOperations(): bool
    {
        return $this->hasAnyModuleAccess([
            self::MODULE_DOCUMENTS,
            self::MODULE_MONITORING_REVIEW,
            self::MODULE_INVENTORY,
            self::MODULE_MATERIAL_APPROVALS,
        ]);
    }

    public function assignedProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withTimestamps();
    }

    public function accessRole(): BelongsTo
    {
        return $this->belongsTo(AccessRole::class);
    }

    public function monitoringReports(): HasMany
    {
        return $this->hasMany(ProjectMonitoringReport::class);
    }
}
