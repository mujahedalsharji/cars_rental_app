<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\CarService;
use App\Services\SettingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        protected CarService $carService,
        protected SettingService $settingService
    ) {}

    public function show(Request $request)
    {
        $cars = $this->carService->getAllPublished(['per_page' => 100]);
        $whatsappNumber = $this->settingService->get('contact.whatsapp');

        $selectedCar = null;
        if ($request->has('car')) {
            try {
                $selectedCar = $this->carService->findBySlug($request->car);
            } catch (\Exception $e) {
                // If invalid car is passed, default to null
            }
        }

        return view('pages.booking', compact('cars', 'whatsappNumber', 'selectedCar'));
    }
}
