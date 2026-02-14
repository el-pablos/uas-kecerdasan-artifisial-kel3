<?php

namespace App\Policies;

use App\Models\Node;
use App\Models\User;

/**
 * Policy untuk Node (entity) access control.
 */
class NodePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('knowledge.view');
    }

    public function view(User $user, Node $node): bool
    {
        return $user->hasPermissionTo('knowledge.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('knowledge.create');
    }

    public function update(User $user, Node $node): bool
    {
        return $user->hasPermissionTo('knowledge.edit');
    }

    public function delete(User $user, Node $node): bool
    {
        return $user->hasPermissionTo('knowledge.delete');
    }
}
