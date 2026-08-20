<?php

namespace App\Observers;

use App\Models\Faq;
use App\Services\FaqService;

class FaqObserver
{
    public function __construct(private FaqService $faqService) {}

    public function saved(Faq $faq): void
    {
        $this->faqService->clearCache();
    }

    public function deleted(Faq $faq): void
    {
        $this->faqService->clearCache();
    }
}
