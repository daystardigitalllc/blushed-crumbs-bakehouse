<!-- POPUP MODAL ORDER FORM BUILDER -->
<div id="order-modal-popup" class="order-modal-overlay" style="display:none;">
    <div class="order-modal-card">
        <button class="modal-close-btn" onclick="closeOrderModal()">✕</button>

        <div id="cake-order-builder">
            @php
                $steps = $tenant->form_schema ?? [];
                $totalSteps = count($steps);
                $fulfillmentSettings = $tenant->normalizedFulfillmentSettings();
                $fulfillmentOptions = [];
                if (!empty($fulfillmentSettings['pickup_enabled'])) $fulfillmentOptions[] = 'pickup';
                if (!empty($fulfillmentSettings['delivery_enabled'])) $fulfillmentOptions[] = 'delivery';
                if (!empty($fulfillmentSettings['shipping_enabled'])) $fulfillmentOptions[] = 'shipping';
                if (empty($fulfillmentOptions)) $fulfillmentOptions[] = 'pickup';
                $fulfillmentLabels = ['pickup' => 'Pickup', 'delivery' => 'Delivery', 'shipping' => 'Shipping'];
            @endphp
            <script>
                window._fulfillmentSettings = @json($fulfillmentSettings);
                window._fulfillmentOptions = @json($fulfillmentOptions);
            </script>

            @if($totalSteps > 0)
                <!-- STICKY ORDER ESTIMATE BAR & PROGRESS INDICATOR -->
                <div class="sticky-order-summary-bar" style="background: linear-gradient(135deg, #fff5f8, #fdeef4); border: 2px solid #f8c6d7; padding: 12px 20px; border-radius: 14px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(92, 29, 55, 0.05);">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="font-size:1.4rem;">🛍️</span>
                        <div>
                            <span style="font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; color:#7a2b4a; font-weight:700; display:block;">Selected Items</span>
                            <span id="global-cart-items-summary" style="font-size:0.9rem; font-weight:600; color:#5c1d37;">No items selected</span>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <span style="font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; color:#7a2b4a; font-weight:700; display:block;">Estimated Total</span>
                        <strong id="global-cart-total-estimate" style="font-size:1.35rem; color:#e67399; font-weight:800;">$0</strong>
                    </div>
                </div>
            @endif

            <div class="order-content" id="form-container-toggle">
                @if($totalSteps === 0)
                    <div style="text-align:center; padding:50px 20px;">
                        <span style="font-size:3.5rem; display:block; margin-bottom:12px;">📋</span>
                        <h3 style="color:#5c1d37; margin-bottom:8px; font-size:1.4rem; font-weight:700;">Order Form Not Configured Yet</h3>
                        <p style="color:#666; font-size:0.95rem; max-width:420px; margin:0 auto 24px;">This bakery has not configured their custom order form steps yet. Please check back later or contact the baker directly!</p>
                        <button onclick="closeOrderModal()" class="btn btn-primary" style="padding:10px 26px; border-radius:20px;">Close</button>
                    </div>
                @endif

                @foreach($steps as $index => $step)
                    @php
                        $stepNum = $index + 1;
                        $isFirst = ($stepNum === 1);
                        $isLast = ($stepNum === $totalSteps);
                        $prevStepNum = $stepNum - 1;
                        $nextStepNum = $stepNum + 1;
                        $type = $step['type'] ?? 'text';
                        $title = $step['title'] ?? 'Order Step';
                        $subtext = $step['subtext'] ?? '';
                        $optionsStr = $step['options'] ?? '';
                        $description = $step['description'] ?? '';
                        $options = array_filter(array_map('trim', explode(',', $optionsStr)));
                    @endphp

                    <section class="step {{ $isFirst ? 'active' : '' }}" id="step-{{ $stepNum }}" data-step-type="{{ $type }}">
                        <h2>{{ $title }}</h2>
                        @if(!empty($subtext))
                            <p style="text-align:center; margin-bottom:18px; font-size:0.9rem; color:#666;">{{ $subtext }}</p>
                        @endif

                        @if($type === 'products')
                            <div id="product-grid">
                                @if(isset($products) && count($products) > 0)
                                    @foreach($products as $prod)
                                        <div class="product" data-name="{{ $prod->name }}" data-price="{{ $prod->price }}">
                                            <strong>{{ $prod->name }}</strong><br>${{ number_format($prod->price, 0) }}
                                        </div>
                                    @endforeach
                                @else
                                    <div style="grid-column: 1 / -1; text-align:center; padding:35px 15px; color:#666;">
                                        <span style="font-size:2.2rem; display:block; margin-bottom:8px;">🧁</span>
                                        <strong style="font-size:1.05rem; color:#5c1d37; display:block;">No Products Available</strong>
                                        <p style="font-size:0.9rem; margin-top:4px;">This bakery has not added any products to their order form yet.</p>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="cart-bar">
                                <div id="cart-items-list"></div>
                                <div id="cart-summary">Items: 0 <br> <strong>Total: $0</strong></div>
                                <div class="nav-buttons">
                                    <button class="next-btn" id="to-step-{{ $nextStepNum }}" disabled>Continue</button>
                                </div>
                            </div>

                        @elseif($type === 'calendar')
                            <div id="calendar-wrapper">
                                <div class="wpbs-main-wrapper wpbs-main-wrapper-calendar-1">
                                    <div class="wpbs-container wpbs-calendar-1">
                                        <div class="wpbs-legend" style="display:flex; justify-content:center; gap:20px; margin: 15px 0;">
                                            <div class="wpbs-legend-item" style="color: var(--dark-text); font-weight: 700;"><span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:var(--primary); margin-right:6px;"></span> Available</div>
                                            <div class="wpbs-legend-item" style="color: #888; font-weight: 600;"><span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#e2d4c7; margin-right:6px;"></span> Booked / Unavailable</div>
                                        </div>
                                        <div id="interactive-calendar-grid"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="cart-bar">
                                <div class="selected-date-display">
                                    Selected Date: <span id="selected-date" style="font-weight:700; color:var(--primary);">None</span>
                                </div>
                                <div class="nav-buttons">
                                    <button class="back-btn" id="back-step-{{ $prevStepNum }}">Back</button>
                                    <button class="next-btn" id="to-step-{{ $nextStepNum }}" disabled>Continue</button>
                                </div>
                            </div>

                        @elseif(in_array($type, ['flavors', 'frosting', 'fillings', 'chips']))
                            <div id="{{ $type === 'flavors' ? 'flavor-list' : ($type === 'frosting' ? 'frosting-list' : ($type === 'fillings' ? 'filling-list' : 'custom-chip-list-' . $stepNum)) }}" class="option-chip-grid">
                                @foreach($options as $opt)
                                    @php
                                        $optStr = trim($opt);
                                        $addonPrice = 0.00;
                                        $cleanName = $optStr;
                                        if (preg_match('/\(\+\$?([0-9]+(?:\.[0-9]{1,2})?)\)/i', $optStr, $matches)) {
                                            $addonPrice = (float) $matches[1];
                                            $cleanName = trim(preg_replace('/\(\+\$?([0-9]+(?:\.[0-9]{1,2})?)\)/i', '', $optStr));
                                        }
                                    @endphp
                                    <div class="product option-chip" data-name="{{ $optStr }}" data-clean-name="{{ $cleanName }}" data-addon-price="{{ $addonPrice }}" style="display:inline-flex; align-items:center; justify-content:space-between; gap:8px;">
                                        <span>{{ $cleanName }}</span>
                                        @if($addonPrice > 0)
                                            <span class="price-badge" style="font-size:0.75rem; font-weight:700; padding:3px 8px; border-radius:10px; flex-shrink:0;">+ ${{ number_format($addonPrice, 2) }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="nav-buttons cart-bar">
                                <button class="back-btn" id="back-step-{{ $prevStepNum }}">Back</button>
                                <button class="next-btn" id="to-step-{{ $nextStepNum }}">Continue</button>
                            </div>

                        @elseif($type === 'fulfillment')
                            <div id="fulfillment-grid" class="fulfillment-options" style="display:flex; justify-content:center; gap:20px; margin-bottom:20px; flex-wrap:wrap;">
                                @foreach($fulfillmentOptions as $foIndex => $foValue)
                                    <div class="product token-option {{ $foIndex === 0 ? 'active' : '' }}" data-value="{{ $foValue }}" style="padding:15px 30px; border-radius:12px; cursor:pointer;">
                                        <strong>{{ $fulfillmentLabels[$foValue] }}</strong>
                                    </div>
                                @endforeach
                            </div>

                            <div id="address-wrapper" class="hidden-field" style="display:none; margin-bottom:20px;">
                                <p style="text-align:left; margin-top: 15px; font-weight: 600; color: var(--dark-text);">Delivery Address</p>
                                <input type="text" id="delivery-address" placeholder="Street Address, City, State, ZIP" style="width:100%; padding:12px; border-radius:10px; border:1px solid #ccc;">
                            </div>

                            <div id="shipping-wrapper" class="hidden-field" style="display:none; margin-bottom:20px;">
                                <p style="text-align:left; margin-top: 15px; font-weight: 600; color: var(--dark-text);">Ship To State</p>
                                <select id="shipping-state" style="width:100%; padding:12px; border-radius:10px; border:1px solid #ccc; margin-bottom:12px;">
                                    <option value="">Select a state...</option>
                                    @foreach(($fulfillmentSettings['shipping_states'] ?? []) as $shipAbbr)
                                        <option value="{{ $shipAbbr }}">{{ $shipAbbr }}</option>
                                    @endforeach
                                </select>
                                <p style="text-align:left; font-weight: 600; color: var(--dark-text);">Shipping Address</p>
                                <input type="text" id="shipping-address" placeholder="Street Address, City, ZIP" style="width:100%; padding:12px; border-radius:10px; border:1px solid #ccc; margin-bottom:12px;">
                                <p style="text-align:left; font-size:0.9rem; color:var(--dark-text); font-weight:600;">
                                    @if(($fulfillmentSettings['shipping_rate_mode'] ?? 'flat') === 'flat')
                                        Shipping cost: ${{ number_format($fulfillmentSettings['shipping_flat_rate'] ?? 0, 2) }}
                                    @else
                                        Shipping cost: to be determined — we'll follow up with a quote.
                                    @endif
                                </p>
                            </div>

                            <div id="time-slot-wrapper">
                                <p style="text-align:left; margin-top: 20px; font-weight: 600; color: var(--dark-text);">Select Time Frame</p>
                                <div class="time-radio-grid" style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;">
                                    @php $timeSlots = !empty($options) ? $options : ['8:30 AM', '9:00 AM', '9:30 AM', '10:00 AM', '10:30 AM']; @endphp
                                    @foreach($timeSlots as $slotIndex => $slot)
                                        <label class="time-radio-card" style="background:#fff; border:1px solid #ddd; padding:8px 16px; border-radius:8px; cursor:pointer;">
                                            <input type="radio" name="order-time" value="{{ $slot }}" {{ $slotIndex === 2 ? 'checked' : '' }}> {{ $slot }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div class="nav-buttons cart-bar" style="margin-top:25px;">
                                <button class="back-btn" id="back-step-{{ $prevStepNum }}">Back</button>
                                <button class="next-btn" id="to-step-{{ $nextStepNum }}">Continue</button>
                            </div>

                        @elseif($type === 'social_discount')
                            <div id="social-grid">
                                @foreach($options as $opt)
                                    <div class="product">{{ $opt }}</div>
                                @endforeach
                            </div>
                            
                            <div class="nav-buttons cart-bar">
                                <button class="back-btn" id="back-step-{{ $prevStepNum }}">Back</button>
                                <button class="next-btn" id="to-step-{{ $nextStepNum }}">Continue</button>
                            </div>

                        @elseif($type === 'file_upload')
                            <input type="file" id="inspiration-upload" multiple accept="image/*" style="display: none;">

                            <div id="upload-container" class="upload-dropzone" style="padding:30px 20px; text-align:center; cursor:pointer;" onclick="document.getElementById('inspiration-upload').click();">
                                <div class="upload-prompt">
                                    <span class="upload-icon" style="font-size:2.5rem; color:var(--primary); display:block; margin-bottom:8px;">✦</span>
                                    <p style="font-size:1.05rem; font-weight:600; color:var(--dark-text); margin-bottom:4px;">Drag &amp; drop your images here or <strong>click to browse</strong></p>
                                    <span style="font-size: 12px; color: #888;">{{ !empty($description) ? $description : 'Supports PNG, JPG, JPEG' }}</span>
                                </div>
                            </div>

                            <div id="upload-count-status" style="margin-top:15px; font-weight:700; color:var(--primary); text-align:center; display:none;"></div>
                            <div id="preview-gallery" style="display:flex; gap:12px; flex-wrap:wrap; margin-top:15px; justify-content:center;"></div>

                            <div class="nav-buttons cart-bar" style="margin-top:25px;">
                                <button class="back-btn" id="back-step-{{ $prevStepNum }}">Back</button>
                                <button class="next-btn" id="to-step-{{ $nextStepNum }}">Continue</button>
                            </div>

                        @elseif($type === 'terms')
                            <div class="terms-scroll-box" style="height:180px; overflow-y:auto; border:1px solid #eee; padding:15px; border-radius:12px; background:#fafafa; font-size:0.85rem; line-height:1.6; margin-bottom:15px;">
                                @if(!empty($description))
                                    {!! $description !!}
                                @else
                                    <p style="color:#666; font-style:italic;">Please consult the bakery directly for their order policies and terms.</p>
                                @endif
                            </div>

                            <div class="terms-acceptance-wrapper" style="margin-bottom:15px;">
                                <label class="terms-checkbox-label" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                                    <input type="checkbox" id="terms-agree-checkbox">
                                    <span class="checkbox-text" style="font-weight:600; font-size:0.95rem; color:#4a2133;">I agree to the terms and conditions</span>
                                </label>
                            </div>

                            <div class="nav-buttons cart-bar">
                                <button class="back-btn" id="back-step-{{ $prevStepNum }}">Back</button>
                                <button class="next-btn" id="to-step-{{ $nextStepNum }}" disabled>Accept</button>
                            </div>

                        @elseif($type === 'contact_info')
                            <form id="order-form">
                                @csrf
                                <input type="text" id="contact-name" placeholder="Full Name" required style="width:100%; padding:12px; border-radius:10px; border:1px solid #ccc; margin-bottom:12px;">
                                <input type="email" id="contact-email" placeholder="Email" required style="width:100%; padding:12px; border-radius:10px; border:1px solid #ccc; margin-bottom:12px;">
                                
                                <div class="phone-input-container" style="display:flex; align-items:center; border:1px solid #ccc; border-radius:10px; overflow:hidden; margin-bottom:12px;">
                                    <span class="phone-prefix" style="background:#eee; padding:12px; font-weight:600; font-size:0.9rem;">+1 US 🇺🇸</span>
                                    <input type="tel" id="contact-phone" placeholder="Phone Number" required style="border:none; padding:12px; width:100%; outline:none;">
                                </div>

                                <input type="text" id="contact-coupon" placeholder="Coupon Code (optional)" style="width:100%; padding:12px; border-radius:10px; border:1px solid #ccc; margin-bottom:15px;">
                                
                                <div class="nav-buttons cart-bar">
                                    <button type="button" class="back-btn" id="back-step-{{ $prevStepNum }}">Back</button>
                                    <button type="submit" id="submit-form-btn" class="btn btn-primary" style="padding:12px 30px;">Submit Order</button>
                                </div>
                            </form>

                        @elseif($type === 'textarea' || $type === 'allergies')
                            <textarea id="{{ $type === 'allergies' ? 'order-allergies' : 'order-notes' }}" placeholder="{{ !empty($description) ? $description : 'Type here...' }}" style="width:100%; height:110px; padding:12px; border-radius:10px; border:1px solid #ccc; font-family:inherit;"></textarea>
                            
                            <div class="nav-buttons cart-bar" style="margin-top:20px;">
                                <button class="back-btn" id="back-step-{{ $prevStepNum }}">Back</button>
                                <button class="next-btn" id="to-step-{{ $nextStepNum }}">Continue</button>
                            </div>

                        @elseif($type === 'select')
                            <select class="custom-step-select" style="width:100%; padding:14px; border-radius:10px; border:1px solid #ccc; font-family:inherit; font-size:0.95rem;" onchange="updateCartSummary()">
                                <option value="" disabled selected>Select an option…</option>
                                @foreach($options as $opt)
                                    @php
                                        $optStr = trim($opt);
                                        $addonPrice = 0.00;
                                        $cleanName = $optStr;
                                        if (preg_match('/\(\+\$?([0-9]+(?:\.[0-9]{1,2})?)\)/i', $optStr, $matches)) {
                                            $addonPrice = (float) $matches[1];
                                            $cleanName = trim(preg_replace('/\(\+\$?([0-9]+(?:\.[0-9]{1,2})?)\)/i', '', $optStr));
                                        }
                                    @endphp
                                    <option value="{{ $optStr }}" data-addon-price="{{ $addonPrice }}" data-clean-name="{{ $cleanName }}">
                                        {{ $cleanName }} @if($addonPrice > 0) (+ ${{ number_format($addonPrice, 2) }}) @endif
                                    </option>
                                @endforeach
                            </select>

                            <div class="nav-buttons cart-bar" style="margin-top:20px;">
                                <button class="back-btn" id="back-step-{{ $prevStepNum }}">Back</button>
                                <button class="next-btn" id="to-step-{{ $nextStepNum }}">Continue</button>
                            </div>

                        @elseif($type === 'toggle')
                            <div style="display:flex; gap:14px; margin-top:8px; justify-content:center;">
                                <div class="product" style="cursor:pointer; padding:12px 32px; border-radius:50px; font-weight:700;" onclick="this.classList.add('selected'); this.nextElementSibling.classList.remove('selected')">Yes</div>
                                <div class="product" style="cursor:pointer; padding:12px 32px; border-radius:50px; font-weight:700;" onclick="this.classList.add('selected'); this.previousElementSibling.classList.remove('selected')">No</div>
                            </div>

                            <div class="nav-buttons cart-bar" style="margin-top:20px;">
                                <button class="back-btn" id="back-step-{{ $prevStepNum }}">Back</button>
                                <button class="next-btn" id="to-step-{{ $nextStepNum }}">Continue</button>
                            </div>

                        @else
                            <input type="text" placeholder="{{ !empty($description) ? $description : 'Type your answer here...' }}" style="width:100%; padding:14px; border-radius:10px; border:1px solid #ccc; font-family:inherit; font-size:0.95rem;">

                            <div class="nav-buttons cart-bar" style="margin-top:20px;">
                                <button class="back-btn" id="back-step-{{ $prevStepNum }}">Back</button>
                                <button class="next-btn" id="to-step-{{ $nextStepNum }}">Continue</button>
                            </div>
                        @endif
                    </section>
                @endforeach
            </div>

            <div id="thank-you-container" style="display:none; text-align:center; padding:40px 20px;">
                <span style="font-size:3.5rem;">🌸</span>
                <h2 style="font-family:'Great Vibes', cursive; font-size:3.2rem; margin:15px 0; color:#5c1d37;">Thank You For Your Order!</h2>
                <p style="font-size:1.05rem; color:#555;">Your order details have been successfully submitted to the baker. We will review your request and send your confirmation &amp; invoice shortly!</p>
                <button onclick="window.location.reload()" class="btn btn-primary" style="margin-top:25px; padding:12px 30px;">Place Another Order</button>
            </div>
        </div>
    </div>
</div>
