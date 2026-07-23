<?php

namespace App\Policies;

use App\Models\MaterialRequest;
use App\Models\User;

class MaterialRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasModuleAccess(User::MODULE_MATERIAL_APPROVALS);
    }

    public function create(User $user): bool
    {
        return $user->hasModuleAccess(User::MODULE_MATERIAL_REQUESTS);
    }

    public function view(User $user, MaterialRequest $materialRequest): bool
    {
        return $user->hasModuleAccess(User::MODULE_MATERIAL_APPROVALS)
            || ($materialRequest->user_id === $user->id && $user->hasModuleAccess(User::MODULE_MATERIAL_REQUESTS));
    }

    public function approve(User $user, MaterialRequest $materialRequest): bool
    {
        return $user->hasModuleAccess(User::MODULE_MATERIAL_APPROVALS);
    }

    public function reject(User $user, MaterialRequest $materialRequest): bool
    {
        return $user->hasModuleAccess(User::MODULE_MATERIAL_APPROVALS);
    }
}
