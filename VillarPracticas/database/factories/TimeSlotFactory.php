<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TimeSlotFactory extends Factory
{
    protected $model = \App\Models\TimeSlot::class;

    public function definition(): array
    {
        // Se usará sobre todo con seeder custom
        return [
            'start_time' => '09:00:00',
            'end_time'   => '10:00:00',
        ];
    }
}