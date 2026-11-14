<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    /** Every signed-in role can see the purchase order list. */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, User::ROLES, true) || $user->isAdmin();
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->viewAny($user);
    }

    /** Purchasing staff, managers and admins create orders; warehouse staff only receive. */
    public function create(User $user): bool
    {
        return $user->hasRole('manager', 'staff');
    }

    /** Only draft orders, and only by the creator or an admin. */
    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $purchaseOrder->canBeEdited()
            && ($user->id === $purchaseOrder->created_by || $user->isAdmin());
    }

    public function submit(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $purchaseOrder->canBeSubmitted()
            && ($user->id === $purchaseOrder->created_by || $user->isManager() || $purchaseOrder->is_auto_generated);
    }

    /** Only draft orders, and only by an admin. */
    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $purchaseOrder->canBeEdited() && $user->isAdmin();
    }

    public function cancel(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $purchaseOrder->canBeCancelled() && $user->isManager();
    }

    /** Managers and admins only. */
    public function approve(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $purchaseOrder->canBeApproved() && $user->isManager();
    }

    public function reject(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->approve($user, $purchaseOrder);
    }

    public function send(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $purchaseOrder->canBeSent() && $user->isManager();
    }

    /** Warehouse staff and admins. */
    public function receive(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $purchaseOrder->canBeReceived() && $user->isWarehouse();
    }
}
