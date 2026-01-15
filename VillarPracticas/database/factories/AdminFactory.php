<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AdminFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
 {
        return [
            'nom'=>fake()->sentence,
            'email'=>fake()->sentence,
            'contrasenya'=>fake()->sentence,
            'correo_notificaciones'=>fake()->sentence,
            'rol'=>fake()->sentence,
            'activo'=>fake()->sentence,
        ];
    }
}
