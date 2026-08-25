<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\View\View;

class ServicesController extends Controller
{
    public function __construct(protected SettingService $settingService) {}

    public function index(): View
    {
        $whatsappNumber = $this->settingService->get('contact.whatsapp_number');

        return view('pages.services', compact('whatsappNumber'));
    }
}
