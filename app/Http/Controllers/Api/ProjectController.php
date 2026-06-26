<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentFilter;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function index(Request $request)
    {
        $filter = PaymentFilter::fromRequest($request);

        return response()->json([
            'data' => $this->payments->projectsOverview($filter),
        ]);
    }
}
