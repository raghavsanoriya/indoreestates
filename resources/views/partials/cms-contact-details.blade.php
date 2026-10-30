@if($cms->text('contact.address'))<p class="mb-0 mt-2 preserve-lines">{{ $cms->text('contact.address') }}</p>@endif
@if($cms->text('contact.phone'))<p class="mb-0 mt-2"><a href="tel:{{ preg_replace('/[^0-9+]/', '', $cms->text('contact.phone')) }}">{{ $cms->text('contact.phone') }}</a></p>@endif
@if($cms->text('contact.whatsapp'))<p class="mb-0 mt-2"><a href="https://wa.me/{{ preg_replace('/\D/', '', $cms->text('contact.whatsapp')) }}" target="_blank" rel="noopener">WhatsApp {{ $cms->text('contact.whatsapp') }}</a></p>@endif
@if($cms->text('contact.email'))<p class="mb-0 mt-2"><a href="mailto:{{ $cms->text('contact.email') }}">{{ $cms->text('contact.email') }}</a></p>@endif
