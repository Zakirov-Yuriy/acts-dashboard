<?php

namespace Tests\Feature;

use App\Models\Act;
use App\Models\LegalEntity;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PaymentApiTest extends TestCase
{
    use RefreshDatabase;

    private function makePayment(int $ageDays): Payment
    {
        $entity = LegalEntity::create([
            'name' => 'ООО «Тест»', 'inn' => '7700000000',
            'bank_account' => '40700000000000000000',
        ]);
        $project = Project::create([
            'name' => 'Тестовый проект', 'legal_entity_id' => $entity->id, 'status' => 'active',
        ]);

        return Payment::create([
            'project_id' => $project->id,
            'legal_entity_id' => $entity->id,
            'payment_date' => Carbon::today()->subDays($ageDays)->toDateString(),
            'amount' => 100000,
            'payment_purpose' => 'Оплата по договору за разработку',
            'service_stage' => 'Разработка сайта',
        ]);
    }

    public function test_payments_endpoint_returns_paginated_data_with_status(): void
    {
        $payment = $this->makePayment(5);
        Act::create(['payment_id' => $payment->id, 'is_sent' => false, 'is_signed' => false]);

        $this->getJson('/api/payments')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'amount', 'project' => ['name'], 'legal_entity' => ['inn'], 'status' => ['value', 'label', 'color'], 'act']],
                'meta' => ['total', 'current_page', 'last_page'],
            ])
            ->assertJsonPath('data.0.status.value', 'not_sent');
    }

    public function test_summary_counts_are_correct(): void
    {
        $closedPayment = $this->makePayment(40);
        Act::create([
            'payment_id' => $closedPayment->id,
            'is_sent' => true, 'sent_at' => Carbon::today()->subDays(38),
            'is_signed' => true, 'signed_at' => Carbon::today()->subDays(35),
        ]);

        $attentionPayment = $this->makePayment(50);
        Act::create(['payment_id' => $attentionPayment->id, 'is_sent' => false, 'is_signed' => false]);

        $this->getJson('/api/dashboard/summary')
            ->assertOk()
            ->assertJsonPath('data.payments_count', 2)
            ->assertJsonPath('data.closed_acts_count', 1)
            ->assertJsonPath('data.needs_attention_count', 1)
            ->assertJsonPath('data.without_sent_act_count', 1);
    }

    public function test_status_filter_returns_only_matching_payments(): void
    {
        $p1 = $this->makePayment(50);
        Act::create(['payment_id' => $p1->id, 'is_sent' => false]);   // needs_attention
        $p2 = $this->makePayment(3);
        Act::create(['payment_id' => $p2->id, 'is_sent' => true, 'is_signed' => true, 'sent_at' => now(), 'signed_at' => now()]); // closed

        $this->getJson('/api/payments?act_status=needs_attention')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status.value', 'needs_attention');
    }

    public function test_marking_act_signed_auto_marks_sent_and_closes(): void
    {
        $payment = $this->makePayment(5);
        $act = Act::create(['payment_id' => $payment->id, 'is_sent' => false, 'is_signed' => false]);

        $this->patchJson("/api/acts/{$act->id}", ['is_signed' => true])
            ->assertOk()
            ->assertJsonPath('data.is_sent', true)      // подпись автоматически проставила отправку
            ->assertJsonPath('data.is_signed', true)
            ->assertJsonPath('data.status.value', 'closed');

        $this->assertNotNull($act->fresh()->sent_at);
        $this->assertNotNull($act->fresh()->signed_at);
    }

    public function test_unsending_act_also_removes_signature(): void
    {
        $payment = $this->makePayment(5);
        $act = Act::create([
            'payment_id' => $payment->id,
            'is_sent' => true, 'sent_at' => now(),
            'is_signed' => true, 'signed_at' => now(),
        ]);

        $this->patchJson("/api/acts/{$act->id}", ['is_sent' => false])
            ->assertOk()
            ->assertJsonPath('data.is_sent', false)
            ->assertJsonPath('data.is_signed', false);
    }
}
