<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LegalEntityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'ООО «'.$this->faker->company().'»',
            'inn' => (string) $this->faker->numerify('##########'),
            'kpp' => (string) $this->faker->numerify('#########'),
            'ogrn' => (string) $this->faker->numerify('#############'),
            'bank_account' => '4070'.$this->faker->numerify('################'),
            'bank_name' => $this->faker->randomElement(['ПАО Сбербанк', 'АО «Тинькофф Банк»', 'АО «Альфа-Банк»']),
            'contact_person' => $this->faker->name(),
        ];
    }
}
