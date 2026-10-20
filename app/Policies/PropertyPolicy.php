<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function create(User $user): bool
    {
        return $user->canList();
    }

    public function view(User $user, Property $property): bool
    {
        return $user->isAdmin() || $property->user_id === $user->id;
    }

    public function update(User $user, Property $property): bool
    {
        return $user->isAdmin() || ($user->role === 'agent' && $property->user_id === $user->id);
    }

    public function delete(User $user, Property $property): bool
    {
        return $this->update($user, $property);
    }

    public function restore(User $user, Property $property): bool
    {
        return $this->update($user, $property);
    }

    public function moderate(User $user, Property $property): bool
    {
        return $user->isAdmin();
    }
}
