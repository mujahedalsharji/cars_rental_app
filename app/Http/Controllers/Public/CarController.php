<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\CarService;
use App\Services\CategoryService;
use App\Services\SettingService;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function __construct(
        protected CarService $carService,
        protected CategoryService $categoryService,
        protected SettingService $settingService
    ) {}

    public function index(Request $request)
    {
        $categories = $this->categoryService->getActive();
        $cars = $this->carService->getAllPublished($request->only(['category', 'search', 'page', 'per_page']));

        return view('pages.cars.index', compact('cars', 'categories'));
    }

    public function show(string $slug)
    {
        $car = $this->carService->findBySlug($slug);
        $whatsappNumber = $this->settingService->get('contact.whatsapp');

        return view('pages.cars.show', compact('car', 'whatsappNumber'));
    }
}
