@php
    $napAddress = $tenant->napAddressLine();
    $napPhone = $tenant->phone;
@endphp
@if($napAddress || $napPhone)
    <p class="footer-nap">
        @if($napAddress)<span class="footer-nap-address">{{ $napAddress }}</span>@endif
        @if($napAddress && $napPhone) &middot; @endif
        @if($napPhone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $napPhone) }}" class="footer-link footer-nap-phone">{{ $napPhone }}</a>@endif
    </p>
@endif
