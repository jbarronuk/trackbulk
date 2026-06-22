<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class SiteController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Welcome', $this->commonProps());
    }

    public function plans(): Response
    {
        return Inertia::render(
            Auth::check() ? 'AuthPlans' : 'Plans',
            $this->commonProps() + ['stripe_prices' => config('subscriptions.stripe_prices')]
        );
    }

    public function contact(): Response
    {
        return Inertia::render('Contact');
    }

    public function submitContactForm(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string|min:10',
        ]);

        $body = "Name: {$validated['name']}\nEmail: {$validated['email']}\n\nMessage:\n{$validated['message']}";

        Mail::raw($body, function ($message) {
            $message->to(config('mail.contact_address'))
                ->subject('New Contact Form Submission');
        });

        return redirect()->back()->with('success', 'Your message has been sent!');
    }

    private function commonProps(): array
    {
        return [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ];
    }
}