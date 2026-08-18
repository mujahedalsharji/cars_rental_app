<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CarDetailResource;
use App\Http\Resources\CarListingResource;
use App\Services\CarService;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function __construct(
        protected CarService $carService
    ) {}

    public function index(Request $request)
    {
        $cars = $this->carService->getAllPublished($request->only(['category', 'search', 'page', 'per_page']));

        return CarListingResource::collection($cars);
    }

    public function show(string $slug)
    {
        $car = $this->carService->findBySlug($slug);

        return new CarDetailResource($car);
    }
}
