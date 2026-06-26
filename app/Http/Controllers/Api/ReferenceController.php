<?php

namespace App\Http\Controllers\Api;

use App\Enums\ActStatus;
use App\Http\Controllers\Controller;
use App\Models\LegalEntity;
use App\Models\Payment;
use App\Models\Project;

/** Справочники для выпадающих фильтров на фронте. */
class ReferenceController extends Controller
{
    public function index()
    {
        return response()->json([
            'projects' => Project::orderBy('name')->get(['id', 'name']),
            'legal_entities' => LegalEntity::orderBy('name')->get(['id', 'name', 'inn']),
            'service_stages' => Payment::query()
                ->distinct()
                ->orderBy('service_stage')
                ->pluck('service_stage'),
            'act_statuses' => ActStatus::options(),
        ]);
    }
}
