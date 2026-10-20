@extends('layouts.dashboard')
@section('content')
<div class="widget-box-2 mess-box dashboard-messages">
    <h5 class="title">Messages</h5><ul class="list-mess">
    @forelse($conversations as $conversation)
        @php($peer=auth()->id()===$conversation->customer_id ? $conversation->agent : $conversation->customer)
        <li class="mess-item"><a class="user-box" href="{{ route('conversations.show',$conversation) }}"><div class="avatar"><img src="{{ $peer->avatar_path ? route('media.avatar',$peer) : asset('branding/avatar-placeholder.svg') }}" alt="" width="34" height="34"></div><div class="content"><div class="name fw-6">{{ $conversation->customer->name }} ↔ {{ $conversation->agent->name }} @if($conversation->unread_count)<span class="badge">{{ $conversation->unread_count }} unread</span>@endif</div><span class="caption-2 text-variant-3">{{ $conversation->updated_at->diffForHumans() }}</span></div></a><p><a href="{{ route('conversations.show',$conversation) }}">{{ $conversation->property->title }}</a></p><p>{{ Str::limit($conversation->messages->first()?->body ?? 'Open your conversation to reply.',200) }}</p></li>
    @empty<li class="dashboard-empty">No conversations yet. Send an inquiry from a property page to get started.</li>@endforelse
    </ul><div class="pagination-wrap">{{ $conversations->links() }}</div>
</div>
@endsection
