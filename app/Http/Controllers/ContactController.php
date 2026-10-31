<?php

namespace App\Http\Controllers;

use App\Models\ContactSubmission;
use App\Models\Property;
use App\Models\Subscriber;
use App\Rules\Recaptcha;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $property = $request->filled('property') ? Property::visible()->find($request->integer('property')) : null;

        return view('contact', compact('property'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', 'max:255'], 'phone' => ['nullable', 'string', 'max:30'], 'body' => ['required', 'string', 'max:5000'], 'g-recaptcha-response' => [new Recaptcha]]);
        ContactSubmission::create(Arr::only($data, ['name', 'email', 'phone', 'body']));

        return back()->with('status', 'Thank you. Your message has reached our team.');
    }

    public function subscribe(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255'], 'g-recaptcha-response' => [new Recaptcha]]);
        Subscriber::firstOrCreate(['email' => mb_strtolower($data['email'])]);

        return back()->with('status', 'You are subscribed to property updates.');
    }
}
