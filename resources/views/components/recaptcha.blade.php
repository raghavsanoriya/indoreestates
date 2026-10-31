@props(['size' => 'normal'])
@if(\App\Support\Recaptcha::enabled())
<div {{ $attributes->class(['o3-recaptcha mb-3']) }}><div class="g-recaptcha" data-sitekey="{{ \App\Support\Recaptcha::siteKey() }}" data-size="{{ $size === 'compact' ? 'compact' : 'normal' }}"></div>@error('g-recaptcha-response')<p class="text-danger mt-1 mb-0">{{ $message }}</p>@enderror</div>
@once
@push('scripts')
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endpush
@endonce
@endif
