<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentFilter;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function summary(Request $request)
    {
        $filter = PaymentFilter::fromRequest($request);

        return response()->json([
            'data' => $this->payments->summary($filter),
        ]);
    }
}
