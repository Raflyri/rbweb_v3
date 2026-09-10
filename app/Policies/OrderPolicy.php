<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

/**
 * Authorisation for orders — admin/super_admin only, same as the catalogue.
 *
 * Orders carry a buyer's name, phone number and home address. Nobody without a
 * business reason to fulfil them should be able to list, open, or edit one, and
 * that emphatically includes the Client Area roles that exist for writing
 * articles. Buyers themselves never come through here: they reach their own
 * single order by its unguessable token, not by any authenticated route.
 */
class OrderPolicy
{
    use HandlesAuthorization;

    /** The only roles allowed to see or touch orders. */
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

    public function view(AuthUser $authUser, Order $order): bool
    {
        return static::userIsManager($authUser);
    }

    /**
     * Orders are created by buyers through the public form, never typed into
     * the admin panel — so there is deliberately no way to create one here.
     */
    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function update(AuthUser $authUser, Order $order): bool
    {
        return static::userIsManager($authUser);
    }

    /**
     * May this user declare money received, or send a receipt back?
     *
     * Its own ability rather than a lean on update(): confirming a payment is
     * the moment an order becomes something the shop owes goods for, and it
     * deserves a gate that can be tightened without touching everything else
     * an admin does to an order.
     */
    public function confirmPayment(AuthUser $authUser, Order $order): bool
    {
        return static::userIsManager($authUser);
    }

    public function delete(AuthUser $authUser, Order $order): bool
    {
        // Cancelling is a status change, not a deletion: an order that
        // disappears takes the only record of what was agreed with it.
        return $authUser->hasRole('super_admin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function restore(AuthUser $authUser, Order $order): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function forceDelete(AuthUser $authUser, Order $order): bool
    {
        return false;
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return false;
    }
}
