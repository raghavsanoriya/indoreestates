@extends('layouts.auth')
@section('content')
<form method="post" action="{{ route('password.email') }}">@csrf<div class="login-item-01"><h4>Forgot Password?</h4><p>Enter your account email to request a password reset link.</p><div class="mb-3"><label for="email" class="form-label">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required autocomplete="email"></div><button class="btn btn-lg btn-primary w-100 mb-3">Send Reset Link</button><p class="text-center"><a href="{{ route('login') }}">Back to Sign In</a></p></div></form>
@endsection
