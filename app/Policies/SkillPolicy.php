<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Skill;
use Illuminate\Auth\Access\HandlesAuthorization;

class SkillPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Skill');
    }

    protected function ownsOrIsAdmin(AuthUser $authUser, Skill $skill): bool
    {
        return $authUser->id === $skill->user_id || $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function view(AuthUser $authUser, Skill $skill): bool
    {
        return $authUser->can('View:Skill') && $this->ownsOrIsAdmin($authUser, $skill);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Skill');
    }

    public function update(AuthUser $authUser, Skill $skill): bool
    {
        return $authUser->can('Update:Skill') && $this->ownsOrIsAdmin($authUser, $skill);
    }

    public function delete(AuthUser $authUser, Skill $skill): bool
    {
        return $authUser->can('Delete:Skill') && $this->ownsOrIsAdmin($authUser, $skill);
    }


    public function restore(AuthUser $authUser, Skill $skill): bool
    {
        return $authUser->can('Restore:Skill');
    }

    public function forceDelete(AuthUser $authUser, Skill $skill): bool
    {
        return $authUser->can('ForceDelete:Skill');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Skill');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Skill');
    }

    public function replicate(AuthUser $authUser, Skill $skill): bool
    {
        return $authUser->can('Replicate:Skill');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Skill');
    }

}