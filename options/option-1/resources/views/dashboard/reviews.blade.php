@extends('layouts.dashboard')
@section('content')
<div class="widget-box-2 mess-box">
<h5 class="title">Recent Reviews</h5><ul class="list-mess">
@forelse($reviews as $review)<li class="mess-item">
    <div class="user-box"><div class="avatar"><img src="{{ $review->author->avatar_path ? route('media.avatar',$review->author) : asset('branding/avatar-placeholder.svg') }}" alt="" width="34" height="34"></div><div class="content"><div class="name fw-6">{{ $review->author->name }}</div><span class="caption-2 text-variant-3">{{ $review->created_at->diffForHumans() }}</span></div></div>
    <p><strong>{{ $review->property->title }}</strong></p><p class="preserve-lines">{{ $review->body }}</p>
    <ul class="list-star" aria-label="{{ $review->rating }} out of 5 stars">@for($star=1;$star<=5;$star++)<li @class(['icon','icon-star','inactive'=>$star>$review->rating]) aria-hidden="true"></li>@endfor</ul>
    <div class="actions"><span class="status {{ $review->status }}">{{ ucfirst($review->status) }}</span>
    @if($review->user_id===auth()->id() && $review->property->isPublic())<a class="button small outline" href="{{ route('properties.show',$review->property) }}">Edit review on property</a>@endif
    @can('delete',$review)<form method="post" action="{{ route('reviews.destroy',$review) }}" data-confirm="Delete this review?">@csrf @method('DELETE')<button class="button small danger">Delete</button></form>@endcan
    @if(auth()->user()->isAdmin())<form method="post" action="{{ route('reviews.moderate',$review) }}">@csrf @method('PATCH')<button class="button small" name="status" value="approved">Approve</button><button class="button small outline" name="status" value="rejected">Reject</button></form>@endif
    </div>
</li>@empty<li class="dashboard-empty">No reviews yet.</li>@endforelse
</ul><div class="pagination-wrap">{{ $reviews->links() }}</div>
</div>
@endsection
