<?php

namespace App\Policies;

use App\Models\Cow;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Cow Policy
 * 
 * Demonstrates Laravel Authorization (Policies)
 * 
 * Authorization Rules:
 * - All authenticated users can view/list cows (public read access)
 * - All authenticated users can create cows
 * - Only admins can update/delete cows (role-based authorization)
 * 
 * Admin detection: Users with email ending in @admin.example.com
 */
class CowPolicy
{
    /**
     * Determine whether the user can view any models.
     * 
     * All authenticated users can view the list of cows.
     */
    public function viewAny(User $user): bool
    {
        return true; // Public read access for authenticated users
    }

    /**
     * Determine whether the user can view the model.
     * 
     * All authenticated users can view individual cows.
     */
    public function view(User $user, Cow $cow): bool
    {
        return true; // Public read access for authenticated users
    }

    /**
     * Determine whether the user can create models.
     * 
     * All authenticated users can create cows.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create
    }

    /**
     * Determine whether the user can update the model.
     * 
     * Only admins can update cows.
     */
    public function update(User $user, Cow $cow): bool|Response
    {
        // Ensure we have the latest user data from the database
        $user->refresh();
        
        if (!$this->isAdmin($user)) {
            return Response::deny('You do not have permission to update this cow.');
        }
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * Only admins can delete cows.
     */
    public function delete(User $user, Cow $cow): bool|Response
    {
        // Ensure we have the latest user data from the database
        $user->refresh();
        
        if (!$this->isAdmin($user)) {
            return Response::deny('You do not have permission to delete this cow.');
        }
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     * 
     * Only admins can restore soft-deleted cows.
     */
    public function restore(User $user, Cow $cow): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     * 
     * Only admins can force delete cows.
     */
    public function forceDelete(User $user, Cow $cow): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Check if user is an admin.
     * 
     * Admin detection based on email pattern (matching feature flags).
     * In a real app, you'd use a role column or role system.
     */
    private function isAdmin(User $user): bool
    {
        $email = $user->email;
        
        if (!$email) {
            return false;
        }
        
        return str_ends_with($email, '@admin.example.com') 
            || str_contains($email, 'admin@');
    }
}
