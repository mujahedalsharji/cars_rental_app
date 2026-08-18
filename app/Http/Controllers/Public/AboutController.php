<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\SettingService;

class AboutController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    public function index()
    {
        $aboutText = $this->settingService->get('company.about_text');

        return view('pages.about', compact('aboutText'));
    }
}
