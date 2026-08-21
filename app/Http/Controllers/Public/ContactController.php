<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\ContactFormRequest;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    public function index(): View
    {
        $contactSettings = $this->settingService->getGroup('contact');

        return view('pages.contact', compact('contactSettings'));
    }

    public function submit(ContactFormRequest $request): RedirectResponse
    {
        $request->validated();

        return back()->with('success', 'شكراً لتواصلك. سيعود إليك فريقنا في أقرب وقت.');
    }
}
