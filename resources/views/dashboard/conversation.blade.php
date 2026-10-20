@extends('layouts.dashboard')
@section('dashboard-content')
<div class="card"><div class="card-header"><h5>{{ $conversation->property->title }}</h5><p class="mb-0">{{ $conversation->customer->name }} ↔ {{ $conversation->agent->name }}</p></div><div class="card-body">
@foreach($messages->getCollection()->reverse() as $message)<article class="p-3 mb-3 rounded {{ $message->user_id===auth()->id()?'bg-primary-transparent':'bg-light' }}"><p class="fs-14">{{ $message->author->name }} · {{ $message->created_at->format('d M Y, H:i') }}@if($message->user_id===auth()->id()) · {{ $message->read_at?'Read':'Sent' }}@endif</p><p class="preserve-lines mb-0">{{ $message->body }}</p></article>@endforeach{{ $messages->links() }}
@can('reply',$conversation)<form method="post" action="{{ route('conversations.reply',$conversation) }}">@csrf<x-form-field name="body" label="Your Reply" type="textarea" rows="4" maxlength="5000" required/><button class="btn btn-primary">Send Reply</button></form>@else<p class="alert alert-info">Administrator inspection only. Only conversation participants can reply.</p>@endcan
</div></div>
@endsection
