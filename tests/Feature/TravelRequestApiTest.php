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
        $this->user = User::factory()->create();
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
    
}
