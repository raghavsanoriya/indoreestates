@extends('layouts.auth')
@section('content')
<form method="post" action="{{ route('register') }}">@csrf<div class="login-item-01"><h4>Create Your Account</h4>
<div class="mb-3"><label for="name" class="form-label">Name</label><input id="name" name="name" value="{{ old('name') }}" class="form-control" maxlength="120" required autocomplete="name"></div>
<div class="mb-3"><label for="email" class="form-label">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required autocomplete="email"></div>
<div class="mb-3"><label for="password" class="form-label">Password</label><input id="password" type="password" name="password" class="form-control" minlength="12" required autocomplete="new-password"></div>
<div class="mb-3"><label for="password_confirmation" class="form-label">Confirm Password</label><input id="password_confirmation" type="password" name="password_confirmation" class="form-control" minlength="12" required autocomplete="new-password"></div><p>New accounts are customers. Contact the administrator to request agent access.</p><button class="btn btn-lg btn-primary w-100 mb-3">Create Account</button><p class="text-center">Already have an account? <a href="{{ route('login') }}">Sign In</a></p></div></form>
@endsection
