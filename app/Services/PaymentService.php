<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Слой бизнес-логики дашборда. Здесь живут фильтрация, пагинация,
 * расчёт сводки и агрегатов по проектам. Контроллеры остаются тонкими.
 *
 * Решение по архитектуре: «обычные» фильтры (проект, юрлицо, период, этап,
 * поиск) выполняются в SQL. Фильтр по статусу акта применяется в PHP через
 * единый App\Enums\ActStatus, чтобы не дублировать правило статуса в SQL.
 * Для текущего объёма (мок-данные) это дёшево; путь масштабирования описан
 * в README (денормализованный статус, обновляемый по расписанию).
 */
class PaymentService
{
    /** Запрос с применёнными «обычными» (SQL) фильтрами. */
    public function query(PaymentFilter $filter): Builder
    {
        return Payment::query()
            ->withDashboardRelations()
            ->when($filter->projectId, fn (Builder $q, $id) => $q->where('project_id', $id))
            ->when($filter->legalEntityId, fn (Builder $q, $id) => $q->where('legal_entity_id', $id))
            ->when($filter->serviceStage, fn (Builder $q, $s) => $q->where('service_stage', $s))
            ->when($filter->dateFrom, fn (Builder $q, $d) => $q->whereDate('payment_date', '>=', $d))
            ->when($filter->dateTo, fn (Builder $q, $d) => $q->whereDate('payment_date', '<=', $d))
            ->when($filter->search, function (Builder $q, string $term) {
                $like = '%'.$term.'%';
                $q->where(function (Builder $q) use ($like) {
                    $q->where('payment_purpose', 'like', $like)
                        ->orWhereHas('project', fn (Builder $p) => $p->where('name', 'like', $like))
                        ->orWhereHas('legalEntity', fn (Builder $le) => $le->where('name', 'like', $like));
                });
            })
            ->orderByDesc('payment_date')
            ->orderByDesc('id');
    }

    /**
     * Полная отфильтрованная коллекция оплат (включая фильтр по статусу).
     * Используется и таблицей, и сводкой, и агрегатами — гарантирует,
     * что цифры в сводке всегда согласованы с тем, что видно в таблице.
     */
    public function collect(PaymentFilter $filter): Collection
    {
        $payments = $this->query($filter)->get();

        if ($filter->hasStatusFilter()) {
            $payments = $payments->filter(
                fn (Payment $p) => $p->actStatus() === $filter->actStatus
            )->values();
        }

        return $payments;
    }

    /** Пагинация поверх отфильтрованной коллекции. */
    public function paginate(PaymentFilter $filter, int $perPage, int $page): LengthAwarePaginator
    {
        $all = $this->collect($filter);

        $items = $all->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            items: $items,
            total: $all->count(),
            perPage: $perPage,
            currentPage: $page,
            options: ['path' => LengthAwarePaginator::resolveCurrentPath()],
        );
    }

    /** Сводка по дашборду для текущего набора фильтров. */
    public function summary(PaymentFilter $filter): array
    {
        $payments = $this->collect($filter);

        $closed = $payments->filter(fn (Payment $p) => $p->actStatus()->isClosed());
        $open = $payments->reject(fn (Payment $p) => $p->actStatus()->isClosed());

        return [
            'total_amount' => round((float) $payments->sum('amount'), 2),
            'payments_count' => $payments->count(),
            'projects_count' => $payments->pluck('project_id')->unique()->count(),
            'closed_amount' => round((float) $closed->sum('amount'), 2),
            'open_amount' => round((float) $open->sum('amount'), 2),
            'closed_acts_count' => $closed->count(),
            // Оплаты без отправленного акта.
            'without_sent_act_count' => $payments
                ->filter(fn (Payment $p) => ! (bool) $p->act?->is_sent)->count(),
            // Акт отправлен, но не подписан.
            'sent_not_signed_count' => $payments
                ->filter(fn (Payment $p) => (bool) $p->act?->is_sent && ! (bool) $p->act?->is_signed)
                ->count(),
            // Требуют внимания.
            'needs_attention_count' => $payments
                ->filter(fn (Payment $p) => $p->actStatus() === \App\Enums\ActStatus::NeedsAttention)
                ->count(),
        ];
    }

    /** Агрегаты по проектам в рамках текущих фильтров. */
    public function projectsOverview(PaymentFilter $filter): Collection
    {
        return $this->collect($filter)
            ->groupBy('project_id')
            ->map(function (Collection $payments) {
                /** @var Payment $first */
                $first = $payments->first();
                $closed = $payments->filter(fn (Payment $p) => $p->actStatus()->isClosed());

                return [
                    'project_id' => $first->project_id,
                    'project_name' => $first->project?->name,
                    'legal_entity_name' => $first->project?->legalEntity?->name,
                    'inn' => $first->project?->legalEntity?->inn,
                    'total_amount' => round((float) $payments->sum('amount'), 2),
                    'payments_count' => $payments->count(),
                    'closed_acts_count' => $closed->count(),
                    'open_acts_count' => $payments->count() - $closed->count(),
                    'needs_attention_count' => $payments
                        ->filter(fn (Payment $p) => $p->actStatus() === \App\Enums\ActStatus::NeedsAttention)
                        ->count(),
                    // Общий статус документооборота по проекту.
                    'doc_status' => $this->projectDocStatus($payments),
                ];
            })
            ->sortByDesc('total_amount')
            ->values();
    }

    /** Сводный статус документооборота проекта: closed / attention / in_progress. */
    private function projectDocStatus(Collection $payments): string
    {
        if ($payments->contains(fn (Payment $p) => $p->actStatus() === \App\Enums\ActStatus::NeedsAttention)) {
            return 'attention';
        }

        if ($payments->every(fn (Payment $p) => $p->actStatus()->isClosed())) {
            return 'closed';
        }

        return 'in_progress';
    }
}
