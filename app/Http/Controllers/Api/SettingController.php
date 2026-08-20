<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicSettingsResource;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    public function index(): JsonResource
    {
        $settings = $this->settingService->all();

        return (new PublicSettingsResource((object) $settings))->additional(['success' => true]);
    }

    public function contact(): JsonResponse
    {
        $contactSettings = $this->settingService->getGroup('contact');

        return response()->json([
            'success' => true,
            'data' => $contactSettings,
        ]);
    }
}
