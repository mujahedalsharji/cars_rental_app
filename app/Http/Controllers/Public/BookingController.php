<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\CarService;
use App\Services\SettingService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        protected CarService $carService,
        protected SettingService $settingService
    ) {}

    public function show(Request $request): View
    {
        $cars = $this->carService->getAllPublished(['per_page' => 100]);
        $whatsappNumber = $this->settingService->get('contact.whatsapp_number');

        $selectedCar = null;
        if ($request->filled('car')) {
            try {
                $selectedCar = $this->carService->findBySlug($request->string('car')->toString());
            } catch (ModelNotFoundException) {
                $selectedCar = null;
            }
        }

        return view('pages.booking', compact('cars', 'whatsappNumber', 'selectedCar'));
    }
}
