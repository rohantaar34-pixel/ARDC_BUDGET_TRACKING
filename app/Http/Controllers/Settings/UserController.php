<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AccessRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['assignedProjects', 'accessRole'])->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();
        $roles = AccessRole::orderByDesc('is_protected')->orderBy('name')->get();
        $moduleOptionsByRole = $roles->mapWithKeys(fn (AccessRole $role) => [
            (string) $role->id => User::dashboardModuleOptionsForWorkflow($role->workflow_type),
        ])->all();
        $roleDefaults = $roles->mapWithKeys(fn (AccessRole $role) => [
            (string) $role->id => array_values($role->module_permissions ?? []),
        ])->all();
        $roleWorkflows = $roles->mapWithKeys(fn (AccessRole $role) => [
            (string) $role->id => $role->workflow_type,
        ])->all();

        return view('settings.users.index', compact(
            'users',
            'projects',
            'roles',
            'moduleOptionsByRole',
            'roleDefaults',
            'roleWorkflows'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateUser($request);
        $validated['module_permissions'] = array_values(array_unique($validated['module_permissions'] ?? []));
        $accessRole = AccessRole::findOrFail($validated['access_role_id']);
        $validated['role'] = $accessRole->systemRole();

        $projectIds = $accessRole->workflow_type === AccessRole::WORKFLOW_FIELD_STAFF
            ? array_map('intval', $validated['project_ids'] ?? [])
            : [];
        unset($validated['project_ids']);

        $user = User::create($validated);
        $user->assignedProjects()->sync($projectIds);

        return redirect()
            ->route('settings.users.index')
            ->with('success', 'User "' . $validated['name'] . '" created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $this->validateUser($request, $user);
        $validated['module_permissions'] = array_values(array_unique($validated['module_permissions'] ?? []));
        $accessRole = AccessRole::findOrFail($validated['access_role_id']);
        $validated['role'] = $accessRole->systemRole();

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $projectIds = $accessRole->workflow_type === AccessRole::WORKFLOW_FIELD_STAFF
            ? array_map('intval', $validated['project_ids'] ?? [])
            : [];
        unset($validated['project_ids']);

        $user->update($validated);
        $user->assignedProjects()->sync($projectIds);

        return redirect()
            ->route('settings.users.index')
            ->with('success', 'User "' . $user->name . '" updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()
                ->route('settings.users.index')
                ->with('error', 'You cannot delete your own user account while signed in.');
        }

        if (User::count() <= 1) {
            return redirect()
                ->route('settings.users.index')
                ->with('error', 'At least one user account must remain.');
        }

        if ($user->isAdmin() && !$this->hasAnotherAdmin($user->id)) {
            return redirect()
                ->route('settings.users.index')
                ->with('error', 'At least one admin account must remain.');
        }

        if ($user->canManageUsers() && !$this->hasAnotherUsersManager($user->id)) {
            return redirect()
                ->route('settings.users.index')
                ->with('error', 'At least one account with User Management access must remain.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('settings.users.index')
            ->with('success', 'User "' . $name . '" deleted successfully.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $request->merge([
            'project_ids' => array_values(array_filter($request->input('project_ids', []))),
            'module_permissions' => array_values(array_unique(array_filter($request->input('module_permissions', [])))),
        ]);

        $passwordRule = Password::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols();

        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string', 'min:2', 'max:255'],
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    $user
                        ? Rule::unique('users', 'email')->ignore($user->id)
                        : 'unique:users,email',
                ],
                'password' => $user
                        ? ['nullable', 'confirmed', $passwordRule]
                        : ['required', 'confirmed', $passwordRule],
                'position' => ['required', 'string', 'min:2', 'max:255'],
                'access_role_id' => ['required', 'integer', 'exists:access_roles,id'],
                'module_permissions' => ['required', 'array', 'min:1'],
                'module_permissions.*' => ['string'],
                'project_ids' => ['nullable', 'array'],
                'project_ids.*' => ['integer', 'exists:projects,id'],
            ],
            [
                'name.min' => 'Name must be at least 2 characters.',
                'password.confirmed' => 'Password confirmation does not match.',
                'module_permissions.min' => 'Assign at least one module.',
            ]
        );

        $validator->after(function ($validator) use ($request, $user) {
            $accessRole = AccessRole::find($request->integer('access_role_id'));
            if (!$accessRole) {
                return;
            }

            $selectedModules = array_values(array_map('strval', $request->input('module_permissions', [])));
            $allowedModules = User::moduleKeysForWorkflow($accessRole->workflow_type);
            $wouldManageUsers = in_array(User::MODULE_SETTINGS_USERS, $selectedModules, true);
            $wouldBeAdministrator = $accessRole->workflow_type === AccessRole::WORKFLOW_ADMINISTRATOR;

            if (empty($selectedModules)) {
                $validator->errors()->add('module_permissions', 'Assign at least one module.');
            }

            if (array_diff($selectedModules, $allowedModules)) {
                $validator->errors()->add('module_permissions', 'One or more selected modules are invalid for this workflow.');
            }

            if ($accessRole->workflow_type === AccessRole::WORKFLOW_FIELD_STAFF
                && empty($request->input('project_ids', []))) {
                $validator->errors()->add('project_ids', 'Assign at least one project for field staff.');
            }

            if ($user && $user->isAdmin() && !$wouldBeAdministrator && !$this->hasAnotherAdmin($user->id)) {
                $validator->errors()->add('access_role_id', 'At least one administrator account must remain.');
            }

            if ($user && $user->id === Auth::id() && !$wouldManageUsers) {
                $validator->errors()->add('module_permissions', 'You cannot remove your own User Management access while signed in.');
            }

            if ($user && $user->canManageUsers() && !$wouldManageUsers && !$this->hasAnotherUsersManager($user->id)) {
                $validator->errors()->add('module_permissions', 'At least one account with User Management access must remain.');
            }
        });

        return $validator->validate();
    }

    private function hasAnotherAdmin(?int $excludedUserId = null): bool
    {
        return User::query()
            ->where('role', 'admin')
            ->when($excludedUserId, fn ($query) => $query->whereKeyNot($excludedUserId))
            ->exists();
    }

    private function hasAnotherUsersManager(?int $excludedUserId = null): bool
    {
        return User::query()
            ->when($excludedUserId, fn ($query) => $query->whereKeyNot($excludedUserId))
            ->get()
            ->contains(fn (User $candidate) => $candidate->canManageUsers());
    }
}
