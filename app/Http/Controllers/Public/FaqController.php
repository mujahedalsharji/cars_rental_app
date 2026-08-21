<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\FaqService;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function __construct(
        protected FaqService $faqService
    ) {}

    public function index(): View
    {
        $faqs = $this->faqService->getActive();

        return view('pages.faq', compact('faqs'));
    }
}
