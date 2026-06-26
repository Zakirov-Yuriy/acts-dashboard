<?php

namespace Database\Factories;

use App\Models\LegalEntity;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Проект '.$this->faker->bothify('??-###'),
            'legal_entity_id' => LegalEntity::factory(),
            'status' => $this->faker->randomElement(['active', 'on_hold', 'closed']),
        ];
    }
}
