<?php

namespace App\Models;

use App\Enums\ActStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Act extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id', 'is_sent', 'sent_at', 'is_signed', 'signed_at', 'manager_comment',
    ];

    protected function casts(): array
    {
        return [
            'is_sent' => 'boolean',
            'is_signed' => 'boolean',
            'sent_at' => 'date',
            'signed_at' => 'date',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Вычисляемый статус акта. Требует загруженной связи payment
     * (нужна дата оплаты для правила «требует внимания»).
     */
    protected function status(): Attribute
    {
        return Attribute::get(fn (): ActStatus => ActStatus::resolve(
            isSent: (bool) $this->is_sent,
            isSigned: (bool) $this->is_signed,
            paymentDate: $this->payment->payment_date,
            sentAt: $this->sent_at,
        ));
    }
}
