<?php

namespace App\Services;

use App\Models\TravelRequest;
use App\Notifications\TravelRequestStatusUpdated;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    public function sendStatusUpdateNotification(TravelRequest $travelRequest): void
    {
        $travelRequest->user->notify(new TravelRequestStatusUpdated($travelRequest));
    }
}
