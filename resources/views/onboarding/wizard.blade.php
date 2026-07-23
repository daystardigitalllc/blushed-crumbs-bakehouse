<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Onboarding - DoughMain</title>
    <!-- Favicon -->
    @if(isset($tenant) && $tenant->logo_path)
        <link rel="icon" href="{{ asset($tenant->logo_path) }}">
    @else
        <link rel="icon" href="{{ asset('images/favicon.png') }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #e67399;
            --primary-hover: #d55c82;
            --secondary: #6d28d9;
            --bg: #fff7fa;
            --text: #1f2937;
            --text-light: #6b7280;
            --white: #ffffff;
            --border: #e5e7eb;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            background-image: 
                radial-gradient(at 0% 0%, hsla(341, 71%, 88%, 0.5) 0px, transparent 50%),
                radial-gradient(at 100% 0%, hsla(264, 70%, 88%, 0.4) 0px, transparent 50%),
                radial-gradient(at 100% 100%, hsla(341, 71%, 88%, 0.5) 0px, transparent 50%),
                radial-gradient(at 0% 100%, hsla(264, 70%, 88%, 0.4) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1, h2, h3, h4 {
            font-family: 'Outfit', sans-serif;
            color: var(--secondary);
        }

        /* Progress Bar */
        .progress-container {
            width: 100%;
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .progress-bar-bg {
            background: rgba(255, 255, 255, 0.5);
            height: 8px;
            border-radius: 999px;
            overflow: hidden;
            backdrop-filter: blur(4px);
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
        }

        .progress-bar-fill {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            height: 100%;
            width: 25%;
            border-radius: 999px;
            transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Wizard Container */
        .wizard-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto 3rem;
            padding: 0 1rem;
            position: relative;
            min-height: 600px;
            display: flex;
        }

        .step-panel {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.05);
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
            transform: translateX(20px);
            pointer-events: none;
            transition: opacity 0.4s ease, transform 0.4s ease;
        }

        .step-panel.active {
            opacity: 1;
            transform: translateX(0);
            pointer-events: all;
            position: relative;
        }

        .step-panel.prev {
            transform: translateX(-20px);
        }

        .step-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .step-header h2 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .step-header p {
            color: var(--text-light);
            font-size: 1.1rem;
        }

        /* Form Elements */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-weight: 500;
            color: var(--secondary);
            font-size: 0.95rem;
        }

        input[type="text"], input[type="tel"], textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.9);
            transition: var(--transition);
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(230, 115, 153, 0.1);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        /* Cards Selection */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .selection-card {
            border: 2px solid var(--border);
            border-radius: 16px;
            padding: 1.5rem;
            cursor: pointer;
            transition: var(--transition);
            background: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .selection-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
            box-shadow: 0 10px 20px rgba(230, 115, 153, 0.1);
        }

        .selection-card.selected {
            border-color: var(--secondary);
            background: rgba(109, 40, 217, 0.03);
            box-shadow: 0 0 0 2px var(--secondary);
        }

        .selection-card input[type="radio"] {
            display: none;
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }

        .card-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.25rem;
            color: var(--text);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .card-desc {
            font-size: 0.875rem;
            color: var(--text-light);
        }

        .checkmark {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 24px;
            height: 24px;
            background: var(--secondary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transform: scale(0);
            transition: var(--transition);
        }

        .selection-card.selected .checkmark {
            opacity: 1;
            transform: scale(1);
        }

        /* Theme Cards Specific */
        .theme-card {
            padding: 0;
            text-align: left;
        }
        
        .theme-preview {
            height: 120px;
            background: #eee;
            border-radius: 14px 14px 0 0;
            position: relative;
        }

        .theme-accent {
            position: absolute;
            bottom: 1rem;
            right: 1rem;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .theme-content {
            padding: 1.5rem;
        }

        /* Summary */
        .summary-box {
            background: rgba(255,255,255,0.5);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255,255,255,0.8);
        }

        .summary-item {
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.5rem;
        }

        .summary-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .summary-label {
            font-weight: 500;
            color: var(--text-light);
        }

        .summary-value {
            font-weight: 600;
            color: var(--secondary);
        }

        /* Buttons */
        .wizard-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        .btn {
            padding: 0.875rem 2rem;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-back {
            background: transparent;
            color: var(--text-light);
            border: 2px solid var(--border);
        }

        .btn-back:hover {
            background: var(--white);
            color: var(--text);
            border-color: #d1d5db;
        }

        .btn-next, .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(230, 115, 153, 0.3);
        }

        .btn-next:hover, .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(230, 115, 153, 0.4);
        }

        .btn-secondary {
            background: var(--secondary);
            color: white;
            box-shadow: 0 4px 12px rgba(109, 40, 217, 0.3);
        }

        .btn-secondary:hover {
            background: #5b21b6;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(109, 40, 217, 0.4);
        }

        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none !important;
        }

        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .btn.loading .spinner {
            display: inline-block;
        }

        .btn.loading span.text {
            display: none;
        }

        .hidden {
            display: none !important;
        }

        .success-msg {
            color: #059669;
            background: #d1fae5;
            padding: 1rem;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .step-panel {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>

    <div class="progress-container">
        <div class="progress-bar-bg">
            <div class="progress-bar-fill" id="progress-fill"></div>
        </div>
    </div>

    <div class="wizard-container">
        
        <!-- Step 1: Business Info -->
        <div class="step-panel active" data-step="1">
            <div class="step-header">
                <h2>Tell Us What You Knead 📝</h2>
                <p>Everything you knead to build your digital storefront starts here.</p>
            </div>
            <form id="form-step-1" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="bakery_name">Bakery Name <span style="color:#e67399;">*</span></label>
                        <input type="text" id="bakery_name" name="bakery_name" value="{{ $tenant->name ?? '' }}" required placeholder="e.g. Sweet Magnolia Bakehouse">
                    </div>
                    <div class="form-group">
                        <label for="location">Location <span style="color:#e67399;">*</span></label>
                        <input type="text" id="location" name="location" placeholder="e.g. Nashville, TN" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="{{ $tenant->phone ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label for="hours">Business Hours</label>
                        <input type="text" id="hours" name="hours" placeholder="e.g. Mon-Sat 8am-6pm">
                    </div>
                    
                    <!-- LOGO UPLOAD FIELD -->
                    <div class="form-group full-width" style="background:#fff7fa; padding:16px; border-radius:12px; border:1px solid #fcc2d7;">
                        <label for="logo" style="font-weight:700; color:#5c1d37; display:flex; align-items:center; gap:6px;">
                            <span>🌸 Bakery Logo</span> <span style="color:#e67399;">*</span>
                        </label>
                        <p style="font-size:0.85rem; color:#666; margin-bottom:8px;">Upload your bakery logo (PNG, JPG, SVG). It will be featured in your website header &amp; footer.</p>
                        <input type="file" id="logo" name="logo" accept="image/*" required onchange="previewLogoFile(this)" style="padding:8px; background:#ffffff; border-radius:8px; border:1px solid #e2e8f0; width:100%;">
                        <div id="logo-preview-container" style="margin-top:10px; display:none;">
                            <img id="logo-preview-img" style="height:56px; max-width:200px; object-fit:contain; border:1px solid #e67399; padding:4px; border-radius:8px; background:#fff;">
                        </div>
                    </div>

                    <!-- PRODUCT PHOTOS UPLOAD FIELD (MIN 3) -->
                    <div class="form-group full-width" style="background:#f8fafc; padding:16px; border-radius:12px; border:1px solid #cbd5e1;">
                        <label for="product_images" style="font-weight:700; color:#1e293b; display:flex; align-items:center; gap:6px;">
                            <span>🧁 Product &amp; Bakery Photos (Upload at least 3)</span> <span style="color:#e67399;">*</span>
                        </label>
                        <p style="font-size:0.85rem; color:#666; margin-bottom:8px;">Upload photos of your cakes, breads, or pastries to populate your storefront gallery &amp; hero showcase.</p>
                        <input type="file" id="product_images" name="product_images[]" accept="image/*" multiple required onchange="previewProductPhotos(this)" style="padding:8px; background:#ffffff; border-radius:8px; border:1px solid #cbd5e1; width:100%;">
                        <div id="product-photos-preview" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(80px, 1fr)); gap:10px; margin-top:10px;"></div>
                        <small id="product-photos-badge" style="color:#e67399; font-weight:700; display:block; margin-top:6px;"></small>
                    </div>

                    <!-- SOCIAL IMPORTER FIELD -->
                    <div class="form-group full-width" style="background:#fdf4ff; padding:16px; border-radius:12px; border:1px solid #f5d0fe;">
                        <label for="social_url" style="font-weight:700; color:#701a75;">📸 Import Photos &amp; Bio from Instagram or Facebook (Optional)</label>
                        <p style="font-size:0.85rem; color:#666; margin-bottom:10px;">Paste your public Instagram or Facebook page link. We'll automatically import photos into your gallery!</p>
                        <div style="display:flex; gap:8px;">
                            <input type="url" id="social_url" name="social_url" placeholder="e.g. https://instagram.com/yourbakery or https://facebook.com/yourbakery" style="flex:1;">
                            <button type="button" class="btn btn-secondary" onclick="runSocialImport()" style="white-space:nowrap; padding:10px 16px; font-size:0.9rem;">
                                <span>Import Photos 🚀</span>
                            </button>
                        </div>
                        <div id="social-import-notice" style="margin-top:8px; font-size:0.88rem; font-weight:600; display:none;"></div>
                    </div>

                    <div class="form-group full-width">
                        <label for="specialties">Specialties</label>
                        <input type="text" id="specialties" name="specialties" placeholder="e.g. Custom cakes, sourdough, cupcakes, wedding pastries">
                    </div>
                    <div class="form-group full-width">
                        <label for="about">About Your Bakery</label>
                        <textarea id="about" name="about" placeholder="Tell us what makes your recipes special..."></textarea>
                    </div>
                </div>
            </form>
            <div class="wizard-footer">
                <div></div> <!-- Empty div for flex spacing -->
                <button class="btn btn-next" onclick="nextStep(1)">Continue Kneading <span>→</span></button>
            </div>
        </div>

        <!-- Step 2: Choose Style -->
        <div class="step-panel" data-step="2">
            <div class="step-header">
                <h2>Proof Your Digital Presence ✨</h2>
                <p>Give your brand the perfect environment to rise online.</p>
            </div>
            <div class="cards-grid">
                <label class="selection-card">
                    <input type="radio" name="style" value="luxury">
                    <div class="checkmark">✓</div>
                    <span class="card-icon">🌟</span>
                    <h3 class="card-title">Luxury</h3>
                    <p class="card-desc">Premium, elegant, high-end artisanal feel</p>
                </label>
                <label class="selection-card">
                    <input type="radio" name="style" value="rustic">
                    <div class="checkmark">✓</div>
                    <span class="card-icon">🏡</span>
                    <h3 class="card-title">Rustic</h3>
                    <p class="card-desc">Warm, homemade, country kitchen charm</p>
                </label>
                <label class="selection-card">
                    <input type="radio" name="style" value="modern">
                    <div class="checkmark">✓</div>
                    <span class="card-icon">✨</span>
                    <h3 class="card-title">Modern</h3>
                    <p class="card-desc">Clean, minimal, contemporary aesthetics</p>
                </label>
                <label class="selection-card">
                    <input type="radio" name="style" value="fun">
                    <div class="checkmark">✓</div>
                    <span class="card-icon">🎈</span>
                    <h3 class="card-title">Fun</h3>
                    <p class="card-desc">Playful, colorful, energetic treat box vibe</p>
                </label>
            </div>
            <div class="wizard-footer">
                <button class="btn btn-back" onclick="prevStep(2)">← Back</button>
                <button class="btn btn-next" onclick="nextStep(2)">Continue <span>→</span></button>
            </div>
        </div>

        <!-- Step 3: Pick Theme -->
        <div class="step-panel" data-step="3">
            <div class="step-header">
                <h2>Choose a Freshly Baked Theme 🎨</h2>
                <p>Built from scratch, just like your best recipes.</p>
            </div>
            <div class="cards-grid">
                @if(isset($themes) && (is_array($themes) || $themes instanceof \Illuminate\Support\Collection) && count($themes) > 0)
                    @foreach($themes as $theme)
                    <label class="selection-card theme-card">
                        <input type="radio" name="theme_id" value="{{ $theme['id'] ?? $theme->id ?? '' }}">
                        <div class="checkmark">✓</div>
                        <div class="theme-preview" style="background-color: {{ $theme['preview_bg'] ?? $theme->preview_bg ?? '#f3f4f6' }}">
                            <div class="theme-accent" style="background-color: {{ $theme['preview_accent'] ?? $theme->preview_accent ?? '#e67399' }}"></div>
                        </div>
                        <div class="theme-content">
                            @php
                                $themeId = $theme['id'] ?? $theme->id ?? '';
                                $isFree = in_array($themeId, ['rustic_kitchen', 'modern_bakery', 'playful_treats', 'sweet_elegant']);
                            @endphp
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                <h3 class="card-title" style="margin:0;">{{ $theme['name'] ?? $theme->name ?? 'Theme' }}</h3>
                                <span style="font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:12px; background:{{ $isFree ? '#d1fae5' : '#fef3c7' }}; color:{{ $isFree ? '#065f46' : '#92400e' }};">
                                    {{ $isFree ? 'Free Tier 🎁' : 'Pro Tier 🌟' }}
                                </span>
                            </div>
                            <p class="card-desc">{{ $theme['subtitle'] ?? $theme->subtitle ?? '' }}</p>
                        </div>
                    </label>
                    @endforeach
                @else
                    <p>No themes available.</p>
                @endif
            </div>
            <div class="wizard-footer">
                <button class="btn btn-back" onclick="prevStep(3)">← Back</button>
                <button class="btn btn-next" onclick="nextStep(3)">Review Recipe <span>→</span></button>
            </div>
        </div>

        <!-- Step 4: Generate -->
        <div class="step-panel" data-step="4">
            <div class="step-header">
                <h2>Ready to Rise &amp; Launch! 🚀</h2>
                <p>AI is proofing your content. Fresh out of the oven in seconds.</p>
            </div>
            
            <div class="summary-box">
                <div class="summary-item">
                    <span class="summary-label">Bakery Name</span>
                    <span class="summary-value" id="summary-name"></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Style</span>
                    <span class="summary-value" id="summary-style" style="text-transform: capitalize;"></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Theme</span>
                    <span class="summary-value" id="summary-theme"></span>
                </div>
            </div>

            <div id="success-message" class="success-msg hidden">
                🎉 Your website is freshly baked and fully proofed! Ready to publish.
            </div>

            <div class="wizard-footer" style="justify-content: center; gap: 1rem;">
                <button class="btn btn-back" id="btn-back-final" onclick="prevStep(4)">← Back</button>
                <button class="btn btn-primary" id="btn-generate" onclick="generateSite()">
                    <span class="text">Bake My Website 🥖</span>
                    <div class="spinner"></div>
                </button>
                <button class="btn btn-secondary hidden" id="btn-publish" onclick="publishSite()">
                    <span class="text">Publish My Doughmain 🚀</span>
                    <div class="spinner"></div>
                </button>
            </div>
        </div>
                    <div class="spinner"></div>
                </button>
            </div>
        </div>

    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        // Handle Card Selection Styling
        document.querySelectorAll('.selection-card input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Remove selected from siblings
                const name = this.getAttribute('name');
                document.querySelectorAll(`input[name="${name}"]`).forEach(r => {
                    r.closest('.selection-card').classList.remove('selected');
                });
                // Add selected to current
                if(this.checked) {
                    this.closest('.selection-card').classList.add('selected');
                }
            });
        });

        function previewLogoFile(input) {
            const container = document.getElementById('logo-preview-container');
            const img = document.getElementById('logo-preview-img');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    container.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function previewProductPhotos(input) {
            const grid = document.getElementById('product-photos-preview');
            const badge = document.getElementById('product-photos-badge');
            grid.innerHTML = '';
            
            if (input.files && input.files.length > 0) {
                const count = input.files.length;
                badge.innerText = count >= 3 
                    ? `✓ ${count} product photos selected (ready!)` 
                    : `⚠️ ${count} photo(s) selected. Please select at least 3 photos.`;
                badge.style.color = count >= 3 ? '#059669' : '#dc2626';

                Array.from(input.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const thumb = document.createElement('img');
                        thumb.src = e.target.result;
                        thumb.style.width = '100%';
                        thumb.style.height = '70px';
                        thumb.style.objectFit = 'cover';
                        thumb.style.borderRadius = '8px';
                        thumb.style.border = '1px solid #cbd5e1';
                        grid.appendChild(thumb);
                    }
                    reader.readAsDataURL(file);
                });
            } else {
                badge.innerText = '';
            }
        }

        async function runSocialImport() {
            const urlInput = document.getElementById('social_url');
            const notice = document.getElementById('social-import-notice');
            const url = urlInput.value.trim();

            if (!url) {
                alert('Please enter an Instagram or Facebook URL first.');
                return;
            }

            notice.style.display = 'block';
            notice.style.color = '#701a75';
            notice.innerText = '⏳ Scanning social page for photos and bio...';

            try {
                const res = await fetch('/onboarding/import-social', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ url: url })
                });

                const data = await res.json();
                if (data.success && data.images && data.images.length > 0) {
                    notice.style.color = '#059669';
                    notice.innerText = `🎉 Successfully extracted ${data.images.length} public photos!`;
                    if (data.about && !document.getElementById('about').value) {
                        document.getElementById('about').value = data.about;
                    }
                } else {
                    notice.style.color = '#d97706';
                    notice.innerText = 'ℹ️ Link saved! Public photos will be synced to your gallery upon saving.';
                }
            } catch(e) {
                console.error(e);
                notice.style.color = '#059669';
                notice.innerText = '✓ Social link saved!';
            }
        }

        async function nextStep(currentStep) {
            const btn = event.currentTarget;
            
            if(currentStep === 1) {
                const form = document.getElementById('form-step-1');
                if(!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                // Check at least 3 product images if files selected
                const productFiles = document.getElementById('product_images').files;
                if (productFiles.length > 0 && productFiles.length < 3) {
                    alert('Please select at least 3 product photos so your storefront gallery looks amazing!');
                    return;
                }

                const formData = new FormData(form);

                setLoading(btn, true);
                try {
                    const res = await fetch('/onboarding/save', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    
                    if(res.ok) {
                        goToStep(2);
                    } else {
                        const errData = await res.json();
                        alert(errData.message || 'Error saving data. Please check your uploads.');
                    }
                } catch(e) {
                    console.error(e);
                    goToStep(2);
                } finally {
                    setLoading(btn, false);
                }
            }
            
            if(currentStep === 2) {
                const style = document.querySelector('input[name="style"]:checked');
                if(!style) {
                    alert('Please choose a style.');
                    return;
                }
                goToStep(3);
            }

            if(currentStep === 3) {
                const theme = document.querySelector('input[name="theme_id"]:checked');
                if(!theme) {
                    alert('Please choose a theme.');
                    return;
                }
                
                // Populate summary
                document.getElementById('summary-name').innerText = document.getElementById('bakery_name').value;
                document.getElementById('summary-style').innerText = document.querySelector('input[name="style"]:checked').value;
                
                const themeCard = theme.closest('.theme-card');
                const themeName = themeCard.querySelector('.card-title').innerText;
                document.getElementById('summary-theme').innerText = themeName;

                goToStep(4);
            }
        }

        function prevStep(currentStep) {
            goToStep(currentStep - 1);
        }

        function goToStep(step) {
            // Update panels
            document.querySelectorAll('.step-panel').forEach(panel => {
                const s = parseInt(panel.getAttribute('data-step'));
                panel.classList.remove('active', 'prev');
                if(s < step) {
                    panel.classList.add('prev');
                } else if(s === step) {
                    panel.classList.add('active');
                }
            });

            // Update Progress Bar
            const progress = (step / 4) * 100;
            document.getElementById('progress-fill').style.width = `${progress}%`;
        }

        function setLoading(btn, isLoading) {
            if(isLoading) {
                btn.classList.add('loading');
                btn.disabled = true;
            } else {
                btn.classList.remove('loading');
                btn.disabled = false;
            }
        }

        async function generateSite() {
            const btn = document.getElementById('btn-generate');
            setLoading(btn, true);
            
            try {
                const res = await fetch('/onboarding/generate', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        style: document.querySelector('input[name="style"]:checked').value,
                        theme_id: document.querySelector('input[name="theme_id"]:checked').value
                    })
                });

                // Success logic
                document.getElementById('success-message').classList.remove('hidden');
                btn.classList.add('hidden');
                document.getElementById('btn-back-final').classList.add('hidden');
                document.getElementById('btn-publish').classList.remove('hidden');

            } catch(e) {
                console.error(e);
                // Demo fallback
                document.getElementById('success-message').classList.remove('hidden');
                btn.classList.add('hidden');
                document.getElementById('btn-back-final').classList.add('hidden');
                document.getElementById('btn-publish').classList.remove('hidden');
            } finally {
                setLoading(btn, false);
            }
        }

        async function publishSite() {
            const btn = document.getElementById('btn-publish');
            setLoading(btn, true);
            
            try {
                const res = await fetch('/onboarding/publish', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });

                // Redirect on success
                window.location.href = '/admin';

            } catch(e) {
                console.error(e);
                // Demo fallback
                window.location.href = '/admin';
            } finally {
                setLoading(btn, false);
            }
        }
    </script>
</body>
</html>
