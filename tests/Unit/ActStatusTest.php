<?php

namespace Tests\Unit;

use App\Enums\ActStatus;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class ActStatusTest extends TestCase
{
    private Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();
        $this->now = Carbon::create(2026, 6, 1);
    }

    public function test_not_sent_for_fresh_unsent_act(): void
    {
        $status = ActStatus::resolve(
            isSent: false, isSigned: false,
            paymentDate: $this->now->copy()->subDays(5),
            now: $this->now,
        );
        $this->assertSame(ActStatus::NotSent, $status);
    }

    public function test_awaiting_signature_when_sent_recently(): void
    {
        $status = ActStatus::resolve(
            isSent: true, isSigned: false,
            paymentDate: $this->now->copy()->subDays(5),
            sentAt: $this->now->copy()->subDays(2),
            now: $this->now,
        );
        $this->assertSame(ActStatus::AwaitingSignature, $status);
    }

    public function test_closed_when_sent_and_signed(): void
    {
        $status = ActStatus::resolve(
            isSent: true, isSigned: true,
            paymentDate: $this->now->copy()->subDays(40),
            sentAt: $this->now->copy()->subDays(35),
            now: $this->now,
        );
        $this->assertSame(ActStatus::Closed, $status);
    }

    public function test_needs_attention_when_old_payment_without_sent_act(): void
    {
        $status = ActStatus::resolve(
            isSent: false, isSigned: false,
            paymentDate: $this->now->copy()->subDays(45), // старше 30 дней
            now: $this->now,
        );
        $this->assertSame(ActStatus::NeedsAttention, $status);
    }

    public function test_needs_attention_when_sent_long_ago_but_unsigned(): void
    {
        $status = ActStatus::resolve(
            isSent: true, isSigned: false,
            paymentDate: $this->now->copy()->subDays(40),
            sentAt: $this->now->copy()->subDays(20), // отправлен >14 дней назад
            now: $this->now,
        );
        $this->assertSame(ActStatus::NeedsAttention, $status);
    }

    public function test_labels_are_in_russian(): void
    {
        $this->assertSame('Закрыт', ActStatus::Closed->label());
        $this->assertSame('Требует внимания', ActStatus::NeedsAttention->label());
    }
}
