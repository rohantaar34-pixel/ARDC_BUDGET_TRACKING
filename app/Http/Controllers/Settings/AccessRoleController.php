<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AccessRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccessRoleController extends Controller
{
    public function index()
    {
        $roles = AccessRole::withCount('users')->orderByDesc('is_protected')->orderBy('name')->get();
        $workflowOptions = AccessRole::workflowOptions();
        $moduleOptionsByWorkflow = collect(array_keys($workflowOptions))
            ->mapWithKeys(fn (string $workflow) => [
                $workflow => User::dashboardModuleOptionsForWorkflow($workflow),
            ])
            ->all();

        return view('settings.roles.index', compact('roles', 'workflowOptions', 'moduleOptionsByWorkflow'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRole($request);

        AccessRole::create([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['name']),
            'is_protected' => false,
        ]);

        return redirect()
            ->route('settings.roles.index')
            ->with('success', 'Role "' . $validated['name'] . '" created successfully.');
    }

    public function update(Request $request, AccessRole $role)
    {
        $validated = $this->validateRole($request, $role);

        if ($role->is_protected
            && $validated['workflow_type'] !== AccessRole::WORKFLOW_ADMINISTRATOR) {
            return back()
                ->withInput()
                ->withErrors(['workflow_type' => 'The protected Super Admin role must remain an Administrator workflow.']);
        }

        if ($role->is_protected
            && !in_array(User::MODULE_SETTINGS_USERS, $validated['module_permissions'], true)) {
            return back()
                ->withInput()
                ->withErrors(['module_permissions' => 'The protected Super Admin role must keep User Management access.']);
        }

        $oldWorkflow = $role->workflow_type;

        DB::transaction(function () use ($role, $validated, $oldWorkflow) {
            $role->update([
                ...$validated,
                'slug' => $role->slug ?: $this->uniqueSlug($validated['name'], $role->id),
            ]);

            if ($oldWorkflow !== $validated['workflow_type']) {
                $role->users()->get()->each(function (User $user) use ($oldWorkflow, $validated, $role) {
                    $updates = ['role' => $role->systemRole()];

                    if (is_array($user->module_permissions)) {
                        $updates['module_permissions'] = $this->remapWorkflowModules(
                            $user->module_permissions,
                            $oldWorkflow,
                            $validated['workflow_type']
                        );
                    }

                    $user->update($updates);
                });
            }
        });

        return redirect()
            ->route('settings.roles.index')
            ->with('success', 'Role "' . $role->name . '" updated successfully.');
    }

    public function destroy(AccessRole $role)
    {
        if ($role->is_protected) {
            return back()->with('error', 'The protected Super Admin role cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Move users to another role before deleting "' . $role->name . '".');
        }

        $name = $role->name;
        $role->delete();

        return redirect()
            ->route('settings.roles.index')
            ->with('success', 'Role "' . $name . '" deleted successfully.');
    }

    private function validateRole(Request $request, ?AccessRole $role = null): array
    {
        $request->merge([
            'module_permissions' => array_values(array_unique(array_filter(
                $request->input('module_permissions', [])
            ))),
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('access_roles', 'name')->ignore($role?->id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'workflow_type' => ['required', Rule::in(array_keys(AccessRole::workflowOptions()))],
            'module_permissions' => ['required', 'array', 'min:1'],
            'module_permissions.*' => ['string'],
        ]);

        $allowedModules = User::moduleKeysForWorkflow($validated['workflow_type']);
        if (array_diff($validated['module_permissions'], $allowedModules)) {
            throw ValidationException::withMessages([
                'module_permissions' => 'One or more selected modules are invalid for this workflow.',
            ]);
        }

        return $validated;
    }

    private function uniqueSlug(string $name, ?int $ignoredRoleId = null): string
    {
        $base = Str::slug($name) ?: 'role';
        $slug = $base;
        $suffix = 2;

        while (AccessRole::where('slug', $slug)
            ->when($ignoredRoleId, fn ($query) => $query->whereKeyNot($ignoredRoleId))
            ->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    private function remapWorkflowModules(array $modules, string $from, string $to): array
    {
        $fromField = $from === AccessRole::WORKFLOW_FIELD_STAFF;
        $toField = $to === AccessRole::WORKFLOW_FIELD_STAFF;

        if ($fromField === $toField) {
            return array_values(array_intersect($modules, User::moduleKeysForWorkflow($to)));
        }

        $replacements = $toField
            ? [
                User::MODULE_MONITORING_REVIEW => User::MODULE_MONITORING_SUBMIT,
                User::MODULE_MATERIAL_APPROVALS => User::MODULE_MATERIAL_REQUESTS,
            ]
            : [
                User::MODULE_MONITORING_SUBMIT => User::MODULE_MONITORING_REVIEW,
                User::MODULE_MATERIAL_REQUESTS => User::MODULE_MATERIAL_APPROVALS,
            ];

        return collect($modules)
            ->map(fn (string $module) => $replacements[$module] ?? $module)
            ->intersect(User::moduleKeysForWorkflow($to))
            ->values()
            ->all();
    }
}
