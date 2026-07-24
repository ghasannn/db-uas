<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EventPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperadmin() || $user->isOrganizer();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Event $event): bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        return $user->isOrganizer() && $user->organization_id && (int)$user->organization_id === (int)$event->organization_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        if ($user->isOrganizer() && $user->organization) {
            return $user->organization->status === 'approved';
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Event $event): bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        return $user->isOrganizer() && $user->organization_id && (int)$user->organization_id === (int)$event->organization_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Event $event): bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        return $user->isOrganizer() && $user->organization_id && (int)$user->organization_id === (int)$event->organization_id;
    }
}
