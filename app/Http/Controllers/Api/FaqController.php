<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqResource;
use App\Services\FaqService;

class FaqController extends Controller
{
    public function __construct(
        protected FaqService $faqService
    ) {}

    public function index()
    {
        $faqs = $this->faqService->getActive();

        return FaqResource::collection($faqs);
    }
}
