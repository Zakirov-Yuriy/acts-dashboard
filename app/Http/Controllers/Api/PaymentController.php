<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Services\PaymentFilter;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function index(Request $request)
    {
        $filter = PaymentFilter::fromRequest($request);
        $perPage = (int) $request->integer('per_page', 25) ?: 25;
        $page = (int) $request->integer('page', 1) ?: 1;

        $paginator = $this->payments->paginate($filter, $perPage, $page);

        return PaymentResource::collection($paginator);
    }
}
