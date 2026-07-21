<!-- POPUP MODAL ORDER FORM BUILDER -->
<div id="order-modal-popup" class="order-modal-overlay" style="display:none;">
    <div class="order-modal-card">
        <button class="modal-close-btn" onclick="closeOrderModal()">✕</button>

        <div id="cake-order-builder">
            <div class="order-content" id="form-container-toggle">
                
                <!-- STEP 1: PRODUCTS -->
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
                        <div class="product" data-name="Quarter-Sheet Cake" data-price="75"><strong>Quarter-Sheet Cake</strong><br>$75</div>
                        <div class="product" data-name="Baddie On a Budget 🎀" data-price="45"><strong>Baddie On a Budget 🎀</strong><br>$45</div>
                        <div class="product" data-name="1/2 Dozen Cupcakes" data-price="18"><strong>1/2 Dozen Cupcakes</strong><br>$18</div>
                        <div class="product" data-name="Dozen Cupcakes" data-price="35"><strong>Dozen Cupcakes</strong><br>$35</div>
                        <div class="product" data-name="Dozen Cake Shooters" data-price="50"><strong>Dozen Cake Shooters</strong><br>$50</div>
                        <div class="product" data-name="Dozen Chocolate Covered Strawberries" data-price="30"><strong>Dozen Chocolate Covered Strawberries</strong><br>$30</div>
                        <div class="product" data-name="Dozen Chocolate Covered Oreo" data-price="35"><strong>Dozen Chocolate Covered Oreo</strong><br>$35</div>
                        <div class="product" data-name="Dozen Chocolate Covered Marshmallows" data-price="20"><strong>Dozen Chocolate Covered Marshmallows</strong><br>$20</div>
                        <div class="product" data-name="Cakesickles" data-price="40"><strong>Cakesickles</strong><br>$40</div>
                        <div class="product" data-name="Chocolate Rice Krispies" data-price="35"><strong>Chocolate Rice Krispies</strong><br>$35</div>
                        <div class="product" data-name="Small Party Pack" data-price="100"><strong>Small Party Pack</strong><br>$100</div>
                        <div class="product" data-name="Medium Party Pack" data-price="165"><strong>Medium Party Pack</strong><br>$165</div>
                        <div class="product" data-name="Large Party Pack" data-price="220"><strong>Large Party Pack</strong><br>$220</div>
                        <div class="product" data-name="Extra Large Party Pack" data-price="335"><strong>Extra Large Party Pack</strong><br>$335</div>
                        <div class="product" data-name="Small 2 Tiered Cake" data-price="120"><strong>Small 2 Tiered Cake</strong><br>$120</div>
                        <div class="product" data-name="Medium 2 Tiered Cake" data-price="175"><strong>Medium 2 Tiered Cake</strong><br>$175</div>
                        <div class="product" data-name="Large 2 Tiered Cake" data-price="245"><strong>Large 2 Tiered Cake</strong><br>$245</div>
                        <div class="product" data-name="2 Tiered Heart Cake" data-price="145"><strong>2 Tiered Heart Cake</strong><br>$145</div>
                        <div class="product" data-name="Small 3 Tiered Cake" data-price="325"><strong>Small 3 Tiered Cake</strong><br>$325</div>
                        <div class="product" data-name="Wedding Consultation" data-price="0"><strong>Wedding Consultation</strong><br>$0</div>
                        <div class="product" data-name="Wedding Tasting Pack" data-price="50"><strong>Wedding Tasting Pack</strong><br>$50</div>
                    </div>
                    
                    <div class="cart-bar">
                        <div id="cart-items-list"></div>
                        <div id="cart-summary">Items: 0 <br> <strong>Total: $0</strong></div>
                        <div class="nav-buttons">
                            <button class="next-btn" id="to-step-2" disabled>Continue</button>
                        </div>
                    </div>
                </section>

                <!-- STEP 2: SELECT DATE -->
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

                <!-- STEP 3: FLAVORS -->
                <section class="step" id="step-3">
                    <h2>Choose Your Flavors</h2>
                    <p style="text-align:center; margin-bottom:15px; font-size:0.9rem; color:#666;">Select all that apply (Luxury flavors +$10)</p>
                    <div id="flavor-list">
                        <div class="product">Strawberry Bliss</div>
                        <div class="product">Vanilla Bean</div>
                        <div class="product">Chocolate Dream</div>
                        <div class="product">Creamy Hazelnut</div>
                        <div class="product">Confetti Explosion</div>
                        <div class="product">Red Velvet</div>
                        <div class="product">Swirly Marble</div>
                        <div class="product">Lemon Drop</div>
                        <div class="product">Golden Carrot</div>
                        <div class="product">Raspberry Swirl</div>
                        <div class="product">Almond Elegance (LUX)</div>
                        <div class="product">Pistachio ice (LUX)</div>
                        <div class="product">Coconut Cream (LUX)</div>
                        <div class="product">Cinnamon spice (LUX)</div>
                        <div class="product">Cotton candy temptation (LUX)</div>
                        <div class="product">Espresso Bean (LUX)</div>
                        <div class="product">Cherry Bark (LUX)</div>
                        <div class="product">Cookie Butter (LUX)</div>
                        <div class="product">Tres Leches (LUX)</div>
                        <div class="product">Banana Creme Pie (LUX)</div>
                    </div>
                    
                    <div class="nav-buttons cart-bar">
                        <button class="back-btn" id="back-step-2">Back</button>
                        <button class="next-btn" id="to-step-4">Continue</button>
                    </div>
                </section>

                <!-- STEP 4: FROSTING -->
                <section class="step" id="step-4">
                    <h2>Select Frosting</h2>
                    <div id="frosting-list">
                        <div class="product">Vanilla buttercream</div>
                        <div class="product">Cream Cheese</div>
                        <div class="product">Strawberry buttercream</div>
                        <div class="product">Oreo buttercream</div>
                        <div class="product">Chocolate buttercream</div>
                        <div class="product">Confetti buttercream</div>
                        <div class="product">Whip Cream</div>
                    </div>
                    
                    <div class="nav-buttons cart-bar">
                        <button class="back-btn" id="back-step-3">Back</button>
                        <button class="next-btn" id="to-step-5">Continue</button>
                    </div>
                </section>

                <!-- STEP 5: FILLINGS -->
                <section class="step" id="step-5">
                    <h2>Choice of Fillings?</h2>
                    <p style="text-align:center; margin-bottom:15px; font-size:0.9rem; color:#666;">Select all that apply</p>
                    <div id="filling-list">
                        <div class="product">Fudge</div>
                        <div class="product">Cookies and cream</div>
                        <div class="product">Strawberries and cream</div>
                        <div class="product">Peanut Butter</div>
                        <div class="product">Nutella</div>
                        <div class="product">Edible cookie dough</div>
                        <div class="product">Lemon curd</div>
                        <div class="product">Plain buttercream/frosting (no additional cost)</div>
                        <div class="product">Raspberry</div>
                        <div class="product">Cheesecake</div>
                        <div class="product">Dulce de Leche</div>
                        <div class="product">Pineapple</div>
                        <div class="product">Cream cheese</div>
                        <div class="product">Banana</div>
                    </div>
                    
                    <div class="nav-buttons cart-bar">
                        <button class="back-btn" id="back-step-4">Back</button>
                        <button class="next-btn" id="to-step-6">Continue</button>
                    </div>
                </section>

                <!-- STEP 6: SPECIAL REQUESTS -->
                <section class="step" id="step-6">
                    <h2>Special Requests</h2>
                    <p style="text-align:center; margin-bottom: 20px;">Is there anything specific you want me to add or know about your order?</p>
                    
                    <textarea id="order-notes" placeholder="Type your notes here..." style="width:100%; height:110px; padding:12px; border-radius:10px; border:1px solid #ccc; font-family:inherit;"></textarea>
                    
                    <div class="nav-buttons cart-bar">
                        <button class="back-btn" id="back-step-5">Back</button>
                        <button class="next-btn" id="to-step-7">Continue</button>
                    </div>
                </section>

                <!-- STEP 7: FULFILLMENT OPTIONS -->
                <section class="step" id="step-7">
                    <h2>Fulfillment Options</h2>
                    
                    <div id="fulfillment-grid" class="fulfillment-options" style="display:flex; justify-content:center; gap:20px; margin-bottom:20px;">
                        <div class="product token-option active" data-value="pickup" style="padding:15px 30px; border-radius:12px; cursor:pointer;">
                            <strong>Pickup</strong>
                        </div>
                        <div class="product token-option" data-value="delivery" style="padding:15px 30px; border-radius:12px; cursor:pointer;">
                            <strong>Delivery</strong>
                        </div>
                    </div>

                    <div id="address-wrapper" class="hidden-field" style="display:none; margin-bottom:20px;">
                        <p style="text-align:left; margin-top: 15px; font-weight: 600; color: #b35978;">Delivery Address</p>
                        <input type="text" id="delivery-address" placeholder="Street Address, City, State, ZIP" style="width:100%; padding:12px; border-radius:10px; border:1px solid #ccc;">
                    </div>

                    <div id="time-slot-wrapper">
                        <p style="text-align:left; margin-top: 20px; font-weight: 600; color: #b35978;">Select Time Frame</p>
                        <div class="time-radio-grid" style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;">
                            <label class="time-radio-card" style="background:#fff; border:1px solid #ddd; padding:8px 16px; border-radius:8px; cursor:pointer;">
                                <input type="radio" name="order-time" value="8:30"> 8:30 AM
                            </label>
                            <label class="time-radio-card" style="background:#fff; border:1px solid #ddd; padding:8px 16px; border-radius:8px; cursor:pointer;">
                                <input type="radio" name="order-time" value="9:00"> 9:00 AM
                            </label>
                            <label class="time-radio-card" style="background:#fff; border:1px solid #ddd; padding:8px 16px; border-radius:8px; cursor:pointer;">
                                <input type="radio" name="order-time" value="9:30" checked> 9:30 AM
                            </label>
                            <label class="time-radio-card" style="background:#fff; border:1px solid #ddd; padding:8px 16px; border-radius:8px; cursor:pointer;">
                                <input type="radio" name="order-time" value="10:00"> 10:00 AM
                            </label>
                            <label class="time-radio-card" style="background:#fff; border:1px solid #ddd; padding:8px 16px; border-radius:8px; cursor:pointer;">
                                <input type="radio" name="order-time" value="10:30"> 10:30 AM
                            </label>
                        </div>
                    </div>
                    
                    <div class="nav-buttons cart-bar" style="margin-top:25px;">
                        <button class="back-btn" id="back-step-6">Back</button>
                        <button class="next-btn" id="to-step-8">Continue</button>
                    </div>
                </section>

                <!-- STEP 8: ALLERGIES -->
                <section class="step" id="step-8">
                    <h2>Any Allergies?</h2>
                    <p style="text-align:center; max-width: 600px; margin: 0 auto 20px auto; font-size: 14px; line-height: 1.5; color: #888;">
                        By answering this question you understand any allergies not listed I will not be at fault for.
                    </p>
                    
                    <textarea id="order-allergies" placeholder="List any allergies here (or type 'None')..." style="width:100%; height:110px; padding:12px; border-radius:10px; border:1px solid #ccc; font-family:inherit;"></textarea>
                    
                    <div class="nav-buttons cart-bar">
                        <button class="back-btn" id="back-step-7">Back</button>
                        <button class="next-btn" id="to-step-9">Continue</button>
                    </div>
                </section>

                <!-- STEP 9: SOCIAL MEDIA FOLLOWS -->
                <section class="step" id="step-9">
                    <h2>Social Media Follows</h2>
                    <p style="text-align:center; max-width: 600px; margin: 0 auto 25px auto;">
                        <strong>$5 off</strong> for social media you follow or join!<br>
                        <span style="font-size: 13px; color: #888;">(Does not apply to the ones you are already following. Select all that apply.)</span>
                    </p>
                    
                    <div id="social-grid">
                        <div class="product">Instagram: (@Blushed_Crumbs)</div>
                        <div class="product">Facebook group: (Blushes Crumbs)</div>
                        <div class="product">TikTok: (@Blushed_Crumbs)</div>
                        <div class="product">Facebook page: (Blushed Crumbs)</div>
                    </div>
                    
                    <div class="nav-buttons cart-bar">
                        <button class="back-btn" id="back-step-8">Back</button>
                        <button class="next-btn" id="to-step-10">Continue</button>
                    </div>
                </section>

                <!-- STEP 10: INSPIRATION FILES UPLOAD -->
                <section class="step" id="step-10">
                    <h2>Inspiration Files</h2>
                    <p style="text-align:center; margin-bottom: 25px;">Have any pictures or designs you'd like us to use for inspiration?</p>
                    
                    <div id="upload-container" class="upload-dropzone" style="border:2px dashed #e67399; background:#fff7fa; padding:35px 20px; border-radius:16px; text-align:center; cursor:pointer;" onclick="document.getElementById('inspiration-upload').click()">
                        <div class="upload-prompt">
                            <span class="upload-icon" style="font-size:2.5rem; color:#e67399; display:block; margin-bottom:10px;">✦</span>
                            <p style="font-size:1.05rem;">Drag & drop your images here or <strong>browse</strong></p>
                            <span style="font-size: 12px; color: #aaa;">Supports PNG, JPG, JPEG</span>
                        </div>
                        <input type="file" id="inspiration-upload" multiple accept="image/*" style="display: none;">
                    </div>

                    <div id="preview-gallery" style="display:flex; gap:10px; flex-wrap:wrap; margin-top:20px;"></div>

                    <div class="nav-buttons cart-bar">
                        <button class="back-btn" id="back-step-9">Back</button>
                        <button class="next-btn" id="to-step-11">Continue</button>
                    </div>
                </section>

                <!-- STEP 11: TERMS & CONDITIONS -->
                <section class="step" id="step-11">
                    <h2>Terms & Conditions</h2>
                    <p style="text-align:center; color: #ff6b8b; font-weight: 600; margin-bottom:15px;">PLEASE READ BEFORE ACCEPTING ‼️‼️‼️</p>
                    
                    <div class="terms-scroll-box" style="height:180px; overflow-y:auto; border:1px solid #eee; padding:15px; border-radius:12px; background:#fafafa; font-size:0.85rem; line-height:1.6; margin-bottom:15px;">
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

                    <div class="terms-acceptance-wrapper" style="margin-bottom:15px;">
                        <label class="terms-checkbox-label" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                            <input type="checkbox" id="terms-agree-checkbox">
                            <span class="checkbox-text" style="font-weight:600; font-size:0.95rem; color:#4a2133;">I agree to the terms and conditions</span>
                        </label>
                    </div>

                    <div class="nav-buttons cart-bar">
                        <button class="back-btn" id="back-step-10">Back</button>
                        <button class="next-btn" id="to-step-12" disabled>Continue</button>
                    </div>
                </section>

                <!-- STEP 12: CONTACT INFO & SUBMIT -->
                <section class="step" id="step-12">
                    <h2>Contact Information</h2>
                    <form id="order-form">
                        <input type="text" id="contact-name" placeholder="Full Name" required style="width:100%; padding:12px; border-radius:10px; border:1px solid #ccc; margin-bottom:12px;">
                        <input type="email" id="contact-email" placeholder="Email" required style="width:100%; padding:12px; border-radius:10px; border:1px solid #ccc; margin-bottom:12px;">
                        
                        <div class="phone-input-container" style="display:flex; align-items:center; border:1px solid #ccc; border-radius:10px; overflow:hidden; margin-bottom:12px;">
                            <span class="phone-prefix" style="background:#eee; padding:12px; font-weight:600; font-size:0.9rem;">+1 US 🇺🇸</span>
                            <input type="tel" id="contact-phone" placeholder="Phone Number" required style="border:none; padding:12px; width:100%; outline:none;">
                        </div>

                        <input type="text" id="contact-coupon" placeholder="Coupon Code (optional)" style="width:100%; padding:12px; border-radius:10px; border:1px solid #ccc; margin-bottom:15px;">
                        
                        <div class="nav-buttons cart-bar">
                            <button type="button" class="back-btn" id="back-step-11">Back</button>
                            <button type="submit" id="submit-form-btn" class="btn btn-primary" style="padding:12px 30px;">Submit Order</button>
                        </div>
                    </form>
                </section>
            </div>

            <div id="thank-you-container" style="display:none; text-align:center; padding:40px 20px;">
                <span style="font-size:3.5rem;">🌸</span>
                <h2 style="font-family:'Great Vibes', cursive; font-size:3.2rem; margin:15px 0; color:#5c1d37;">Thank You For Your Order!</h2>
                <p style="font-size:1.05rem; color:#555;">Your order details have been successfully submitted to the baker. We will review your request and send your confirmation & invoice shortly!</p>
                <button onclick="window.location.reload()" class="btn btn-primary" style="margin-top:25px; padding:12px 30px;">Place Another Order</button>
            </div>
        </div>
    </div>
</div>
