<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\BannerService;
use App\Services\CarService;
use App\Services\FaqService;
use App\Services\SettingService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected BannerService $bannerService,
        protected CarService $carService,
        protected FaqService $faqService,
        protected SettingService $settingService
    ) {}

    public function index(): View
    {
        $banners = $this->bannerService->getActive();
        $featuredCars = $this->carService->getFeatured(8);
        $faqs = $this->faqService->getActive()->take(5);
        $settings = $this->settingService->getGroup('company');
        $whatsappNumber = $this->settingService->get('contact.whatsapp_number');

        return view('pages.home', compact('banners', 'featuredCars', 'faqs', 'settings', 'whatsappNumber'));
    }
}
