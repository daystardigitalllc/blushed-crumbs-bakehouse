@php
    $__sectionColors = $tenant->section_colors ?? [];
    $__selectors = $tenant->sectionColorSelectors();
    $__rules = [];

    foreach ($__sectionColors as $__secId => $__slots) {
        if (!is_array($__slots) || empty($__slots)) {
            continue;
        }
        $__sel = $__selectors[$__secId] ?? null;
        if (!$__sel) {
            continue;
        }

        if (!empty($__slots['bg']) && $__sel['bg'] && $__sel['bg_mode'] !== 'skip') {
            if ($__sel['bg_mode'] === 'gradient') {
                $__rules[] = "body.theme-{$tenant->theme_id} {$__sel['bg']} { background: {$__slots['bg']} !important; background-image: none !important; }";
            } else {
                $__rules[] = "body.theme-{$tenant->theme_id} {$__sel['bg']} { background-color: {$__slots['bg']} !important; }";
            }
        }

        if (!empty($__slots['heading']) && $__sel['heading']) {
            $__rules[] = "body.theme-{$tenant->theme_id} {$__sel['heading']} { color: {$__slots['heading']} !important; }";
        }

        if (!empty($__slots['text']) && $__sel['text']) {
            $__rules[] = "body.theme-{$tenant->theme_id} {$__sel['text']} { color: {$__slots['text']} !important; }";
        }

        if ($__sel['button']) {
            if (!empty($__slots['button_bg'])) {
                $__rules[] = "body.theme-{$tenant->theme_id} {$__sel['button']} { background: {$__slots['button_bg']} !important; border-color: {$__slots['button_bg']} !important; }";
            }
            if (!empty($__slots['button_text'])) {
                $__rules[] = "body.theme-{$tenant->theme_id} {$__sel['button']} { color: {$__slots['button_text']} !important; }";
            }
        }

        if (!empty($__sel['button2'])) {
            if (!empty($__slots['button2_bg'])) {
                $__rules[] = "body.theme-{$tenant->theme_id} {$__sel['button2']} { background: {$__slots['button2_bg']} !important; border-color: {$__slots['button2_bg']} !important; }";
            }
            if (!empty($__slots['button2_text'])) {
                $__rules[] = "body.theme-{$tenant->theme_id} {$__sel['button2']} { color: {$__slots['button2_text']} !important; }";
            }
        }
    }
@endphp
@if(count($__rules))
<style id="tenant-color-override">
{!! implode("\n", $__rules) !!}
</style>
@endif
