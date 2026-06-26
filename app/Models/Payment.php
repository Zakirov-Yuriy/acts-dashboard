<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'legal_entity_id', 'payment_date', 'amount',
        'payment_purpose', 'service_stage', 'invoice_number', 'contract_number',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** Плательщик. */
    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class);
    }

    public function act(): HasOne
    {
        return $this->hasOne(Act::class);
    }

    /** Связи, нужные для расчёта статуса и отображения. */
    public function scopeWithDashboardRelations(Builder $query): Builder
    {
        return $query->with(['project', 'legalEntity', 'act']);
    }

    /**
     * Статус закрывающего документа по этой оплате.
     * Работает и когда акта ещё нет (тогда он «не отправлен»).
     */
    public function actStatus(): \App\Enums\ActStatus
    {
        $act = $this->act;

        return \App\Enums\ActStatus::resolve(
            isSent: (bool) $act?->is_sent,
            isSigned: (bool) $act?->is_signed,
            paymentDate: $this->payment_date,
            sentAt: $act?->sent_at,
        );
    }
}
