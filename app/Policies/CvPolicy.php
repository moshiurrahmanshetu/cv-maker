<?php

namespace App\Policies;

use App\Models\Cv;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CvPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Cv $cv): Response
    {
        return $user->id === $cv->user_id || $user->isAdmin()
            ? Response::allow()
            : Response::deny('You do not have permission to view this CV.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Cv $cv): Response
    {
        return $user->id === $cv->user_id || $user->isAdmin()
            ? Response::allow()
            : Response::deny('You do not have permission to edit this CV.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Cv $cv): Response
    {
        return $user->id === $cv->user_id || $user->isAdmin()
            ? Response::allow()
            : Response::deny('You do not have permission to delete this CV.');
    }

    /**
     * Determine whether the user can duplicate the model.
     */
    public function duplicate(User $user, Cv $cv): Response
    {
        return $user->id === $cv->user_id || $user->isAdmin()
            ? Response::allow()
            : Response::deny('You do not have permission to duplicate this CV.');
    }
}
