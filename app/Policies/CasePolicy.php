<?php

namespace App\Policies;

use App\Models\CaseModel;
use App\Models\User;

/**
 * Policy untuk Case access control.
 */
class CasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('cases.view');
    }

    public function view(User $user, CaseModel $case): bool
    {
        return $user->hasPermissionTo('cases.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('cases.create');
    }

    public function update(User $user, CaseModel $case): bool
    {
        return $user->hasPermissionTo('cases.edit');
    }

    public function delete(User $user, CaseModel $case): bool
    {
        return $user->hasPermissionTo('cases.delete');
    }
}
