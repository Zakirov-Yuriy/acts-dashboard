<?php

namespace App\Http\Resources;

use App\Enums\ActStatus;
use App\Models\Act;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Act */
class ActResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->payment
            ? $this->payment->actStatus()
            : ActStatus::resolve((bool) $this->is_sent, (bool) $this->is_signed, $this->created_at);

        return [
            'id' => $this->id,
            'is_sent' => (bool) $this->is_sent,
            'sent_at' => $this->sent_at?->toDateString(),
            'is_signed' => (bool) $this->is_signed,
            'signed_at' => $this->signed_at?->toDateString(),
            'manager_comment' => $this->manager_comment,
            'status' => [
                'value' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
            ],
        ];
    }
}
