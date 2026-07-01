<?php

namespace App\Actions\Jetstream;

use App\Models\PortfolioSnapshot;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Permanently delete the user and all user-owned data.
     */
    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            /*
             * Portfolio snapshots currently do not have a cascading
             * foreign-key constraint, so they must be removed manually.
             */
            PortfolioSnapshot::where('user_id', $user->id)->delete();

            $user->deleteProfilePhoto();

            /*
             * Remove any personal access tokens belonging to the user.
             */
            $user->tokens()->delete();

            /*
             * Other related FinSight records use cascadeOnDelete().
             */
            $user->delete();
        });
    }
}