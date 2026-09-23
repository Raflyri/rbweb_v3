<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CustomPage;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CustomPagePolicy
{
    use HandlesAuthorization;

    public const MANAGER_ROLES = ['super_admin', 'admin'];

    public static function userIsManager(?AuthUser $user): bool
    {
        return $user !== null
            && method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(static::MANAGER_ROLES);
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return static::userIsManager($authUser);
    }

    public function view(AuthUser $authUser, CustomPage $page): bool
    {
        return static::userIsManager($authUser);
    }

    public function create(AuthUser $authUser): bool
    {
        return static::userIsManager($authUser);
    }

    public function update(AuthUser $authUser, CustomPage $page): bool
    {
        return static::userIsManager($authUser);
    }

    public function delete(AuthUser $authUser, CustomPage $page): bool
    {
        return static::userIsManager($authUser);
    }
}
