@extends('layouts.dashboard')
@section('content')
<p class="eyebrow green">PROPERTY CONVERSATION</p><h1>{{ $conversation->property->title }}</h1><p class="muted">{{ $conversation->customer->name }} ↔ {{ $conversation->agent->name }} · <a href="{{ route('conversations.index') }}">All messages</a></p>
<div class="panel">@foreach($messages->getCollection()->reverse() as $message)<article @class(['message-bubble','mine'=>$message->user_id===auth()->id()])><small>{{ $message->author->name }} · {{ $message->created_at->format('d M Y, H:i') }}@if($message->user_id===auth()->id()) · {{ $message->read_at?'Read':'Sent' }}@endif</small><p class="preserve-lines">{{ $message->body }}</p></article>@endforeach
{{ $messages->links() }}
<form method="post" action="{{ route('conversations.reply',$conversation) }}" class="stack-form">@csrf<label>Your reply<textarea name="body" maxlength="5000" required>{{ old('body') }}</textarea></label><button class="button">Send reply ↗</button></form></div>
@endsection
