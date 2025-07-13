<?php

namespace Database\Factories;

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TravelRequestFactory extends Factory
{
    protected $model = TravelRequest::class;

    public function definition()
    {
        $departure = $this->faker->dateTimeBetween('+2 days', '+10 days');
        $return = (clone $departure)->modify('+5 days');

        return [
            'user_id' => User::factory(),
            'applicant_name' => $this->faker->name,
            'destination' => $this->faker->city,
            'departure_date' => $departure->format('Y-m-d'),
            'return_date' => $return->format('Y-m-d'),
            'status' => TravelRequest::STATUS_REQUESTED,
            'reason' => $this->faker->sentence,
        ];
    }
} 