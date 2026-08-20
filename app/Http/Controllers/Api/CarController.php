<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CarListRequest;
use App\Http\Resources\CarDetailResource;
use App\Http\Resources\CarListingResource;
use App\Services\CarService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class CarController extends Controller
{
    public function __construct(
        protected CarService $carService
    ) {}

    public function index(CarListRequest $request): AnonymousResourceCollection
    {
        $cars = $this->carService->getAllPublished($request->validated());

        return CarListingResource::collection($cars)->additional(['success' => true]);
    }

    public function show(string $slug): JsonResource
    {
        $car = $this->carService->findBySlug($slug);

        return (new CarDetailResource($car))->additional(['success' => true]);
    }
}
