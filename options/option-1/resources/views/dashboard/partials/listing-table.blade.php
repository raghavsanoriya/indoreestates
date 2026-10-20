<div class="wrap-table"><div class="table-responsive"><table>
    <thead><tr><th>Listing</th><th>Status</th><th>Action</th></tr></thead>
    <tbody>
    @forelse($properties as $property)
    <tr>
        <td><div class="listing-box">
            <div class="images"><img src="{{ $property->coverUrl() }}" alt="{{ $property->title }}" width="615" height="405" loading="lazy"></div>
            <div class="content"><div class="title"><a class="link" href="{{ $property->trashed() ? route('listings.index') : route('listings.preview',$property) }}">{{ $property->title }}</a></div>
                <div class="text-date">Posting date: {{ $property->created_at->format('F j, Y') }}</div>
                <div class="text-btn text-primary">{{ $property->priceLabel() }}</div>
                <div class="import-label">{{ $property->location }}@if(auth()->user()->isAdmin()) · {{ $property->owner->name }}@endif @if($property->source_key)<br>Imported · {{ $property->source_details['developer'] }}@endif</div>
            </div>
        </div></td>
        <td><div class="status-wrap"><span @class(['btn-status',$property->trashed()?'trashed':$property->status])>{{ $property->trashed() ? 'Trashed' : ucfirst($property->status) }}</span>
            <span class="status-note">{{ $property->source_key ? 'Confirm unit availability' : ucfirst($property->availability) }}</span>
            @if(!$property->isInIndore())<span class="status-note">Hidden from public: outside Indore</span>@endif
            @if($property->rejection_reason)<span class="status-note">{{ $property->rejection_reason }}</span>@endif
        </div></td>
        <td><ul class="list-action">
            @if($property->trashed())
                <li><form method="post" action="{{ route('listings.restore',$property->id) }}">@csrf<button class="item">↶ Restore draft</button></form></li>
            @else
                <li><a class="item" href="{{ route('listings.edit',$property) }}"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m4 16-1 5 5-1L21 7l-4-4L4 16Zm11-11 4 4" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>Edit</a></li>
                <li><a class="item" href="{{ route('listings.preview',$property) }}"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="1.5"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/></svg>Preview</a></li>
                <li><form method="post" action="{{ route('listings.destroy',$property) }}" data-confirm="Move this listing to trash? You can restore it later.">@csrf @method('DELETE')<button class="item"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M9 7V3h6v4M6 7l1 14h10l1-14M10 11v6m4-6v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>Trash</button></form></li>
            @endif
        </ul>
        @if(auth()->user()->isAdmin() && !$property->trashed())<details class="listing-moderation"><summary>Moderate listing</summary><form method="post" action="{{ route('listings.moderate',$property) }}" class="stack-form">@csrf @method('PATCH')<label>Decision<select name="decision"><option value="published">Approve & publish</option><option value="rejected">Reject</option></select></label><label>Reason for rejection<textarea name="rejection_reason" maxlength="2000"></textarea></label><button class="button small">Save decision</button></form></details>@endif
        </td>
    </tr>
    @empty<tr><td colspan="3"><div class="dashboard-empty">No listings match this search.</div></td></tr>@endforelse
    </tbody>
</table></div><div class="pagination-wrap">{{ $properties->links() }}</div></div>
