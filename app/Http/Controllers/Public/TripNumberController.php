<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\TripNumberService;
use Illuminate\Http\JsonResponse;

class TripNumberController extends Controller
{
    public function __invoke(TripNumberService $tripNumberService): JsonResponse
    {
        return response()->json([
            'trip_number' => $tripNumberService->reserve(),
        ]);
    }
}
