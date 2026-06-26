<?php

namespace App\Enums;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Статус закрывающего документа (акта).
 *
 * Это ЕДИНСТВЕННЫЙ источник правды для расчёта статуса. Статус не хранится в БД,
 * а вычисляется из флагов is_sent / is_signed и дат, потому что правило
 * «требует внимания» зависит от текущей даты и иначе устаревало бы.
 */
enum ActStatus: string
{
    case NotSent = 'not_sent';
    case AwaitingSignature = 'awaiting_signature';
    case Closed = 'closed';
    case NeedsAttention = 'needs_attention';

    /** Нет акта дольше стольких дней после оплаты — проблема. */
    public const ATTENTION_DAYS = 30;

    /** Акт отправлен, но не подписан дольше стольких дней — нужно тормошить клиента. */
    public const SIGN_WAIT_DAYS = 14;

    public function label(): string
    {
        return match ($this) {
            self::NotSent => 'Не отправлен',
            self::AwaitingSignature => 'Ожидает подписи',
            self::Closed => 'Закрыт',
            self::NeedsAttention => 'Требует внимания',
        };
    }

    /** Семантический цвет бейджа для фронтенда. */
    public function color(): string
    {
        return match ($this) {
            self::NotSent => 'gray',
            self::AwaitingSignature => 'amber',
            self::Closed => 'green',
            self::NeedsAttention => 'red',
        };
    }

    /** Закрытым актом считается отправленный и подписанный. */
    public function isClosed(): bool
    {
        return $this === self::Closed;
    }

    /**
     * Вычислить статус из примитивов. Чистая функция: легко тестируется,
     * не зависит от Eloquent-модели.
     */
    public static function resolve(
        bool $isSent,
        bool $isSigned,
        CarbonInterface $paymentDate,
        ?CarbonInterface $sentAt = null,
        ?CarbonInterface $now = null,
    ): self {
        $now ??= Carbon::now();

        // Отправлен и подписан — закрыт, дальше не смотрим.
        if ($isSent && $isSigned) {
            return self::Closed;
        }

        // Не отправлен слишком долго после оплаты — требует внимания.
        if (! $isSent && $paymentDate->lt($now->copy()->subDays(self::ATTENTION_DAYS))) {
            return self::NeedsAttention;
        }

        // Отправлен, но клиент долго не подписывает — тоже требует внимания.
        if ($isSent && ! $isSigned && $sentAt !== null
            && $sentAt->lt($now->copy()->subDays(self::SIGN_WAIT_DAYS))) {
            return self::NeedsAttention;
        }

        // Отправлен, но ещё не подписан (в пределах нормы) — ждём подпись.
        if ($isSent && ! $isSigned) {
            return self::AwaitingSignature;
        }

        // Иначе акт ещё не отправлен (в пределах нормы).
        return self::NotSent;
    }

    /** @return array<int, array{value:string,label:string,color:string}> */
    public static function options(): array
    {
        return array_map(fn (self $s) => [
            'value' => $s->value,
            'label' => $s->label(),
            'color' => $s->color(),
        ], self::cases());
    }
}
