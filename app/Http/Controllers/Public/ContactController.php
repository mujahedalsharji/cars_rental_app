<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\ContactFormRequest;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    public function index()
    {
        $contactSettings = $this->settingService->getGroup('contact');

        return view('pages.contact', compact('contactSettings'));
    }

    public function submit(ContactFormRequest $request): RedirectResponse
    {
        $request->validated();

        return back()->with('success', 'Thank you for your message. We will get back to you soon.');
    }
}
