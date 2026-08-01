@php
    $napAddress = $tenant->napAddressLine();
@endphp
@if($napAddress)
    <p class="footer-nap">
        <span class="footer-nap-address">{{ $napAddress }}</span>
    </p>
@endif
