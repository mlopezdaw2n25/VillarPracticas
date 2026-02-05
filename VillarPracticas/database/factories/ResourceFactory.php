<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ResourceFactory extends Factory
{
    protected $model = \App\Models\Resource::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['material', 'space']);

        return [
            'name' => $type === 'space'
                ? 'Plató ' . $this->faker->randomElement([1,2,'Superior'])
                : $this->faker->randomElement(['Cámara', 'Trípode', 'Micrófono', 'Foco', 'Batería']) . ' #' . $this->faker->numberBetween(1, 10),

            'type' => $type,
            'total_units' => $type === 'space'
                ? 1
                : $this->faker->numberBetween(2, 10),

            'active' => true,
        ];
    }
}
