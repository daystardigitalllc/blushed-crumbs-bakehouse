<!-- POPUP MODAL ORDER FORM BUILDER -->
<div id="order-modal-popup" class="order-modal-overlay" style="display:none;">
    <div class="order-modal-card">
        <button class="modal-close-btn" onclick="closeOrderModal()">✕</button>

        <div id="cake-order-builder">
            <div class="order-content" id="form-container-toggle">
                
                <!-- Step 1: Products -->
                <section class="step active" id="step-1">
                    <h2>Build Your Order</h2>
                    <div id="product-grid">
                        <div class="product" data-name="4” Cake" data-price="45"><strong>4” Cake</strong><br>$45</div>
                        <div class="product" data-name="6” Cake" data-price="65"><strong>6” Cake</strong><br>$65</div>
                        <div class="product" data-name="7” Cake" data-price="75"><strong>7” Cake</strong><br>$75</div>
                        <div class="product" data-name="8” Cake" data-price="85"><strong>8” Cake</strong><br>$85</div>
                        <div class="product" data-name="9” Cake" data-price="95"><strong>9” Cake</strong><br>$95</div>
                        <div class="product" data-name="10” Cake" data-price="115"><strong>10” Cake</strong><br>$115</div>
                        <div class="product" data-name="Bento Box" data-price="45"><strong>Bento Box</strong><br>$45</div>
                        <div class="product" data-name="Smash Cake" data-price="35"><strong>Smash Cake</strong><br>$35</div>
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
                                <div class="wpbs-legend" style="display:flex; justify-content:center; gap:20px; margin: 15px 0;">
                                    <div class="wpbs-legend-item" style="color: #28a745; font-weight: 600;">🟢 Available</div>
                                    <div class="wpbs-legend-item" style="color: #dc3545; font-weight: 600;">🔴 Booked</div>
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
                    </div>
                    
                    <div class="nav-buttons cart-bar">
                        <button class="back-btn" id="back-step-2">Back</button>
                        <button class="next-btn" id="to-step-4">Continue</button>
                    </div>
                </section>

                <!-- Step 12: Contact Info -->
                <section class="step" id="step-12">
                    <h2>Contact Information</h2>
                    <form id="order-form">
                        <input type="text" id="contact-name" placeholder="Full Name" required style="width:100%; padding:12px; border-radius:10px; border:1px solid #ccc; margin-bottom:12px;">
                        <input type="email" id="contact-email" placeholder="Email" required style="width:100%; padding:12px; border-radius:10px; border:1px solid #ccc; margin-bottom:12px;">
                        <input type="tel" id="contact-phone" placeholder="Phone Number" required style="width:100%; padding:12px; border-radius:10px; border:1px solid #ccc; margin-bottom:12px;">

                        <div class="nav-buttons cart-bar">
                            <button type="button" class="back-btn" id="back-step-11">Back</button>
                            <button type="submit" id="submit-form-btn" class="btn btn-primary">Submit Order</button>
                        </div>
                    </form>
                </section>
            </div>

            <div id="thank-you-container" style="display:none; text-align:center; padding:40px 20px;">
                <span style="font-size:3rem;">🌸</span>
                <h2 style="font-family:'Great Vibes', cursive; font-size:3rem; margin:15px 0;">Thank You For Your Order!</h2>
                <p>Your details have been successfully received. We will look through everything and get back in touch with you shortly!</p>
                <button onclick="window.location.reload()" class="btn btn-primary" style="margin-top:20px;">Place Another Order</button>
            </div>
        </div>
    </div>
</div>
