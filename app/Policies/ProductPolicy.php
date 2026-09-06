<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Product;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

/**
 * Authorisation for the product catalogue.
 *
 * Unlike ArticlePolicy, this is not a workflow — there is no client-facing
 * half of it. Prices and, later, orders and payment confirmations are the
 * business itself, so the whole resource is restricted to the two roles that
 * run it. Article authors in the Client Area must never reach any of this:
 * the panel separation already hides it (each panel discovers its own
 * resource directory), and this policy is the second lock on the same door.
 *
 * Deliberately role-based rather than Shield permission-based: a Shield
 * permission is one careless click in the Roles UI away from being handed to
 * a role that should not have it, and there is no editorial reason for anyone
 * but the owners to touch pricing.
 */
class ProductPolicy
{
    use HandlesAuthorization;

    /** The only roles allowed anywhere near the catalogue. */
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

    public function view(AuthUser $authUser, Product $product): bool
    {
        return static::userIsManager($authUser);
    }

    public function create(AuthUser $authUser): bool
    {
        return static::userIsManager($authUser);
    }

    public function update(AuthUser $authUser, Product $product): bool
    {
        return static::userIsManager($authUser);
    }

    public function delete(AuthUser $authUser, Product $product): bool
    {
        return static::userIsManager($authUser);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return static::userIsManager($authUser);
    }

    public function restore(AuthUser $authUser, Product $product): bool
    {
        return static::userIsManager($authUser);
    }

    public function forceDelete(AuthUser $authUser, Product $product): bool
    {
        return static::userIsManager($authUser);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return static::userIsManager($authUser);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return static::userIsManager($authUser);
    }
}
