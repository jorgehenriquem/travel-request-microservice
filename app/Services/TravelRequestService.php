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
        $updateData = [
            'status' => $data['status'],
        ];

        if ($data['status'] === 'approved') {
            $updateData['approved_at'] = Carbon::now();
        } elseif ($data['status'] === 'cancelled') {
            $updateData['cancelled_at'] = Carbon::now();
            $updateData['cancellation_reason'] = $data['cancellation_reason'];
        }

        $travelRequest->update($updateData);

        return $travelRequest->fresh();
    }

    public function cancelRequest(TravelRequest $travelRequest): TravelRequest
    {
        $travelRequest->update([
            'status' => 'cancelled',
            'cancelled_at' => Carbon::now(),
            'cancellation_reason' => 'Cancelado pelo usuário após aprovação'
        ]);

        return $travelRequest->fresh();
    }
}
