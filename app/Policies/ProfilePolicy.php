<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Profile;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProfilePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Profile');
    }

    protected function ownsOrIsAdmin(AuthUser $authUser, Profile $profile): bool
    {
        return $authUser->id === $profile->user_id || $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function view(AuthUser $authUser, Profile $profile): bool
    {
        return $authUser->can('View:Profile') && $this->ownsOrIsAdmin($authUser, $profile);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Profile');
    }

    public function update(AuthUser $authUser, Profile $profile): bool
    {
        return $authUser->can('Update:Profile') && $this->ownsOrIsAdmin($authUser, $profile);
    }

    public function delete(AuthUser $authUser, Profile $profile): bool
    {
        return $authUser->can('Delete:Profile') && $this->ownsOrIsAdmin($authUser, $profile);
    }


    public function restore(AuthUser $authUser, Profile $profile): bool
    {
        return $authUser->can('Restore:Profile');
    }

    public function forceDelete(AuthUser $authUser, Profile $profile): bool
    {
        return $authUser->can('ForceDelete:Profile');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Profile');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Profile');
    }

    public function replicate(AuthUser $authUser, Profile $profile): bool
    {
        return $authUser->can('Replicate:Profile');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Profile');
    }

}