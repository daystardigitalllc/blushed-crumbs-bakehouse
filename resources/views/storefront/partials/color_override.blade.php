@php
    $__colorOverrides = $tenant->customColorOverrides();
@endphp
@if(count($__colorOverrides) || !empty($tenant->button_color))
<style id="tenant-color-override">
@if(count($__colorOverrides))
body.theme-{{ $tenant->theme_id }} {
    @foreach($__colorOverrides as $__cssVar => $__cssVal)
    {{ $__cssVar }}: {{ $__cssVal }} !important;
    @endforeach
}
@endif
@if(!empty($tenant->button_color))
body.theme-{{ $tenant->theme_id }} .btn-primary,
body.theme-{{ $tenant->theme_id }} .nav-order-btn {
    background: {{ $tenant->button_color }} !important;
    border-color: {{ $tenant->button_color }} !important;
}
@endif
</style>
@endif
