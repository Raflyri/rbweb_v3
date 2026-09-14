<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Achievement;
use Illuminate\Auth\Access\HandlesAuthorization;

class AchievementPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Achievement');
    }

    protected function ownsOrIsAdmin(AuthUser $authUser, Achievement $achievement): bool
    {
        return $authUser->id === $achievement->user_id || $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function view(AuthUser $authUser, Achievement $achievement): bool
    {
        return $authUser->can('View:Achievement') && $this->ownsOrIsAdmin($authUser, $achievement);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Achievement');
    }

    public function update(AuthUser $authUser, Achievement $achievement): bool
    {
        return $authUser->can('Update:Achievement') && $this->ownsOrIsAdmin($authUser, $achievement);
    }

    public function delete(AuthUser $authUser, Achievement $achievement): bool
    {
        return $authUser->can('Delete:Achievement') && $this->ownsOrIsAdmin($authUser, $achievement);
    }


    public function restore(AuthUser $authUser, Achievement $achievement): bool
    {
        return $authUser->can('Restore:Achievement');
    }

    public function forceDelete(AuthUser $authUser, Achievement $achievement): bool
    {
        return $authUser->can('ForceDelete:Achievement');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Achievement');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Achievement');
    }

    public function replicate(AuthUser $authUser, Achievement $achievement): bool
    {
        return $authUser->can('Replicate:Achievement');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Achievement');
    }

}