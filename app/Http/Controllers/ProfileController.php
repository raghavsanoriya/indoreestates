<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('dashboard.profile', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)],
            'phone' => ['nullable', 'string', 'max:30'], 'company' => ['nullable', 'string', 'max:120'], 'position' => ['nullable', 'string', 'max:120'],
            'office_address' => ['nullable', 'string', 'max:255'], 'bio' => ['nullable', 'string', 'max:5000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'social_links' => ['nullable', 'array:facebook,twitter,linkedin'],
            'social_links.*' => ['nullable', 'url:http,https', 'max:255'],
            'current_password_for_email' => [Rule::requiredIf(fn () => $request->input('email') !== $request->user()->email), 'nullable', 'current_password'],
        ], ['current_password_for_email.required' => 'Enter your current password to change your email address.']);
        unset($data['avatar'], $data['current_password_for_email']);
        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'local');
        }
        $request->user()->update($data);

        return back()->with('status', 'Profile updated.');
    }

    public function password(Request $request): RedirectResponse
    {
        $data = $request->validate(['current_password' => ['required', 'current_password'], 'password' => ['required', 'string', Password::defaults(), 'confirmed']]);
        $request->user()->forceFill(['password' => Hash::make($data['password']), 'remember_token' => Str::random(60)])->save();
        $request->session()->regenerate();

        return back()->with('status', 'Password updated.');
    }
}
