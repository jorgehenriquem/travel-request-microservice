<?php

namespace App\Services;

use App\Models\TravelRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class TravelRequestService
{
    public function getFilteredRequests(array $filters): LengthAwarePaginator
    {
        $query = TravelRequest::with('user');

        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['destination'])) {
            $query->byDestination($filters['destination']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }

        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        return $query->paginate(10);
    }

    public function createRequest(array $data): TravelRequest
    {
        $data['user_id'] = auth()->id();
        $data['applicant_name'] = auth()->user()->name;

        return TravelRequest::create($data);
    }

    public function updateStatus(TravelRequest $travelRequest, array $data): TravelRequest
    {
        $status = $data['status'];
        $updateData = [
            'status' => $status,
        ];

        switch ($status) {
            case TravelRequest::STATUS_APPROVED:
            case 'approved':
                $updateData['approved_at'] = Carbon::now();
                $updateData['cancelled_at'] = null;
                $updateData['cancellation_reason'] = null;
                break;

            case TravelRequest::STATUS_CANCELLED:
            case 'cancelled':
                $updateData['cancelled_at'] = Carbon::now();
                $updateData['cancellation_reason'] = $data['cancellation_reason'] ?? null;
                $updateData['approved_at'] = null;
                break;
        }

        $travelRequest->update($updateData);

        return $travelRequest->fresh();
    }

}
