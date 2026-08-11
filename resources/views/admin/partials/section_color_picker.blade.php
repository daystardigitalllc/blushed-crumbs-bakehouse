@php
    $__sel = $tenant->sectionColorSelectors()[$secId] ?? null;
    $__defaults = $tenant->themePaletteDefaults();
    $__saved = data_get($tenant->section_colors, $secId, []);
    $__bgMode = $__sel['bg_mode'] ?? 'skip';
    $__hasButton = !empty($__sel['button']);
    $__hasButton2 = !empty($__sel['button2']);

    $__fields = [];
    if ($__bgMode !== 'skip') {
        $__fields[] = ['slot' => 'bg', 'label' => 'Background'];
    }
    $__fields[] = ['slot' => 'heading', 'label' => 'Heading Text'];
    $__fields[] = ['slot' => 'text', 'label' => 'Body Text'];
    if ($__hasButton) {
        $__fields[] = ['slot' => 'button_bg', 'label' => $__hasButton2 ? 'Button 1 Background' : 'Button Background'];
        $__fields[] = ['slot' => 'button_text', 'label' => $__hasButton2 ? 'Button 1 Text' : 'Button Text'];
    }
    if ($__hasButton2) {
        $__fields[] = ['slot' => 'button2_bg', 'label' => 'Button 2 Background'];
        $__fields[] = ['slot' => 'button2_text', 'label' => 'Button 2 Text'];
    }
@endphp
<div style="margin-top:14px; padding-top:14px; border-top:1px dashed #ddd;">
    <p style="font-weight:700; font-size:0.78rem; color:#555; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.5px;">Section Colors</p>
    @if($__bgMode === 'gradient')
        <p style="font-size:0.76rem; color:#a16207; margin:0 0 10px 0; background:#fef9c3; padding:6px 10px; border-radius:6px;">Setting a Background color here replaces this section's photo/gradient with a flat color.</p>
    @endif
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:12px;">
        @foreach($__fields as $f)
            @php
                $__slot = $f['slot'];
                $__current = $__saved[$__slot] ?? null;
                $__default = $__defaults[$__slot] ?? '#ffffff';
                $__inputId = "seccolor-{$secId}-{$__slot}";
            @endphp
            <div>
                <label style="font-weight:600; font-size:0.76rem; color:#555; display:flex; align-items:center; gap:5px; margin-bottom:4px;">
                    <input type="checkbox" class="section-color-toggle" data-target="{{ $__inputId }}" {{ $__current ? 'checked' : '' }} onchange="toggleSectionColorInput(this)">
                    {{ $f['label'] }}
                </label>
                <input type="color"
                       name="section_colors[{{ $secId }}][{{ $__slot }}]"
                       id="{{ $__inputId }}"
                       value="{{ $__current ?: $__default }}"
                       data-default="{{ $__default }}"
                       {{ $__current ? '' : 'disabled' }}
                       style="width:100%; height:36px; border-radius:6px; border:1px solid #ccc; cursor:pointer;">
            </div>
        @endforeach
    </div>
</div>
