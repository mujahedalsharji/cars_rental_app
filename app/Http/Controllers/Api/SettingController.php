<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicSettingsResource;
use App\Services\SettingService;

class SettingController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    public function index()
    {
        $settings = $this->settingService->all();

        return new PublicSettingsResource((object) $settings);
    }

    public function contact()
    {
        $contactSettings = $this->settingService->getGroup('contact');

        return response()->json([
            'data' => $contactSettings,
        ]);
    }
}
