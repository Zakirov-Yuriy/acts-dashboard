<?php

namespace Database\Factories;

use App\Models\LegalEntity;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'legal_entity_id' => LegalEntity::factory(),
            'payment_date' => $this->faker->dateTimeBetween('-150 days', 'now')->format('Y-m-d'),
            'amount' => $this->faker->randomFloat(2, 15000, 350000),
            'payment_purpose' => 'Оплата по договору',
            'service_stage' => $this->faker->randomElement(['Разработка сайта', 'SEO', 'Реклама', 'Дизайн']),
            'invoice_number' => $this->faker->numerify('СЧ-####'),
            'contract_number' => $this->faker->numerify('Д-####/25'),
        ];
    }
}
