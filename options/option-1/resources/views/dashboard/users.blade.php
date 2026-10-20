@extends('layouts.dashboard')
@section('content')
<p class="eyebrow green">ADMINISTRATION</p><h1>User access</h1><p class="muted">Approve an agent by granting agent access. Customers can browse, save homes, and send inquiries.</p>
<div class="panel table-wrap"><table><thead><tr><th>User</th><th>Email</th><th>Access</th></tr></thead><tbody>@foreach($users as $user)<tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>@if($user->isAdmin())<span class="badge">Administrator</span>@else<form method="post" action="{{ route('admin.role',$user) }}" class="actions">@csrf @method('PATCH')<select name="role" aria-label="Role for {{ $user->name }}"><option value="customer" @selected($user->role==='customer')>Customer</option><option value="agent" @selected($user->role==='agent')>Agent</option></select><button class="button small">Save</button></form>@endif</td></tr>@endforeach</tbody></table>{{ $users->links() }}</div>
@endsection
