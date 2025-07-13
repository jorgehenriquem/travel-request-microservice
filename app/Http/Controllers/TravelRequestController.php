<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTravelRequestRequest;
use App\Http\Requests\UpdateTravelRequestStatusRequest;
use App\Models\TravelRequest;
use App\Services\TravelRequestService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TravelRequestController extends Controller
{
    use AuthorizesRequests;
    private TravelRequestService $travelRequestService;
    private NotificationService $notificationService;

    public function __construct(
        TravelRequestService $travelRequestService,
        NotificationService $notificationService
    ) {
        $this->travelRequestService = $travelRequestService;
        $this->notificationService = $notificationService;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'destination', 'start_date', 'end_date']);
        $travelRequests = $this->travelRequestService->getFilteredRequests($filters);

        return response()->json($travelRequests);
    }

    public function store(CreateTravelRequestRequest $request): JsonResponse
    {
        $travelRequest = $this->travelRequestService->createRequest($request->validated());

        return response()->json($travelRequest, 201);
    }

    public function show(TravelRequest $travelRequest): JsonResponse
    {
        $this->authorize('view', $travelRequest);

        return response()->json($travelRequest);
    }

    public function updateStatus(
        TravelRequest $travelRequest,
        UpdateTravelRequestStatusRequest $request
    ): JsonResponse {
        $this->authorize('updateStatus', $travelRequest);

        $updatedRequest = $this->travelRequestService->updateStatus(
            $travelRequest,
            $request->validated()
        );

        $this->notificationService->sendStatusUpdateNotification($updatedRequest);

        return response()->json($updatedRequest);
    }

    public function cancel(TravelRequest $travelRequest): JsonResponse
    {
        $this->authorize('cancel', $travelRequest);

        if (!$travelRequest->canBeCancelled()) {
            return response()->json([
                'error' => 'Este pedido não pode ser cancelado.'
            ], 422);
        }

        $updatedRequest = $this->travelRequestService->cancelRequest($travelRequest);

        return response()->json($updatedRequest);
    }
}
