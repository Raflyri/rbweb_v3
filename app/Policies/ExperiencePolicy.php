<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Experience;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExperiencePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Experience');
    }

    public function view(AuthUser $authUser, Experience $experience): bool
    {
        return $authUser->can('View:Experience');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Experience');
    }

    public function update(AuthUser $authUser, Experience $experience): bool
    {
        return $authUser->can('Update:Experience');
    }

    public function delete(AuthUser $authUser, Experience $experience): bool
    {
        return $authUser->can('Delete:Experience');
    }

    public function restore(AuthUser $authUser, Experience $experience): bool
    {
        return $authUser->can('Restore:Experience');
    }

    public function forceDelete(AuthUser $authUser, Experience $experience): bool
    {
        return $authUser->can('ForceDelete:Experience');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Experience');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Experience');
    }

    public function replicate(AuthUser $authUser, Experience $experience): bool
    {
        return $authUser->can('Replicate:Experience');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Experience');
    }

}