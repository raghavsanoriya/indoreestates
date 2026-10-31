<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\Recaptcha;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function loginForm(): View
    {
        return view('auth.login');
    }

    public function registerForm(): View
    {
        return view('auth.register');
    }

    public function forgotForm(): View
    {
        return view('auth.forgot-password');
    }

    public function resetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function login(Request $request): RedirectResponse
    {
        // A Google outage must not lock admins out, so login alone lets an unreachable check through.
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string'], 'g-recaptcha-response' => [new Recaptcha(allowWhenUnavailable: true)]]);
        if (! Auth::attempt(Arr::only($data, ['email', 'password']), $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'The email or password is incorrect.']);
        }
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', 'max:255', 'unique:users'], 'password' => ['required', 'string', PasswordRule::defaults(), 'confirmed'], 'g-recaptcha-response' => [new Recaptcha]]);
        $user = User::create(Arr::only($data, ['name', 'email', 'password']));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'Welcome! Your customer account is ready.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function forgot(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'g-recaptcha-response' => [new Recaptcha]]);
        Password::sendResetLink(Arr::only($data, ['email']));

        return back()->with('status', 'If this email has an account, a reset link has been sent. Local email delivery is written to the application mail log.');
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate(['token' => ['required'], 'email' => ['required', 'email'], 'password' => ['required', 'string', PasswordRule::defaults(), 'confirmed']]);
        $status = Password::reset($data, function (User $user, string $password): void {
            $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });
        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => __($status)]);
        }

        return redirect()->route('login')->with('status', 'Your password was reset. Please sign in.');
    }
}
