<?php

namespace App\Services;

use App\Enums\ActStatus;
use Illuminate\Http\Request;

/**
 * Объект фильтра. Превращает «сырой» запрос в типизированный набор критериев,
 * чтобы контроллеры и сервис не зависели от формата HTTP-параметров.
 */
class PaymentFilter
{
    public function __construct(
        public readonly ?int $projectId = null,
        public readonly ?int $legalEntityId = null,
        public readonly ?string $dateFrom = null,
        public readonly ?string $dateTo = null,
        public readonly ?string $serviceStage = null,
        public readonly ?ActStatus $actStatus = null,
        public readonly ?string $search = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $status = $request->query('act_status');

        return new self(
            projectId: $request->integer('project_id') ?: null,
            legalEntityId: $request->integer('legal_entity_id') ?: null,
            dateFrom: $request->query('date_from') ?: null,
            dateTo: $request->query('date_to') ?: null,
            serviceStage: $request->query('service_stage') ?: null,
            actStatus: $status ? ActStatus::tryFrom($status) : null,
            search: trim((string) $request->query('search')) ?: null,
        );
    }

    public function hasStatusFilter(): bool
    {
        return $this->actStatus !== null;
    }
}
