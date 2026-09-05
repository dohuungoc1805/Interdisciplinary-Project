<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterSubscribedMail;
use App\Models\NewsletterSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => 'required|email|max:255']);
        $email = strtolower($data['email']);
        $row = NewsletterSubscription::query()->where('email', $email)->first();
        if ($row) {
            if (! $row->is_active) {
                $row->update(['is_active' => true, 'unsubscribe_token' => Str::random(40)]);
            }

            return back()->with('status', 'You are already on the list. Welcome back!');
        }
        $token = Str::random(40);
        NewsletterSubscription::query()->create([
            'email' => $email,
            'unsubscribe_token' => $token,
            'is_active' => true,
        ]);
        $unsubscribe = URL::route('newsletter.unsubscribe', ['token' => $token]);
        Mail::to($email)->queue(new NewsletterSubscribedMail($unsubscribe));

        return back()->with('status', 'Subscribed! Check your inbox.');
    }

    public function unsubscribe(string $token): View
    {
        $row = NewsletterSubscription::query()->where('unsubscribe_token', $token)->first();
        if ($row) {
            $row->update(['is_active' => false]);
        }

        return view('shop.newsletter-unsubscribed');
    }
}
