<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\FaqService;

class FaqController extends Controller
{
    public function __construct(
        protected FaqService $faqService
    ) {}

    public function index()
    {
        $faqs = $this->faqService->getActive();

        return view('pages.faq', compact('faqs'));
    }
}
