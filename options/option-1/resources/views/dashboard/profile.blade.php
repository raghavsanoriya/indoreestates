@extends('layouts.dashboard')
@section('content')
<form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="widget-box-2 dashboard-profile-form">@csrf @method('PUT')
    <div class="box"><h5 class="title">Account Settings</h5><div class="box-agent-account"><h6>{{ ucfirst($user->role) }} Account</h6><p class="note">Your account access is managed by the administrator. Profile changes cannot grant or remove agent access.</p></div></div>
    <div class="box"><h5 class="title">Avatar</h5><div class="box-agent-avt"><div class="avatar"><img src="{{ $user->avatar_path ? route('media.avatar',$user) : asset('branding/avatar-placeholder.svg') }}" alt="Your profile photo" width="128" height="128"></div><div class="content uploadfile"><label for="profile-avatar">Upload a new avatar</label><div class="box-ip"><input id="profile-avatar" type="file" name="avatar" accept="image/jpeg,image/png,image/webp"></div><p>JPEG, PNG or WebP · up to 2 MB</p></div></div></div>
    <h5 class="title">Information</h5>
    <div class="box box-fieldset"><label for="profile-name">Full name: *</label><input id="profile-name" name="name" value="{{ old('name',$user->name) }}" required maxlength="120"></div>
    <div class="box box-fieldset"><label for="profile-bio">Description:</label><textarea id="profile-bio" name="bio" maxlength="5000">{{ old('bio',$user->bio) }}</textarea></div>
    <div class="box dashboard-profile-grid">
        @foreach(['company'=>'Your Company','position'=>'Position','phone'=>'Your Phone','office_address'=>'Office Address'] as $field=>$label)<div class="box-fieldset"><label for="profile-{{ $field }}">{{ $label }}:</label><input id="profile-{{ $field }}" name="{{ $field }}" value="{{ old($field,$user->$field) }}"></div>@endforeach
    </div>
    <div class="box box-fieldset"><label for="profile-email">Email address: *</label><input id="profile-email" type="email" name="email" value="{{ old('email',$user->email) }}" required></div>
    @foreach(['facebook','twitter','linkedin'] as $network)<div class="box box-fieldset"><label for="profile-{{ $network }}">{{ ucfirst($network) }}:</label><input id="profile-{{ $network }}" type="url" name="social_links[{{ $network }}]" value="{{ old('social_links.'.$network,$user->social_links[$network]??'') }}" placeholder="https://"></div>@endforeach
    <button class="button">Save & Update</button>
</form>
<form method="post" action="{{ route('profile.password') }}" class="widget-box-2 dashboard-profile-form mt-30">@csrf @method('PUT')
    <h5 class="title">Change password</h5>
    <div class="box dashboard-password-grid"><div class="box-fieldset"><label>Current password<input type="password" name="current_password" autocomplete="current-password" required></label></div><div class="box-fieldset"><label>New password<input type="password" name="password" minlength="8" autocomplete="new-password" required></label></div><div class="box-fieldset"><label>Confirm new password<input type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required></label></div></div>
    <button class="button">Update password</button>
</form>
@endsection
