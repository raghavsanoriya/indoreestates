@extends('layouts.app')
@section('content')
<section class="page-intro"><div class="container-wide"><p class="eyebrow green">LET’S TALK</p><h1>Get in touch</h1><p>Our team is here to help you find your next home.</p></div></section>
<section class="section container-wide"><div class="panel narrow"><h2>Send our team a message</h2><form method="post" action="{{ route('contact.store') }}" class="stack-form">@csrf
<label>Name<input name="name" value="{{ old('name',auth()->user()?->name) }}" required maxlength="120"></label>
<label>Email<input type="email" name="email" value="{{ old('email',auth()->user()?->email) }}" required></label>
<label>Phone (optional)<input name="phone" value="{{ old('phone') }}" maxlength="30"></label>
<label>Message<textarea name="body" required maxlength="5000">{{ old('body', $property ? 'Please share current pricing and availability for '.$property->title.' ('.$property->reference.').' : '') }}</textarea></label><button class="button">Send message ↗</button></form></div></section>
@endsection
