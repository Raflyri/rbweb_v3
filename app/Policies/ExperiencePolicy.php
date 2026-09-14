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

    protected function ownsOrIsAdmin(AuthUser $authUser, Experience $experience): bool
    {
        return $authUser->id === $experience->user_id || $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function view(AuthUser $authUser, Experience $experience): bool
    {
        return $authUser->can('View:Experience') && $this->ownsOrIsAdmin($authUser, $experience);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Experience');
    }

    public function update(AuthUser $authUser, Experience $experience): bool
    {
        return $authUser->can('Update:Experience') && $this->ownsOrIsAdmin($authUser, $experience);
    }

    public function delete(AuthUser $authUser, Experience $experience): bool
    {
        return $authUser->can('Delete:Experience') && $this->ownsOrIsAdmin($authUser, $experience);
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