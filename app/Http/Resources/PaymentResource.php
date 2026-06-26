<?php

namespace App\Http\Resources;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Payment */
class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->actStatus();

        return [
            'id' => $this->id,
            'payment_date' => $this->payment_date?->toDateString(),
            'amount' => (float) $this->amount,
            'payment_purpose' => $this->payment_purpose,
            'service_stage' => $this->service_stage,
            'invoice_number' => $this->invoice_number,
            'contract_number' => $this->contract_number,
            'project' => [
                'id' => $this->project?->id,
                'name' => $this->project?->name,
            ],
            'legal_entity' => [
                'id' => $this->legalEntity?->id,
                'name' => $this->legalEntity?->name,
                'inn' => $this->legalEntity?->inn,
            ],
            // Статус дублируем на уровне оплаты для удобной сортировки/раскраски строки.
            'status' => [
                'value' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
            ],
            'act' => $this->act
                ? new ActResource($this->act)
                : null,
        ];
    }
}
