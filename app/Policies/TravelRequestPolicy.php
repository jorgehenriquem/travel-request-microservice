<?php

namespace App\Policies;

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TravelRequestPolicy
{
    use HandlesAuthorization;

    public function view(User $user, TravelRequest $travelRequest): bool
    {
        return $user->isAdmin() || $travelRequest->user_id === $user->id;
    }

    public function update(User $user, TravelRequest $travelRequest): bool
    {
        return $travelRequest->user_id === $user->id && $travelRequest->status === 'requested';
    }

    public function updateStatus(User $user, TravelRequest $travelRequest): bool
    {
        return $user->isAdmin();
    }

    public function cancel(User $user, TravelRequest $travelRequest): bool
    {
        return $travelRequest->user_id === $user->id;
    }
}
