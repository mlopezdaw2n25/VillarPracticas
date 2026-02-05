<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = \App\Models\Reservation::class;

    public function definition(): array
    {
        return [
            'user_id' => 1,
            'date' => $this->faker->dateTimeBetween('now', '+7 days')->format('Y-m-d'),
            'time_slot_id' => 1,
        ];
    }
}
