<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Services\BannerService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BannerController extends Controller
{
    public function __construct(
        protected BannerService $bannerService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $banners = $this->bannerService->getActive();

        return BannerResource::collection($banners)->additional(['success' => true]);
    }
}
