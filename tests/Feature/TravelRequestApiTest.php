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
            'destination' => 'Rio de Janeiro',
            'departure_date' => '2024-03-15',
            'return_date' => '2024-03-20',
            'reason' => 'Conferência'
        ];

        $travelRequest = $this->service->createRequest($data);

        $this->assertInstanceOf(TravelRequest::class, $travelRequest);
        $this->assertEquals($this->user->id, $travelRequest->user_id);
        $this->assertEquals('Rio de Janeiro', $travelRequest->destination);
        $this->assertEquals('requested', $travelRequest->status);
    }
    public function test_destination_is_required()
    {
        $data = [
            'departure_date' => now()->addDays(2)->toDateString(),
            'return_date' => now()->addDays(3)->toDateString(),
            'reason' => 'Viagem de teste'
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/travel-requests', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['destination']);
    }
    public function test_departure_date_is_required_and_after_today()
    {
        $data = [
            'destination' => 'Rio de Janeiro',
            'return_date' => now()->addDays(3)->toDateString(),
            'reason' => 'Viagem de teste'
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
            'destination' => 'Rio de Janeiro',
            'departure_date' => now()->addDays(2)->toDateString(),
            'reason' => 'Viagem de teste'
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
