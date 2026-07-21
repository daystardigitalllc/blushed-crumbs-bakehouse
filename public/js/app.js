// Global Mobile Sidebar Drawer Toggle
window.toggleAdminMobileSidebar = function toggleAdminMobileSidebar() {
    const sidebar = document.getElementById('admin-sidebar-drawer');
    const overlay = document.getElementById('admin-sidebar-overlay');
    if (sidebar) sidebar.classList.toggle('open');
    if (overlay) overlay.classList.toggle('active');
};

document.addEventListener('DOMContentLoaded', () => {
    init12StepOrderForm();
    initAdminPortal();
    renderInteractiveCalendar();
    initOrderModalTrigger();
    initGalleryFiltering();
    handleHashNavigation();
});

// Window Hash Listener for SPA Routing (#gallery, #admin, #home)
window.addEventListener('hashchange', () => {
    handleHashNavigation();
});

function handleHashNavigation() {
    const hash = window.location.hash;
    if (hash === '#gallery') {
        toggleView('gallery');
    } else if (hash === '#about') {
        toggleView('about');
    } else if (hash === '#admin' || hash === '#admin-portal-view') {
        toggleView('admin');
    } else if (hash === '#home') {
        if (document.getElementById('storefront-view')) {
            toggleView('storefront');
        }
        if (history.replaceState) {
            history.replaceState(null, null, window.location.pathname + window.location.search);
        }
    }
}

// Global View Navigation Switcher
window.toggleView = function(view) {
    const storefront = document.getElementById('storefront-view');
    const aboutPage = document.getElementById('about-page-view');
    const galleryPage = document.getElementById('gallery-page-view');
    const adminPortal = document.getElementById('admin-portal-view');

    if (storefront) storefront.style.display = 'none';
    if (aboutPage) aboutPage.style.display = 'none';
    if (galleryPage) galleryPage.style.display = 'none';
    if (adminPortal) adminPortal.style.display = 'none';

    if (view === 'admin') {
        if (adminPortal) adminPortal.style.display = 'block';
        window.location.hash = 'admin';
    } else if (view === 'gallery') {
        if (galleryPage) galleryPage.style.display = 'block';
        window.location.hash = 'gallery';
    } else if (view === 'about') {
        if (aboutPage) aboutPage.style.display = 'block';
        window.location.hash = 'about';
    } else {
        if (storefront) storefront.style.display = 'block';
        if (window.location.hash && history.replaceState) {
            history.replaceState(null, null, window.location.pathname + window.location.search);
        }
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// State Store
const state = {
    currentStep: 1,
    selectedProducts: [],
    selectedDate: null,
    selectedFlavors: [],
    selectedFrosting: [],
    selectedFillings: [],
    fulfillment: 'pickup',
    discounts: 0,
    subtotal: 0,
    total: 0,
    deposit: 0,
    uploadedFiles: []
};

// Modal Order Form Triggers
function initOrderModalTrigger() {
    document.querySelectorAll('.trigger-order-modal, .nav-order-btn, .about-cta-btn, [data-trigger="order"]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openOrderModal();
        });
    });
}

window.openOrderModal = function() {
    const modal = document.getElementById('order-modal-popup');
    if (modal) modal.style.display = 'flex';
};

window.closeOrderModal = function() {
    const modal = document.getElementById('order-modal-popup');
    if (modal) modal.style.display = 'none';
};

// Complete 12-Step Form Navigation Logic
function init12StepOrderForm() {
    // Step 1: Product Selection
    const productGrid = document.getElementById('product-grid');
    if (productGrid) {
        productGrid.addEventListener('click', (e) => {
            const card = e.target.closest('.product');
            if (!card) return;

            const name = card.dataset.name || card.innerText.split('\n')[0];
            const price = parseFloat(card.dataset.price || 0);

            card.classList.toggle('selected');
            
            if (card.classList.contains('selected')) {
                state.selectedProducts.push({ name, price });
            } else {
                state.selectedProducts = state.selectedProducts.filter(p => p.name !== name);
            }

            updateCartSummary();
        });
    }

    // Step Next / Back Button Binding
    document.querySelectorAll('.next-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const nextStepId = btn.id.replace('to-step-', '');
            if (nextStepId) goToStep(parseInt(nextStepId));
        });
    });

    document.querySelectorAll('.back-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const prevStepId = btn.id.replace('back-step-', '');
            if (prevStepId) goToStep(parseInt(prevStepId));
        });
    });

    setupMultiSelectGrid('flavor-list', state.selectedFlavors);
    setupMultiSelectGrid('frosting-list', state.selectedFrosting);
    setupMultiSelectGrid('filling-list', state.selectedFillings);

    // Step 7: Fulfillment Option Toggle
    const fulfillmentGrid = document.getElementById('fulfillment-grid');
    if (fulfillmentGrid) {
        fulfillmentGrid.addEventListener('click', (e) => {
            const card = e.target.closest('.token-option');
            if (!card) return;
            document.querySelectorAll('.token-option').forEach(c => c.classList.remove('active'));
            card.classList.add('active');
            state.fulfillment = card.dataset.value;

            const addrWrap = document.getElementById('address-wrapper');
            if (addrWrap) addrWrap.style.display = state.fulfillment === 'delivery' ? 'block' : 'none';
        });
    }

    // Step 9: Social Media Discounts
    const socialGrid = document.getElementById('social-grid');
    if (socialGrid) {
        socialGrid.addEventListener('click', (e) => {
            const card = e.target.closest('.product');
            if (!card) return;
            card.classList.toggle('selected');
            calculateDiscounts();
        });
    }

    // Step 10: Drag & Drop + File Upload for Inspiration Photos
    const fileInput = document.getElementById('inspiration-upload');
    const dropzone = document.getElementById('upload-container');
    const previewGallery = document.getElementById('preview-gallery');

    if (fileInput && previewGallery) {
        fileInput.addEventListener('change', (e) => {
            handleFileUploads(e.target.files, previewGallery);
        });
    }

    if (dropzone) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });

        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && previewGallery) {
                handleFileUploads(files, previewGallery);
            }
        });
    }

    // Step 11: Terms Checkbox Enablement
    const termsCheck = document.getElementById('terms-agree-checkbox');
    const step11Next = document.getElementById('to-step-12');
    if (termsCheck && step11Next) {
        termsCheck.addEventListener('change', () => {
            step11Next.disabled = !termsCheck.checked;
        });
    }

    // Step 12: Order Submission
    const orderForm = document.getElementById('order-form');
    if (orderForm) {
        orderForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const clientName = document.getElementById('contact-name').value;
            const clientEmail = document.getElementById('contact-email').value;
            const clientPhone = document.getElementById('contact-phone').value;

            document.getElementById('form-container-toggle').style.display = 'none';
            document.getElementById('thank-you-container').style.display = 'block';

            const newOrderNumber = 'BC-' + Math.floor(1000 + Math.random() * 9000);
            const orderObj = {
                order_number: newOrderNumber,
                client_name: clientName,
                client_email: clientEmail,
                client_phone: clientPhone,
                due_date: state.selectedDate || '2026-07-28',
                time_slot: '9:30 AM',
                fulfillment_type: state.fulfillment,
                items: state.selectedProducts,
                total_price: state.total,
                deposit_amount: state.deposit,
                status: 'new'
            };

            appendOrderToAdminQueue(orderObj);
        });
    }
}

function handleFileUploads(files, previewGallery) {
    const statusEl = document.getElementById('upload-count-status');
    Array.from(files).forEach(file => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const imgWrap = document.createElement('div');
                imgWrap.style.cssText = 'position:relative; width:100px; height:100px; border-radius:12px; overflow:hidden; border:2px solid #e67399; box-shadow:0 4px 12px rgba(0,0,0,0.1); background:white;';
                imgWrap.innerHTML = `
                    <img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover;">
                    <span style="position:absolute; bottom:4px; left:4px; background:rgba(40,167,69,0.9); color:white; font-size:10px; font-weight:700; padding:2px 6px; border-radius:8px;">✅ Uploaded</span>
                    <span style="position:absolute; top:4px; right:4px; background:rgba(92,29,55,0.85); color:white; border-radius:50%; width:22px; height:22px; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:bold; cursor:pointer;" onclick="this.parentElement.remove(); updateUploadCountStatus();">✕</span>
                `;
                previewGallery.appendChild(imgWrap);
                state.uploadedFiles.push(file);
                updateUploadCountStatus();
            };
            reader.readAsDataURL(file);
        }
    });
}

function updateUploadCountStatus() {
    const statusEl = document.getElementById('upload-count-status');
    const previewGallery = document.getElementById('preview-gallery');
    if (statusEl && previewGallery) {
        const count = previewGallery.children.length;
        if (count > 0) {
            statusEl.style.display = 'block';
            statusEl.innerText = `✅ ${count} Inspiration photo(s) uploaded successfully!`;
        } else {
            statusEl.style.display = 'none';
        }
    }
}


function goToStep(stepNum) {
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    const targetStep = document.getElementById(`step-${stepNum}`);
    if (targetStep) targetStep.classList.add('active');
    state.currentStep = stepNum;
}

function updateCartSummary() {
    state.subtotal = state.selectedProducts.reduce((sum, item) => sum + item.price, 0);
    state.total = Math.max(0, state.subtotal - state.discounts);
    state.deposit = state.total * 0.5;

    const itemsSummaryText = state.selectedProducts.length > 0
        ? state.selectedProducts.map(p => p.name).join(', ')
        : 'No items selected';

    const listEl = document.getElementById('cart-items-list');
    const summaryEl = document.getElementById('cart-summary');
    const globalItemsEl = document.getElementById('global-cart-items-summary');
    const globalTotalEl = document.getElementById('global-cart-total-estimate');
    const step1Next = document.getElementById('to-step-2');

    if (listEl) listEl.innerHTML = itemsSummaryText;
    if (globalItemsEl) globalItemsEl.innerText = itemsSummaryText;

    if (summaryEl) {
        summaryEl.innerHTML = `Items: ${state.selectedProducts.length} <br> <strong>Total: $${state.total.toFixed(0)}</strong>`;
    }

    if (globalTotalEl) {
        globalTotalEl.innerText = `$${state.total.toFixed(0)}`;
    }

    if (step1Next) {
        step1Next.disabled = state.selectedProducts.length === 0;
    }
}

function calculateDiscounts() {
    const selectedSocials = document.querySelectorAll('#social-grid .product.selected');
    state.discounts = selectedSocials.length * 5;
    updateCartSummary();
}

function setupMultiSelectGrid(elementId, targetArray) {
    const grid = document.getElementById(elementId);
    if (!grid) return;

    grid.addEventListener('click', (e) => {
        const card = e.target.closest('.product');
        if (!card) return;
        card.classList.toggle('selected');
        const name = card.dataset.name || card.innerText;
        
        if (card.classList.contains('selected')) {
            targetArray.push(name);
        } else {
            const idx = targetArray.indexOf(name);
            if (idx > -1) targetArray.splice(idx, 1);
        }
    });
}

function renderInteractiveCalendar() {
    const calGrid = document.getElementById('interactive-calendar-grid');
    if (!calGrid) return;

    calGrid.innerHTML = '';
    const daysInMonth = 31;
    const bookedDays = [4, 5, 12, 15, 17, 18, 21, 22, 23, 24, 25, 26];

    for (let day = 1; day <= daysInMonth; day++) {
        const isBooked = bookedDays.includes(day);
        const dayEl = document.createElement('div');
        dayEl.className = `cal-day ${isBooked ? 'booked' : ''}`;
        dayEl.innerText = day;

        if (!isBooked) {
            dayEl.addEventListener('click', () => {
                document.querySelectorAll('.cal-day').forEach(d => d.classList.remove('selected'));
                dayEl.classList.add('selected');
                state.selectedDate = `2026-07-${day < 10 ? '0' + day : day}`;
                document.getElementById('selected-date').innerText = state.selectedDate;
                const step2Next = document.getElementById('to-step-3');
                if (step2Next) step2Next.disabled = false;
            });
        }

        calGrid.appendChild(dayEl);
    }
}

// Dedicated Gallery Filtering & Lightbox
function initGalleryFiltering() {
    const filterBtns = document.querySelectorAll('.gallery-filter-bar .filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const category = btn.dataset.filter;
            const galleryCards = document.querySelectorAll('.gallery-masonry-grid .gallery-card');

            galleryCards.forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
}

window.openLightbox = function(imageSrc, caption) {
    const modal = document.getElementById('lightbox-modal');
    const img = document.getElementById('lightbox-img');
    const cap = document.getElementById('lightbox-caption');

    if (modal && img) {
        img.src = imageSrc;
        if (cap) cap.innerText = caption || '';
        modal.style.display = 'flex';
    }
};

window.closeLightbox = function() {
    const modal = document.getElementById('lightbox-modal');
    if (modal) modal.style.display = 'none';
};

// Baker Admin Portal Controller & Sidebar Switcher
function initAdminPortal() {
    const mobileBtn = document.getElementById('mobile-hamburger-trigger');
    if (mobileBtn) {
        mobileBtn.onclick = function(e) {
            e.preventDefault();
            window.toggleAdminMobileSidebar();
        };
    }
    // Sidebar & Tab Buttons Switcher
    const adminNavBtns = document.querySelectorAll('.admin-sidebar-nav .admin-nav-item, .admin-tabs .tab-btn');
    adminNavBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.admin-nav-item, .tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));

            btn.classList.add('active');
            const targetTab = document.getElementById(btn.dataset.tab);
            if (targetTab) targetTab.classList.add('active');

            // Auto-close mobile drawer when a tab is selected
            const sidebar = document.getElementById('admin-sidebar-drawer');
            const overlay = document.getElementById('admin-sidebar-overlay');
            if (sidebar && sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
            }
            if (overlay && overlay.classList.contains('active')) {
                overlay.classList.remove('active');
            }
        });
    });

    // Device Gallery File Preview Handler
    const galFileInput = document.getElementById('gal-image-file');
    const galPreviewWrap = document.getElementById('gal-upload-preview');
    const galPreviewImg = document.getElementById('gal-preview-img');

    if (galFileInput && galPreviewImg) {
        galFileInput.addEventListener('change', (e) => {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    galPreviewImg.src = ev.target.result;
                    if (galPreviewWrap) galPreviewWrap.style.display = 'block';
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }

    // Custom Payment Method Builder Handler
    const payForm = document.getElementById('add-payment-method-form');
    if (payForm) {
        payForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const methodName = document.getElementById('pay-method-name').value.trim();
            const methodHandle = document.getElementById('pay-method-handle').value.trim();
            const methodInstructions = document.getElementById('pay-method-instructions').value.trim();

            const payList = document.getElementById('payment-methods-list');
            if (payList) {
                const row = document.createElement('div');
                row.className = 'payment-method-row';
                row.style.cssText = 'display:flex; justify-content:space-between; align-items:center; background:white; padding:15px; border-radius:12px; margin-bottom:10px; border:1px solid #eee;';
                row.innerHTML = `
                    <div>
                        <strong style="color:#5c1d37; font-size:1.05rem;">💳 ${methodName}</strong>: <code>${methodHandle}</code>
                        ${methodInstructions ? `<p style="font-size:0.85rem; color:#666; margin-top:2px;">${methodInstructions}</p>` : ''}
                    </div>
                    <button class="btn btn-sm btn-outline" style="color:#d9534f; border-color:#d9534f;" onclick="this.parentElement.remove()">Remove</button>
                `;
                payList.prepend(row);
            }

            alert(`Payment Method "${methodName}" (${methodHandle}) added to Baker Admin Portal & Client Invoice Options!`);
            payForm.reset();
        });
    }

    // Email Routing Form Handler
    const emailForm = document.getElementById('email-routing-form');
    if (emailForm) {
        emailForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const emailInput = document.getElementById('admin-routing-email');
            const statusEl = document.getElementById('email-save-status');

            if (emailInput) {
                const routingEmail = emailInput.value.trim();
                localStorage.setItem('admin_routing_email', routingEmail);
                if (statusEl) {
                    statusEl.style.display = 'block';
                    statusEl.innerText = `✅ Order Notification Email saved as: ${routingEmail}! Future form entries will route here.`;
                }
            }
        });
    }

    // Add Custom Field / Question to Order Form Handler
    const fieldForm = document.getElementById('add-field-form');
    if (fieldForm) {
        fieldForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const targetStepId = document.getElementById('field-target-step').value;
            const labelText = document.getElementById('field-label').value;
            const fieldType = document.getElementById('field-type').value;
            const optionsText = document.getElementById('field-options').value;

            const targetStep = document.getElementById(targetStepId);
            if (targetStep) {
                const fieldContainer = document.createElement('div');
                fieldContainer.className = 'custom-baker-field';
                fieldContainer.style.cssText = 'margin:15px 0; text-align:left; background:#fff7fa; padding:15px; border-radius:12px; border:1px solid #f8c6d7;';
                
                let fieldHtml = `<label style="font-weight:700; color:#5c1d37; display:block; margin-bottom:6px;">${labelText}</label>`;
                
                if (fieldType === 'textarea') {
                    fieldHtml += `<textarea placeholder="Type answer here..." style="width:100%; height:80px; padding:10px; border-radius:8px; border:1px solid #ccc;"></textarea>`;
                } else if (fieldType === 'select') {
                    const opts = optionsText.split(',').map(o => o.trim());
                    fieldHtml += `<select style="width:100%; padding:10px; border-radius:8px; border:1px solid #ccc;">${opts.map(o => `<option value="${o}">${o}</option>`).join('')}</select>`;
                } else if (fieldType === 'file') {
                    fieldHtml += `<input type="file" style="width:100%; padding:8px; border-radius:8px; border:1px solid #ccc; background:#fff;">`;
                } else {
                    fieldHtml += `<input type="text" placeholder="Type answer here..." style="width:100%; padding:10px; border-radius:8px; border:1px solid #ccc;">`;
                }

                fieldContainer.innerHTML = fieldHtml;
                const cartBar = targetStep.querySelector('.cart-bar') || targetStep.querySelector('.nav-buttons');
                if (cartBar) {
                    targetStep.insertBefore(fieldContainer, cartBar);
                } else {
                    targetStep.appendChild(fieldContainer);
                }

                alert(`Custom field "${labelText}" added live to ${targetStepId.toUpperCase()} in customer order form!`);
                fieldForm.reset();
            }
        });
    }

    // Add Product Form
    const prodForm = document.getElementById('add-product-form');
    if (prodForm) {
        prodForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const name = document.getElementById('new-prod-name').value;
            const price = parseFloat(document.getElementById('new-prod-price').value);
            const category = document.getElementById('new-prod-category').value;

            // Dynamically add to product grid in Order Form Step 1
            const step1Grid = document.getElementById('product-grid');
            if (step1Grid) {
                const prodCard = document.createElement('div');
                prodCard.className = 'product';
                prodCard.dataset.name = name;
                prodCard.dataset.price = price;
                prodCard.innerHTML = `<strong>${name}</strong><br>$${price.toFixed(0)}`;
                step1Grid.prepend(prodCard);
            }

            const adminGrid = document.getElementById('products-admin-grid');
            if (adminGrid) {
                const row = document.createElement('div');
                row.className = 'product-item-row';
                row.style.cssText = 'display:flex; justify-content:space-between; align-items:center; padding:12px; border-bottom:1px solid #eee;';
                row.innerHTML = `
                    <span><strong>${name}</strong> ($${price.toFixed(2)})</span>
                    <div>
                        <input type="number" class="price-input" value="${price.toFixed(2)}" style="width:80px;">
                        <button class="btn btn-sm btn-secondary" onclick="alert('Price updated!')">Save Price</button>
                    </div>
                `;
                adminGrid.prepend(row);
            }

            alert(`Product "${name}" added to order builder & live storefront!`);
            prodForm.reset();
        });
    }

    // Add Gallery Image Form (Device Upload Supported!)
    const galleryForm = document.getElementById('add-gallery-form');
    if (galleryForm) {
        galleryForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const title = document.getElementById('gal-title').value;
            const category = document.getElementById('gal-category').value;
            const fileInput = document.getElementById('gal-image-file');

            let imageSrc = 'public/images/IMG_8117.jpg';

            if (galPreviewImg && galPreviewImg.src && galPreviewImg.src.startsWith('data:image')) {
                imageSrc = galPreviewImg.src;
            } else if (fileInput && fileInput.files && fileInput.files[0]) {
                imageSrc = URL.createObjectURL(fileInput.files[0]);
            }

            const mainGalleryGrid = document.getElementById('public-gallery-grid');
            if (mainGalleryGrid) {
                const card = document.createElement('div');
                card.className = 'gallery-card';
                card.dataset.category = category;
                card.onclick = () => openLightbox(imageSrc, title);
                card.innerHTML = `
                    <div class="gallery-card-img-wrap">
                        <img src="${imageSrc}" alt="${title}">
                    </div>
                    <div class="gallery-card-info">
                        <h4>${title}</h4>
                        <span class="gallery-tag">${category}</span>
                    </div>
                `;
                mainGalleryGrid.prepend(card);
            }

            const adminGalleryList = document.getElementById('admin-gallery-list');
            if (adminGalleryList) {
                const adminItem = document.createElement('div');
                adminItem.style.cssText = 'display:flex; align-items:center; justify-content:space-between; background:white; padding:12px; border-radius:12px; margin-bottom:10px; box-shadow:0 4px 12px rgba(0,0,0,0.05);';
                adminItem.innerHTML = `
                    <div style="display:flex; align-items:center; gap:15px;">
                        <img src="${imageSrc}" style="width:55px; height:55px; object-fit:cover; border-radius:10px;">
                        <div>
                            <strong style="color:#5c1d37;">${title}</strong><br>
                            <span style="font-size:0.8rem; color:#e67399; font-weight:600;">${category}</span>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline" style="color:#d9534f; border-color:#d9534f;" onclick="this.parentElement.remove()">Delete</button>
                `;
                adminGalleryList.prepend(adminItem);
            }

            alert(`Photo "${title}" published live from device to your /gallery page!`);
            galleryForm.reset();
            if (galPreviewWrap) galPreviewWrap.style.display = 'none';
        });
    }

    // Add Review Form
    const revForm = document.getElementById('add-review-form');
    if (revForm) {
        revForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const name = document.getElementById('rev-client-name').value;
            const text = document.getElementById('rev-text').value;

            const pubGrid = document.getElementById('public-reviews-grid');
            if (pubGrid) {
                const revCard = document.createElement('div');
                revCard.className = 'cloud-review-card';
                revCard.innerHTML = `<p>"${text}"</p><h4>${name}</h4>`;
                pubGrid.prepend(revCard);
            }
            alert('Review published directly to storefront!');
            revForm.reset();
        });
    }
}

window.generateInvoiceFromOrder = function(invNum, clientName, total, deposit) {
    document.getElementById('modal-inv-num').innerText = invNum;
    document.getElementById('modal-inv-client').innerText = clientName;
    document.getElementById('modal-inv-total').innerText = '$' + total.toFixed(2);
    document.getElementById('modal-inv-deposit').innerText = '$' + deposit.toFixed(2);

    document.getElementById('invoice-modal').style.display = 'flex';
};

window.closeInvoiceModal = function() {
    document.getElementById('invoice-modal').style.display = 'none';
};

window.copyClientPayLink = function(orderNum) {
    alert(`Invoice payment link for Order #${orderNum} copied!`);
};

function appendOrderToAdminQueue(order) {
    const queue = document.getElementById('admin-orders-list');
    if (!queue) return;

    const card = document.createElement('div');
    card.className = 'order-card urgent-border';
    card.dataset.fulfillment = order.fulfillment_type;
    card.innerHTML = `
        <div class="order-card-header">
            <div class="due-badge due-urgent">⏰ DUE: ${order.due_date} (${order.time_slot})</div>
            <span class="status-tag status-new">NEW REQUEST</span>
        </div>
        <div class="order-card-body">
            <h4>#${order.order_number} - ${order.client_name}</h4>
            <p><strong>Phone:</strong> ${order.client_phone} | <strong>Email:</strong> ${order.client_email}</p>
            <p><strong>Fulfillment:</strong> ${order.fulfillment_type.toUpperCase()}</p>
            <div class="pricing-breakdown">
                <span>Total: <strong>$${order.total_price.toFixed(2)}</strong></span>
                <span>50% Deposit: <strong>$${order.deposit_amount.toFixed(2)}</strong> (⏳ Pending)</span>
            </div>
        </div>
        <div class="order-card-actions">
            <button class="btn btn-sm btn-primary" onclick="generateInvoiceFromOrder('${order.order_number}', '${order.client_name}', ${order.total_price}, ${order.deposit_amount})">💳 Create Invoice</button>
        </div>
    `;
    queue.prepend(card);
}
