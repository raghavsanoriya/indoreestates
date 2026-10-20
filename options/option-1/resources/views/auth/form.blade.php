@extends('layouts.app')
@section('content')
<section class="auth-section"><div class="auth-image"><div><p class="eyebrow">WELCOME TO {{ strtoupper(config('app.name')) }}</p><h1>Your next chapter<br>starts at home.</h1></div></div><div class="auth-panel">
<p class="eyebrow green">YOUR {{ strtoupper(config('app.name')) }} ACCOUNT</p>
<h1>{{ ['login'=>'Welcome back','register'=>'Create your account','forgot'=>'Reset your password','reset'=>'Choose a new password'][$mode] }}</h1>
<p class="muted">{{ $mode==='register' ? 'Save homes, message agents, and find your perfect place.' : 'Everything you need for your next move, in one place.' }}</p>
<form method="post" action="{{ ['login'=>route('login'),'register'=>route('register'),'forgot'=>route('password.email'),'reset'=>route('password.update')][$mode] }}" class="stack-form">
@csrf
@if($mode==='reset')<input type="hidden" name="token" value="{{ $token }}">@endif
@if($mode==='register')<label>Your name<input name="name" autocomplete="name" value="{{ old('name') }}" maxlength="120" required></label>@endif
<label>Email address<input type="email" name="email" autocomplete="email" value="{{ old('email',$email??'') }}" required></label>
@if($mode!=='forgot')<label>Password<input type="password" name="password" autocomplete="{{ $mode==='login'?'current-password':'new-password' }}" required minlength="{{ $mode==='login'?1:8 }}"></label>@endif
@if(in_array($mode,['register','reset']))<label>Confirm password<input type="password" name="password_confirmation" autocomplete="new-password" required minlength="8"></label>@endif
@if($mode==='login')<label class="check"><input type="checkbox" name="remember" value="1">Remember me</label>@endif
<button class="button">{{ ['login'=>'Sign in','register'=>'Create account','forgot'=>'Send reset link','reset'=>'Save new password'][$mode] }} ↗</button>
</form>
@if($mode==='login')<p><a href="{{ route('password.request') }}">Forgot password?</a></p><p>New here? <a href="{{ route('register') }}">Create an account</a></p>
@else<p>Already have an account? <a href="{{ route('login') }}">Sign in</a></p>@endif
@if($mode==='register')<p class="muted small">New accounts are customers. Contact the administrator to request agent access.</p>@endif
</div></section>
@endsection
