<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Services\BannerService;

class BannerController extends Controller
{
    public function __construct(
        protected BannerService $bannerService
    ) {}

    public function index()
    {
        $banners = $this->bannerService->getActive();

        return BannerResource::collection($banners);
    }
}
