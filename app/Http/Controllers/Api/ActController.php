<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateActRequest;
use App\Http\Resources\ActResource;
use App\Models\Act;
use Illuminate\Support\Carbon;

class ActController extends Controller
{
    /**
     * Обновить акт: отметить отправленным / подписанным / задать комментарий.
     * Даты sent_at и signed_at проставляются автоматически при смене флага.
     */
    public function update(UpdateActRequest $request, Act $act)
    {
        $data = $request->validated();

        if (array_key_exists('is_sent', $data)) {
            $act->is_sent = $data['is_sent'];
            $act->sent_at = $data['is_sent'] ? ($act->sent_at ?? Carbon::today()) : null;

            // Снятие отправки автоматически снимает и подпись (нельзя подписать неотправленный).
            if (! $data['is_sent']) {
                $act->is_signed = false;
                $act->signed_at = null;
            }
        }

        if (array_key_exists('is_signed', $data)) {
            $act->is_signed = $data['is_signed'];
            $act->signed_at = $data['is_signed'] ? ($act->signed_at ?? Carbon::today()) : null;

            // Подписать можно только отправленный акт.
            if ($data['is_signed'] && ! $act->is_sent) {
                $act->is_sent = true;
                $act->sent_at = $act->sent_at ?? Carbon::today();
            }
        }

        if (array_key_exists('manager_comment', $data)) {
            $act->manager_comment = $data['manager_comment'];
        }

        $act->save();
        $act->load('payment');

        return new ActResource($act);
    }
}
