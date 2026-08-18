<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;

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

    public function submit(Request $request)
    {
        // For a simple implementation without a database write
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:2000',
        ]);

        return back()->with('success', 'Thank you for your message. We will get back to you soon.');
    }
}
