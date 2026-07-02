<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('assignedProjects')->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();

        return view('settings.users.index', compact('users', 'projects'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'project_ids' => array_values(array_filter($request->input('project_ids', []))),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
            'role' => ['required', Rule::in(['admin', 'employee'])],
            'project_ids' => ['nullable', 'array'],
            'project_ids.*' => ['integer', 'exists:projects,id'],
        ]);

        $projectIds = $validated['role'] === 'employee'
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
        $request->merge([
            'project_ids' => array_values(array_filter($request->input('project_ids', []))),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(6)],
            'role' => ['required', Rule::in(['admin', 'employee'])],
            'project_ids' => ['nullable', 'array'],
            'project_ids.*' => ['integer', 'exists:projects,id'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $projectIds = $validated['role'] === 'employee'
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

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('settings.users.index')
            ->with('success', 'User "' . $name . '" deleted successfully.');
    }
}
