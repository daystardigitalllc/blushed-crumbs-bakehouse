@php
    $__sectionColors = $tenant->section_colors ?? [];
    $__selectors = $tenant->sectionColorSelectors();
    $__rules = [];

    // Appends `::before` to every comma-separated part of a selector list,
    // e.g. "#reviews, .foo" -> "#reviews::before, .foo::before".
    $__beforeOf = function (string $selectorList): string {
        return implode(', ', array_map(
            fn ($part) => trim($part) . '::before',
            explode(',', $selectorList)
        ));
    };

    foreach ($__sectionColors as $__secId => $__slots) {
        if (!is_array($__slots) || empty($__slots)) {
            continue;
        }
        $__sel = $__selectors[$__secId] ?? null;
        if (!$__sel) {
            continue;
        }

        if (!empty($__slots['bg']) && $__sel['bg'] && $__sel['bg_mode'] !== 'skip') {
            if ($__sel['bg_mode'] === 'before') {
                // The section's real element has no background of its own --
                // a clipped `::before` pseudo-layer paints it, so the
                // override has to target that pseudo-element directly.
                $__rules[] = "body.theme-{$tenant->theme_id} " . $__beforeOf($__sel['bg']) . " { background: {$__slots['bg']} !important; background-image: none !important; }";
            } elseif ($__sel['bg_mode'] === 'gradient') {
                $__rules[] = "body.theme-{$tenant->theme_id} {$__sel['bg']} { background: {$__slots['bg']} !important; background-image: none !important; }";
            } else {
                $__rules[] = "body.theme-{$tenant->theme_id} {$__sel['bg']} { background-color: {$__slots['bg']} !important; }";
            }

            // cta_banner and promo_video sit on top of a shared `::before`
            // scrim (a translucent tint/photo-darkener, always present,
            // themed independently of any per-section override) that would
            // otherwise wash out or mute whatever color is chosen here --
            // neutralize it so the chosen background shows cleanly.
            foreach (explode(',', $__sel['bg']) as $__bgPart) {
                $__bgPart = trim($__bgPart);
                if (str_ends_with($__bgPart, 'cta-video-banner') || str_ends_with($__bgPart, 'video-promo-banner')) {
                    $__rules[] = "body.theme-{$tenant->theme_id} {$__bgPart}::before { background: transparent !important; }";
                }
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

    // --- Order Form Custom Colors & Typography Overrides ---
    $__orderColors = $tenant->orderFormColors();
    $__orderTypo = $tenant->orderFormTypography();

    $__rules[] = "
        /* Order Form Card overrides */
        #order-modal-popup .order-modal-card {
            background-color: {$__orderColors['modal_bg']} !important;
        }
        #order-modal-popup .step h2, 
        #order-modal-popup h2, 
        #order-modal-popup h3 {
            color: {$__orderColors['heading']} !important;
        }
        #order-modal-popup, 
        #order-modal-popup p, 
        #order-modal-popup label, 
        #order-modal-popup .product-desc,
        #order-modal-popup .flavor-desc,
        #order-modal-popup .frosting-desc,
        #order-modal-popup .filling-desc,
        #order-modal-popup span:not(#global-cart-total-estimate) {
            color: {$__orderColors['text']} !important;
        }
        /* Sticky progress / estimate bar overrides */
        #order-modal-popup .sticky-order-summary-bar {
            background: {$__orderColors['accent']}08 !important;
            border-color: {$__orderColors['accent']}33 !important;
        }
        #order-modal-popup #global-cart-total-estimate {
            color: {$__orderColors['accent']} !important;
        }
        /* Buttons overrides */
        #order-modal-popup .btn-primary,
        #order-modal-popup button.advance-btn {
            background-color: {$__orderColors['btn_bg']} !important;
            border-color: {$__orderColors['btn_bg']} !important;
            color: {$__orderColors['btn_text']} !important;
        }
        /* Option Card highlight overrides */
        #order-modal-popup .product:hover,
        #order-modal-popup .choice-chip:hover {
            border-color: {$__orderColors['accent']} !important;
        }
        #order-modal-popup .product.selected,
        #order-modal-popup .choice-chip.selected {
            border-color: {$__orderColors['accent']} !important;
            background-color: {$__orderColors['accent']}12 !important;
            box-shadow: 0 0 0 2px {$__orderColors['accent']}33 !important;
        }
    ";

    if ($__orderTypo['font_family'] !== 'default') {
        $fontName = $__orderTypo['font_family'];
        $fontUrlName = str_replace(' ', '+', $fontName);
        // Prepend font @import to the rules array so it gets compiled before usages
        array_unshift($__rules, "@import url('https://fonts.googleapis.com/css2?family={$fontUrlName}:wght@400;500;600;700;800&display=swap');");
        
        $__rules[] = "
            #order-modal-popup,
            #order-modal-popup *,
            #order-modal-popup h2, 
            #order-modal-popup h3, 
            #order-modal-popup .step h2,
            #order-modal-popup button,
            #order-modal-popup label,
            #order-modal-popup p {
                font-family: '{$fontName}', sans-serif !important;
            }
        ";
    }
@endphp
@if(count($__rules))
<style id="tenant-color-override">
{!! implode("\n", $__rules) !!}
</style>
@endif
