@extends('layouts.app')

@section('title', 'Blushed Crumbs Bakehouse | Where Every Celebration Gets Its Sweet Ending')

@section('content')
<!-- Header & Navigation -->
<header class="site-header">
    <div class="header-container">
        <a href="#home" class="logo">
            <span class="logo-icon">🌸</span> Blushed Crumbs
        </a>
        <nav class="nav-links">
            <a href="#home">Home</a>
            <a href="#categories">Menu</a>
            <a href="#order-form-section">Custom Order</a>
            <a href="#gallery">Gallery</a>
            <a href="#reviews">Reviews</a>
            <a href="/admin" class="admin-btn">🔑 Baker Admin Portal</a>
        </nav>
    </div>
</header>

<!-- Hero Section -->
<section id="home" class="hero-section">
    <div class="cloud-divider cloud-top"></div>
    <div class="hero-content">
        <span class="subheading">Welcome to Blushed Crumbs Bakehouse</span>
        <h1>Where Every Celebration Gets Its Sweet Ending.</h1>
        <div class="hero-buttons">
            <a href="#order-form-section" class="btn btn-primary">Custom Order</a>
            <a href="#categories" class="btn btn-secondary">Explore Menu</a>
        </div>
    </div>
    <div class="cloud-divider cloud-bottom"></div>
</section>

<!-- Highlights Banner -->
<section class="highlights-bar">
    <div class="highlight-item">
        <span class="icon">🎂</span>
        <h4>Easy Catering</h4>
        <p>Custom baked goods for any occasion</p>
    </div>
    <div class="highlight-item">
        <span class="icon">🚚</span>
        <h4>Freshly Baked</h4>
        <p>Made to order right before your event</p>
    </div>
    <div class="highlight-item">
        <span class="icon">📦</span>
        <h4>Local Delivery</h4>
        <p>Flexible pickup & delivery options</p>
    </div>
    <div class="highlight-item">
        <span class="icon">💖</span>
        <h4>Baked with Love</h4>
        <p>Cottage bakery crafted with care</p>
    </div>
</section>

<!-- Special Promo Banner -->
<section class="promo-banner">
    <h2>$10 Off Your First Order!</h2>
    <p>Follow us on social media or join our community for instant discounts on custom cakes & treats.</p>
    <a href="#order-form-section" class="btn btn-dark">Order Now</a>
</section>

<!-- Product Categories Grid -->
<section id="categories" class="categories-section">
    <h2 class="section-title">Our Categories</h2>
    <div class="categories-grid">
        <div class="category-card">
            <img src="https://images.unsplash.com/photo-1535141192574-5d4897c13136?auto=format&fit=crop&w=600&q=80" alt="Single Tier Cakes">
            <h3>Single Tier Cakes</h3>
        </div>
        <div class="category-card">
            <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=600&q=80" alt="Multi Tier Cakes">
            <h3>Multi Tier Cakes</h3>
        </div>
        <div class="category-card">
            <img src="https://images.unsplash.com/photo-1587314168485-3236d6710814?auto=format&fit=crop&w=600&q=80" alt="By The Dozen">
            <h3>By The Dozen</h3>
        </div>
    </div>
</section>

<!-- Whimsical Creations Showcase -->
<section class="feature-section">
    <div class="feature-container">
        <div class="feature-img">
            <img src="https://images.unsplash.com/photo-1621303837174-89787a7d4729?auto=format&fit=crop&w=600&q=80" alt="Whimsical Cake Creation">
        </div>
        <div class="feature-text">
            <h2>Whimsical Creations for Every Milestone</h2>
            <ul>
                <li><strong>Custom Wedding Cakes:</strong> Elegant 2 & 3 tiered creations tailored to your theme.</li>
                <li><strong>Birthday & Theme Cakes:</strong> Fun children's and adult milestone designs.</li>
                <li><strong>Anniversary & Party Packs:</strong> Treat packages featuring cupcakes, cake shooters, and cake sickles.</li>
                <li><strong>Gourmet Flavors:</strong> Premium luxury options including Almond Elegance, Pistachio Ice, and Cookie Butter.</li>
            </ul>
        </div>
    </div>
</section>

<!-- 12-Step Cake Order Builder Section -->
<section id="order-form-section" class="order-section">
    <div class="ast-container">
        <div id="cake-order-builder">
            <div class="order-content" id="form-container-toggle">
                
                <!-- Step 1: Products -->
                <section class="step active" id="step-1">
                    <h2>Build Your Order</h2>
                    <div id="product-grid">
                        @foreach($products as $product)
                            <div class="product" data-name="{{ $product->name }}" data-price="{{ $product->price }}">
                                <strong>{{ $product->name }}</strong><br>${{ number_format($product->price, 0) }}
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="cart-bar">
                        <div id="cart-items-list">No items selected</div>
                        <div id="cart-summary">Items: 0 <br> <strong>Total: $0</strong></div>
                        <div class="nav-buttons">
                            <button class="next-btn" id="to-step-2" disabled>Continue</button>
                        </div>
                    </div>
                </section>

                <!-- Step 2: Date Booking Calendar -->
                <section class="step" id="step-2">
                    <h2>Select Your Date</h2>
                    <div id="calendar-wrapper">
                        <div class="wpbs-main-wrapper wpbs-main-wrapper-calendar-1">
                            <div class="wpbs-container wpbs-calendar-1">
                                <h2>Custom Order Booking</h2>
                                <div class="wpbs-legend">
                                    <div class="wpbs-legend-item"><span class="legend-box legend-available"></span> Available</div>
                                    <div class="wpbs-legend-item"><span class="legend-box legend-booked"></span> Booked</div>
                                </div>
                                <div class="calendar-controls">
                                    <select id="calendar-month-select">
                                        <option value="2026-07">July 2026</option>
                                        <option value="2026-08">August 2026</option>
                                        <option value="2026-09">September 2026</option>
                                        <option value="2026-10">October 2026</option>
                                    </select>
                                </div>
                                <div id="interactive-calendar-grid"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="cart-bar">
                        <div class="selected-date-display">
                            Selected Date: <span id="selected-date">None</span>
                        </div>
                        <div class="nav-buttons">
                            <button class="back-btn" id="back-step-1">Back</button>
                            <button class="next-btn" id="to-step-3" disabled>Continue</button>
                        </div>
                    </div>
                </section>

                <!-- Step 3: Choose Flavors -->
                <section class="step" id="step-3">
                    <h2>Choose Your Flavors</h2>
                    <p style="text-align:center;">Select all that apply (Luxury flavors +$10)</p>
                    <div id="flavor-list">
                        <div class="product" data-name="Strawberry Bliss">Strawberry Bliss</div>
                        <div class="product" data-name="Vanilla Bean">Vanilla Bean</div>
                        <div class="product" data-name="Chocolate Dream">Chocolate Dream</div>
                        <div class="product" data-name="Creamy Hazelnut">Creamy Hazelnut</div>
                        <div class="product" data-name="Confetti Explosion">Confetti Explosion</div>
                        <div class="product" data-name="Red Velvet">Red Velvet</div>
                        <div class="product" data-name="Swirly Marble">Swirly Marble</div>
                        <div class="product" data-name="Lemon Drop">Lemon Drop</div>
                        <div class="product" data-name="Golden Carrot">Golden Carrot</div>
                        <div class="product" data-name="Raspberry Swirl">Raspberry Swirl</div>
                        <div class="product lux" data-name="Almond Elegance (LUX)">Almond Elegance (LUX)</div>
                        <div class="product lux" data-name="Pistachio ice (LUX)">Pistachio ice (LUX)</div>
                        <div class="product lux" data-name="Coconut Cream (LUX)">Coconut Cream (LUX)</div>
                        <div class="product lux" data-name="Cinnamon spice (LUX)">Cinnamon spice (LUX)</div>
                        <div class="product lux" data-name="Cotton candy temptation (LUX)">Cotton candy temptation (LUX)</div>
                        <div class="product lux" data-name="Espresso Bean (LUX)">Espresso Bean (LUX)</div>
                        <div class="product lux" data-name="Cherry Bark (LUX)">Cherry Bark (LUX)</div>
                        <div class="product lux" data-name="Cookie Butter (LUX)">Cookie Butter (LUX)</div>
                        <div class="product lux" data-name="Tres Leches (LUX)">Tres Leches (LUX)</div>
                        <div class="product lux" data-name="Banana Creme Pie (LUX)">Banana Creme Pie (LUX)</div>
                    </div>
                    
                    <div class="nav-buttons cart-bar">
                        <button class="back-btn" id="back-step-2">Back</button>
                        <button class="next-btn" id="to-step-4">Continue</button>
                    </div>
                </section>

                <!-- Step 4: Frosting -->
                <section class="step" id="step-4">
                    <h2>Select Frosting</h2>
                    <div id="frosting-list">
                        <div class="product" data-name="Vanilla buttercream">Vanilla buttercream</div>
                        <div class="product" data-name="Cream Cheese">Cream Cheese</div>
                        <div class="product" data-name="Strawberry buttercream">Strawberry buttercream</div>
                        <div class="product" data-name="Oreo buttercream">Oreo buttercream</div>
                        <div class="product" data-name="Chocolate buttercream">Chocolate buttercream</div>
                        <div class="product" data-name="Confetti buttercream">Confetti buttercream</div>
                        <div class="product" data-name="Whip Cream">Whip Cream</div>
                    </div>
                    
                    <div class="nav-buttons cart-bar">
                        <button class="back-btn" id="back-step-3">Back</button>
                        <button class="next-btn" id="to-step-5">Continue</button>
                    </div>
                </section>

                <!-- Step 5: Fillings -->
                <section class="step" id="step-5">
                    <h2>Choice of Fillings?</h2>
                    <p style="text-align:center;">Select all that apply</p>
                    <div id="filling-list">
                        <div class="product" data-name="Fudge">Fudge</div>
                        <div class="product" data-name="Cookies and cream">Cookies and cream</div>
                        <div class="product" data-name="Strawberries and cream">Strawberries and cream</div>
                        <div class="product" data-name="Peanut Butter">Peanut Butter</div>
                        <div class="product" data-name="Nutella">Nutella</div>
                        <div class="product" data-name="Edible cookie dough">Edible cookie dough</div>
                        <div class="product" data-name="Lemon curd">Lemon curd</div>
                        <div class="product" data-name="Plain buttercream/frosting">Plain buttercream/frosting (no additional cost)</div>
                        <div class="product" data-name="Raspberry">Raspberry</div>
                        <div class="product" data-name="Cheesecake">Cheesecake</div>
                        <div class="product" data-name="Dulce de Leche">Dulce de Leche</div>
                        <div class="product" data-name="Pineapple">Pineapple</div>
                        <div class="product" data-name="Cream cheese">Cream cheese</div>
                        <div class="product" data-name="Banana">Banana</div>
                    </div>
                    
                    <div class="nav-buttons cart-bar">
                        <button class="back-btn" id="back-step-4">Back</button>
                        <button class="next-btn" id="to-step-6">Continue</button>
                    </div>
                </section>

                <!-- Step 6: Special Requests -->
                <section class="step" id="step-6">
                    <h2>Special Requests</h2>
                    <p style="text-align:center; margin-bottom: 20px;">Is there anything specific you want me to add or know about your order?</p>
                    
                    <textarea id="order-notes" placeholder="Type your notes here..."></textarea>
                    
                    <div class="nav-buttons cart-bar">
                        <button class="back-btn" id="back-step-5">Back</button>
                        <button class="next-btn" id="to-step-7">Continue</button>
                    </div>
                </section>

                <!-- Step 7: Fulfillment Options -->
                <section class="step" id="step-7">
                    <h2>Fulfillment Options</h2>
                    
                    <div id="fulfillment-grid" class="fulfillment-options">
                        <div class="product token-option active" data-value="pickup">
                            <strong>Pickup</strong>
                        </div>
                        <div class="product token-option" data-value="delivery">
                            <strong>Delivery</strong>
                        </div>
                    </div>

                    <div id="address-wrapper" class="hidden-field" style="display:none;">
                        <p style="text-align:left; margin-top: 20px; font-weight: 600; color: #b35978;">Delivery Address</p>
                        <input type="text" id="delivery-address" placeholder="Street Address, City, State, ZIP">
                    </div>

                    <div id="time-slot-wrapper">
                        <p style="text-align:left; margin-top: 25px; font-weight: 600; color: #b35978;">Select Time Frame</p>
                        <div class="time-radio-grid">
                            <label class="time-radio-card"><input type="radio" name="order-time" value="8:30" checked><span>8:30 AM</span></label>
                            <label class="time-radio-card"><input type="radio" name="order-time" value="9:00"><span>9:00 AM</span></label>
                            <label class="time-radio-card"><input type="radio" name="order-time" value="9:30"><span>9:30 AM</span></label>
                            <label class="time-radio-card"><input type="radio" name="order-time" value="10:00"><span>10:00 AM</span></label>
                            <label class="time-radio-card"><input type="radio" name="order-time" value="10:30"><span>10:30 AM</span></label>
                        </div>
                    </div>
                    
                    <div class="nav-buttons cart-bar">
                        <button class="back-btn" id="back-step-6">Back</button>
                        <button class="next-btn" id="to-step-8">Continue</button>
                    </div>
                </section>

                <!-- Step 8: Allergies -->
                <section class="step" id="step-8">
                    <h2>Any Allergies?</h2>
                    <p style="text-align:center; max-width: 600px; margin: 0 auto 20px auto; font-size: 14px; line-height: 1.5; color: #888;">
                        By answering this question you understand any allergies not listed I will not be at fault for.
                    </p>
                    
                    <textarea id="order-allergies" placeholder="List any allergies here (or type 'None')..."></textarea>
                    
                    <div class="nav-buttons cart-bar">
                        <button class="back-btn" id="back-step-7">Back</button>
                        <button class="next-btn" id="to-step-9">Continue</button>
                    </div>
                </section>

                <!-- Step 9: Social Media Discounts -->
                <section class="step" id="step-9">
                    <h2>Social Media Follows</h2>
                    <p style="text-align:center; max-width: 600px; margin: 0 auto 25px auto;">
                        <strong>$5 off</strong> for social media you follow or join!<br>
                        <span style="font-size: 13px; color: #888;">(Does not apply to the ones you are already following. Select all that apply.)</span>
                    </p>
                    
                    <div id="social-grid">
                        <div class="product" data-discount="5">Instagram: (@Blushed_Crumbs)</div>
                        <div class="product" data-discount="5">Facebook group: (Blushed Crumbs)</div>
                        <div class="product" data-discount="5">TikTok: (@Blushed_Crumbs)</div>
                        <div class="product" data-discount="5">Facebook page: (Blushed Crumbs)</div>
                    </div>
                    
                    <div class="nav-buttons cart-bar">
                        <button class="back-btn" id="back-step-8">Back</button>
                        <button class="next-btn" id="to-step-10">Continue</button>
                    </div>
                </section>

                <!-- Step 10: Inspiration Files -->
                <section class="step" id="step-10">
                    <h2>Inspiration Files</h2>
                    <p style="text-align:center; margin-bottom: 25px;">Have any pictures or designs you'd like us to use for inspiration?</p>
                    
                    <div id="upload-container" class="upload-dropzone">
                        <div class="upload-prompt">
                            <span class="upload-icon">✦</span>
                            <p>Drag &amp; drop your images here or <strong>browse</strong></p>
                            <span style="font-size: 12px; color: #aaa;">Supports PNG, JPG, JPEG</span>
                        </div>
                        <input type="file" id="inspiration-upload" multiple accept="image/*" style="display: none;">
                    </div>

                    <div id="preview-gallery"></div>

                    <div class="nav-buttons cart-bar">
                        <button class="back-btn" id="back-step-9">Back</button>
                        <button class="next-btn" id="to-step-11">Continue</button>
                    </div>
                </section>

                <!-- Step 11: Terms & Conditions -->
                <section class="step" id="step-11">
                    <h2>Terms &amp; Conditions</h2>
                    <p style="text-align:center; color: #ff6b8b; font-weight: 600;">PLEASE READ BEFORE ACCEPTING ‼️‼️‼️</p>
                    
                    <div class="terms-scroll-box">
                        <p>Please know I operate under cottage laws in Tennessee. I do not claim to be a nut free or gluten-free environment.</p>
                        <p><strong>Deposit Requirement:</strong> A 50% non-refundable deposit is required at the time of booking to secure your order date. Orders are not confirmed until the deposit is received.</p>
                        <p><strong>Final Payment:</strong> The remaining balance must be paid before pickup/delivery.</p>
                        <p><strong>Cancellations:</strong> Orders must be cancelled at least 48 hours in advance of the scheduled pickup or delivery time.</p>
                        <p><strong>Refund Policy:</strong> All deposits and payments are non-refundable.</p>
                        <p><strong>Order Changes:</strong> Any changes to the order must be requested at least 48 hours before the scheduled pickup or delivery time and may not always be guaranteed.</p>
                        <p><strong>Pickup/Delivery:</strong> Customers are responsible for picking up orders at the agreed time unless delivery has been arranged in advance.</p>
                        <p><strong>Custom orders:</strong> I welcome custom orders and love working on them. By signing this you understand cakes cannot be perfectly replicated. Each cake is unique and is tailored to the bakers style.</p>
                        <p><strong>Allergy Notice:</strong> Our cakes may contain or come into contact with common allergens such as dairy, eggs, wheat, soy, and nuts.</p>
                        <p><strong>Important Responsibility Notice:</strong> Please know once the cake is out of the bakers hands it is your responsibility to keep your cake or any baked goods cold and on a flat surface. This ensures the frosting from melting or the cake layers from sliding off. In the event of melted frosting or damage to any of your baked goods after leaving the hands of the baker, the baker is not at fault and the client will be held responsible.</p>
                    </div>

                    <div class="terms-acceptance-wrapper">
                        <label class="terms-checkbox-label">
                            <input type="checkbox" id="terms-agree-checkbox">
                            <span class="custom-checkbox"></span>
                            <span class="checkbox-text">I agree to the terms and conditions</span>
                        </label>
                    </div>

                    <div class="nav-buttons cart-bar">
                        <button class="back-btn" id="back-step-10">Back</button>
                        <button class="next-btn" id="to-step-12" disabled>Continue</button>
                    </div>
                </section>

                <!-- Step 12: Contact Information & Submit -->
                <section class="step" id="step-12">
                    <h2>Contact Information</h2>
                    <form id="order-form">
                        <input type="text" id="contact-name" placeholder="Full Name" required>
                        <input type="email" id="contact-email" placeholder="Email" required>
                        
                        <div class="phone-input-container">
                            <span class="phone-prefix">+1 US 🇺🇸</span>
                            <input type="tel" id="contact-phone" placeholder="Phone Number" required>
                        </div>

                        <input type="text" id="contact-coupon" placeholder="Coupon Code (optional)">
                        
                        <div class="nav-buttons cart-bar">
                            <button type="button" class="back-btn" id="back-step-11">Back</button>
                            <button type="submit" id="submit-form-btn">Submit Order</button>
                        </div>
                    </form>
                </section>
            </div>

            <!-- Step Confirmation screen -->
            <div id="thank-you-container" style="display:none;" class="order-content thank-you-card">
                <span class="success-heart">🌸</span>
                <h2>Thank You For Your Order!</h2>
                <p>Your details have been successfully received. We will look through everything and get back in touch with you shortly!</p>
                <button onclick="window.location.reload()" class="home-btn">Place Another Order</button>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Section -->
<section id="gallery" class="gallery-section">
    <h2 class="section-title">Bakery Portfolio</h2>
    <div class="gallery-grid" id="public-gallery-grid">
        @foreach($gallery as $item)
            <div class="gallery-card">
                <img src="{{ $item->image_url }}" alt="{{ $item->title }}">
                <div class="gallery-info">
                    <h4>{{ $item->title }}</h4>
                    <span>{{ $item->category }}</span>
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- Reviews Section -->
<section id="reviews" class="reviews-section">
    <h2 class="section-title">What Our Customers Say</h2>
    <div class="reviews-grid" id="public-reviews-grid">
        @foreach($reviews as $rev)
            <div class="review-card">
                <div class="rating">
                    @for($i = 0; $i < $rev->rating; $i++) ★ @endfor
                </div>
                <p>"{{ $rev->review_text }}"</p>
                <h4>- {{ $rev->client_name }}</h4>
            </div>
        @endforeach
    </div>
</section>

<!-- Footer -->
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-logo">🌸 Blushed Crumbs Bakehouse</div>
        <p>Handcrafted Custom Cakes & Baked Goods | Cottage Bakery, Tennessee</p>
        <p class="copyright">Copyright © {{ date('Y') }} Blushed Crumbs Bakehouse | Powered by BakeBox SaaS Engine</p>
    </div>
</footer>
@endsection
