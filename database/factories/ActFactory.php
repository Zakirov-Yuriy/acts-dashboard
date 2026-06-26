<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActFactory extends Factory
{
    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'is_sent' => false,
            'sent_at' => null,
            'is_signed' => false,
            'signed_at' => null,
            'manager_comment' => null,
        ];
    }
}
