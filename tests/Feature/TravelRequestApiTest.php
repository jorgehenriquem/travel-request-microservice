<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\TravelRequest;
use App\Services\TravelRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TravelRequestApiTest extends TestCase
{
    use RefreshDatabase;

    private TravelRequestService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new TravelRequestService();
        $this->user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->user, 'api');
    }

    public function test_can_create_travel_request()
    {
        $data = [
            'destination' => fake()->city,
            'departure_date' => now()->addDays(rand(2, 10))->toDateString(),
            'return_date' => now()->addDays(rand(11, 20))->toDateString(),
            'reason' => fake()->sentence,
        ];

        $travelRequest = $this->service->createRequest($data);

        $this->assertInstanceOf(TravelRequest::class, $travelRequest);
        $this->assertEquals($this->user->id, $travelRequest->user_id);
        $this->assertEquals($data['destination'], $travelRequest->destination);
        $this->assertEquals('requested', $travelRequest->status);
    }

    public function test_destination_is_required()
    {
        $data = [
            'departure_date' => now()->addDays(rand(2, 10))->toDateString(),
            'return_date' => now()->addDays(rand(11, 20))->toDateString(),
            'reason' => fake()->sentence,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/travel-requests', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['destination']);
    }

    public function test_departure_date_is_required_and_after_today()
    {
        $data = [
            'destination' => fake()->city,
            'return_date' => now()->addDays(rand(11, 20))->toDateString(),
            'reason' => fake()->sentence,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/travel-requests', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['departure_date']);

        $data['departure_date'] = now()->toDateString();
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/travel-requests', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['departure_date']);
    }

    public function test_return_date_is_required_and_after_departure_date()
    {
        $data = [
            'destination' => fake()->city,
            'departure_date' => now()->addDays(rand(2, 10))->toDateString(),
            'reason' => fake()->sentence,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/travel-requests', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['return_date']);

        $data['return_date'] = now()->addDay()->toDateString();
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/travel-requests', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['return_date']);
    }
    
    public function test_user_cannot_update_own_travel_request_status()
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'requested',
        ]);

        $payload = ['status' => 'approved'];

        $response = $this->actingAs($this->user, 'api')
            ->patchJson("/api/travel-requests/{$travelRequest->id}/status", $payload);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Você não pode alterar o status de uma solicitação criada por você mesmo.'
            ]);
    }

    public function test_other_user_can_update_status()
    {
        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $otherUser->id
        ]);

        $nonAdmninUser = User::factory()->create();

        $payload = ['status' => 'approved'];

        $response = $this->actingAs($nonAdmninUser, 'api')
            ->patchJson("/api/travel-requests/{$travelRequest->id}/status", $payload);

        $response->assertStatus(403)
            ->assertJsonFragment(['message' => 'Acesso negado']);
    }

    public function test_status_is_required_and_must_be_valid()
    {
        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->patchJson("/api/travel-requests/{$travelRequest->id}/status", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        $response = $this->actingAs($this->user, 'api')
            ->patchJson("/api/travel-requests/{$travelRequest->id}/status", ['status' => 'invalid']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_cancellation_reason_is_required_when_status_is_cancelled()
    {
        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $otherUser->id
        ]);

        $payload = ['status' => 'cancelled'];

        $response = $this->actingAs($this->user, 'api')
            ->patchJson("/api/travel-requests/{$travelRequest->id}/status", $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cancellation_reason']);
    }
   
    public function test_can_show_travel_request_by_id()
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/travel-requests/{$travelRequest->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $travelRequest->id,
                'destination' => $travelRequest->destination,
                'status' => $travelRequest->status,
                'user_id' => $travelRequest->user_id,
            ]);
    }

    public function test_cannot_show_travel_request_that_does_not_exist()
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/travel-requests/99999");

        $response->assertStatus(404);
    }

    public function test_user_cannot_view_other_user_travel_request()
    {
        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $regularUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($regularUser, 'api')
            ->getJson("/api/travel-requests/{$travelRequest->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_view_any_travel_request()
    {
        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/travel-requests/{$travelRequest->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $travelRequest->id,
                'user_id' => $otherUser->id,
            ]);
    }

    public function test_can_list_all_travel_requests()
    {
        $count = rand(1, 5);
        TravelRequest::factory()->count($count)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/travel-requests');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'destination',
                        'departure_date',
                        'return_date',
                        'status',
                        'reason',
                        'user_id',
                        'applicant_name',
                    ]
                ],
                'current_page',
                'per_page',
                'total',
            ]);

        $data = $response->json('data');
        $this->assertCount($count, $data);
        $this->assertEquals($count, $response->json('total'));
    }

    public function test_can_filter_travel_requests_by_status()
    {
        TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => TravelRequest::STATUS_REQUESTED,
        ]);

        TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => TravelRequest::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/travel-requests?status=approved');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals(TravelRequest::STATUS_APPROVED, $data[0]['status']);
    }

    public function test_user_sees_only_own_travel_requests()
    {
        $otherUser = User::factory()->create();
        
        TravelRequest::factory()->create([
            'user_id' => $this->user->id,
        ]);

        TravelRequest::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $regularUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($regularUser, 'api')
            ->getJson('/api/travel-requests');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(0, $data);
    }

    public function test_admin_sees_all_travel_requests()
    {
        $otherUser = User::factory()->create();
        
        TravelRequest::factory()->create([
            'user_id' => $this->user->id,
        ]);

        TravelRequest::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/travel-requests');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_can_filter_travel_requests_by_destination()
    {
        $destination1 = fake()->city;
        $destination2 = fake()->city;

        TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'destination' => $destination1,
        ]);

        TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'destination' => $destination2,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/travel-requests?destination={$destination1}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($destination1, $data[0]['destination']);
    }

    public function test_can_filter_travel_requests_by_date_range()
    {
        $startDate = now()->addDays(5)->toDateString();
        $endDate = now()->addDays(15)->toDateString();

        TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => now()->addDays(10),
        ]);

        TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => now()->addDays(20),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/travel-requests?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    public function test_can_filter_travel_requests_by_combined_filters()
    {
        $destination = fake()->city;
        $departureDate = now()->addDays(10);

        TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'destination' => $destination,
            'status' => TravelRequest::STATUS_APPROVED,
            'departure_date' => $departureDate,
        ]);

        TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'destination' => $destination,
            'status' => TravelRequest::STATUS_REQUESTED,
            'departure_date' => $departureDate,
        ]);

        $startDate = now()->addDays(5)->toDateString();
        $endDate = now()->addDays(15)->toDateString();

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/travel-requests?destination={$destination}&status=approved&start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($destination, $data[0]['destination']);
        $this->assertEquals(TravelRequest::STATUS_APPROVED, $data[0]['status']);
    }

}
