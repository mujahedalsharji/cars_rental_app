<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use App\Support\ServiceCatalog;
use Illuminate\View\View;

class ServicesController extends Controller
{
    public function __construct(
        protected SettingService $settingService,
        protected ServiceCatalog $serviceCatalog,
    ) {}

    public function index(): View
    {
        $whatsappNumber = $this->settingService->get('contact.whatsapp_number');
        $services = $this->serviceCatalog->hubServices();

        return view('pages.services', compact('services', 'whatsappNumber'));
    }

    public function show(string $service): View
    {
        $servicePage = $this->serviceCatalog->find($service);

        abort_if($servicePage === null, 404);

        $whatsappNumber = $this->settingService->get('contact.whatsapp_number');

        return view('pages.services.show', compact('servicePage', 'whatsappNumber'));
    }
}
