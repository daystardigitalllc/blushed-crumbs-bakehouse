// Non-Intrusive Bottom Toast Notification System
window.showToast = function(message, type = 'success', duration = 3500) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position:fixed; bottom:24px; right:24px; z-index:999999; display:flex; flex-direction:column; gap:10px; pointer-events:none; font-family:system-ui, -apple-system, sans-serif;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const isError = type === 'error';
    const isInfo = type === 'info';
    
    toast.style.cssText = `
        pointer-events:auto;
        display:flex;
        align-items:center;
        gap:12px;
        background:${isError ? '#2b0f1a' : (isInfo ? '#1a2736' : '#1e2d24')};
        color:#ffffff;
        border-left:5px solid ${isError ? '#ff4d4d' : (isInfo ? '#3399ff' : '#28a745')};
        padding:14px 20px;
        border-radius:12px;
        box-shadow:0 10px 30px rgba(0,0,0,0.25);
        font-size:0.92rem;
        font-weight:600;
        min-width:280px;
        max-width:450px;
        opacity:0;
        transform:translateY(20px) scale(0.95);
        transition:all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    `;

    const icon = isError ? '⚠️' : (isInfo ? 'ℹ️' : '✅');
    toast.innerHTML = `
        <span style="font-size:1.2rem;">${icon}</span>
        <span style="flex:1; line-height:1.4;">${message}</span>
        <button onclick="this.parentElement.remove()" style="background:none; border:none; color:rgba(255,255,255,0.6); cursor:pointer; font-size:1.1rem; padding:0 4px; line-height:1;" title="Dismiss">✕</button>
    `;

    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0) scale(1)';
    });

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px) scale(0.95)';
        setTimeout(() => toast.remove(), 400);
    }, duration);
};

// Global non-blocking alert override
window.alert = function(msg) {
    if (!msg) return;
    const isError = /error|failed|please|must|cannot|select|invalid/i.test(msg);
    window.showToast(msg, isError ? 'error' : 'success');
};

// Global Site Content Form Saver
window.saveSiteContentForm = function() {
    const form = document.getElementById('site-content-form');
    if (!form) return;

    const formData = new FormData(form);
    const msgEl = document.getElementById('content-status-msg');

    fetch('/dashboard/content', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (msgEl) {
                msgEl.style.display = 'block';
                msgEl.innerHTML = `✅ ${data.message} <a href="/" target="_blank" style="color:#065f46; font-weight:700; text-decoration:underline; margin-left:8px;">View Live Site ↗</a>`;
                setTimeout(() => { msgEl.style.display = 'none'; }, 4000);
            }
            window.showToast('Site copy & content saved successfully!', 'success');
        } else {
            window.showToast(data.message || 'Failed to save site content.', 'error');
        }
    })
    .catch(err => {
        window.showToast('Error saving content: ' + err.message, 'error');
    });
};

// Global Section Manager Reorder & Visibility Handler
window.toggleSectionAccordion = function(headerEl) {
    const row = headerEl.closest('.section-manager-row');
    if (!row) return;
    const body = row.querySelector('.section-accordion-body');
    const arrow = row.querySelector('.accordion-arrow');

    if (body) {
        const isHidden = body.style.display === 'none' || !body.style.display;
        body.style.display = isHidden ? 'block' : 'none';
        if (arrow) {
            arrow.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
        }
    }
};

window.addAccordionReviewItem = function() {
    const list = document.getElementById('accordion-reviews-list');
    if (!list) return;
    const idx = list.querySelectorAll('.accordion-review-item').length;
    const div = document.createElement('div');
    div.className = 'accordion-review-item';
    div.style.cssText = 'background:#FAF8FF; padding:12px; border-radius:10px; border:1px solid #e9d5ff; display:flex; flex-direction:column; gap:8px;';
    div.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <input type="text" name="reviews[${idx}][name]" value="" placeholder="Customer Name (e.g. Sarah Jenkins)" style="width:240px; padding:6px 10px; border-radius:6px; border:1px solid #ccc; font-weight:700;">
            <button type="button" class="btn btn-sm btn-outline" onclick="this.closest('.accordion-review-item').remove()" style="color:#dc2626; border-color:#fca5a5; padding:2px 8px; font-size:0.78rem;">🗑️ Delete</button>
        </div>
        <textarea name="reviews[${idx}][quote]" rows="2" placeholder="Customer Quote / Testimonial..." style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid #ccc; font-size:0.85rem; font-family:inherit;"></textarea>
    `;
    list.appendChild(div);
};

window.addAccordionFaqItem = function() {
    const list = document.getElementById('accordion-faqs-list');
    if (!list) return;
    const idx = list.querySelectorAll('.accordion-faq-item').length;
    const div = document.createElement('div');
    div.className = 'accordion-faq-item';
    div.style.cssText = 'background:#FAF8FF; padding:12px; border-radius:10px; border:1px solid #e9d5ff; display:flex; flex-direction:column; gap:8px;';
    div.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
            <input type="text" name="faqs[${idx}][q]" value="" placeholder="Question (e.g. 🎂 Do you offer vegan cakes?)" style="flex:1; padding:6px 10px; border-radius:6px; border:1px solid #ccc; font-weight:700;">
            <button type="button" class="btn btn-sm btn-outline" onclick="this.closest('.accordion-faq-item').remove()" style="color:#dc2626; border-color:#fca5a5; padding:2px 8px; font-size:0.78rem;">🗑️ Delete</button>
        </div>
        <textarea name="faqs[${idx}][a]" rows="2" placeholder="Answer / Bakery Policy..." style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid #ccc; font-size:0.85rem; font-family:inherit;"></textarea>
    `;
    list.appendChild(div);
};

// Media Upload Handler for Section Backgrounds
window.uploadSectionMedia = function(fileInput, targetInputId) {
    if (!fileInput.files || !fileInput.files[0]) return;
    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append('file', file);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    window.showToast('Uploading background media...', 'info');

    fetch('/dashboard/upload-media', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const target = document.getElementById(targetInputId);
            if (target) {
                target.value = data.url;
            }
            window.showToast('Media uploaded & linked successfully!', 'success');
        } else {
            window.showToast(data.message || 'Upload failed.', 'error');
        }
    })
    .catch(err => {
        window.showToast('Upload error: ' + err.message, 'error');
    });
};

// Trust Highlight Icon Picker Modal
window.activeIconTargetInput = null;
window.openIconPicker = function(inputEl) {
    window.activeIconTargetInput = inputEl;
    let modal = document.getElementById('icon-picker-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'icon-picker-modal';
        modal.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:99999; display:flex; justify-content:center; align-items:center;';
        modal.innerHTML = `
            <div style="background:white; padding:24px; border-radius:16px; max-width:400px; width:90%; box-shadow:0 10px 30px rgba(0,0,0,0.2); text-align:center;">
                <h4 style="margin:0 0 14px 0; color:#4c1d95;">🎨 Select Bakery Highlight Icon</h4>
                <div style="display:grid; grid-template-columns:repeat(6, 1fr); gap:10px; font-size:1.6rem; margin-bottom:18px;">
                    ${['🎂', '🚚', '📦', '💖', '🍰', '🧁', '📜', '🚗', '⭐', '🍪', '🏆', '👩‍🍳', '🥖', '🍓', '🎁', '📍', '⏰', '💳', '🛡️', '🎈', '💐', '🥂', '🎉', '🥐'].map(icon => `
                        <button type="button" onclick="selectPickedIcon('${icon}')" style="background:#f5f3ff; border:1px solid #ddd6fe; border-radius:10px; padding:8px; cursor:pointer; font-size:1.5rem; transition:transform 0.1s ease;" onmouseover="this.style.transform='scale(1.15)'" onmouseout="this.style.transform='scale(1)'">${icon}</button>
                    `).join('')}
                </div>
                <button type="button" onclick="document.getElementById('icon-picker-modal').style.display='none'" class="btn btn-outline btn-sm">Cancel</button>
            </div>
        `;
        document.body.appendChild(modal);
    } else {
        modal.style.display = 'flex';
    }
};

window.selectPickedIcon = function(icon) {
    if (window.activeIconTargetInput) {
        window.activeIconTargetInput.value = icon;
    }
    const modal = document.getElementById('icon-picker-modal');
    if (modal) modal.style.display = 'none';
};

window.moveSectionUp = function(btn) {
    const row = btn.closest('.section-manager-row');
    if (row && row.previousElementSibling) {
        row.parentNode.insertBefore(row, row.previousElementSibling);
        updateSectionOrderInputs();
    }
};

window.moveSectionDown = function(btn) {
    const row = btn.closest('.section-manager-row');
    if (row && row.nextElementSibling) {
        row.parentNode.insertBefore(row.nextElementSibling, row);
        updateSectionOrderInputs();
    }
};

function updateSectionOrderInputs() {
    const rows = document.querySelectorAll('#section-manager-list .section-manager-row');
    rows.forEach((row, idx) => {
        const orderInput = row.querySelector('.section-order-input');
        if (orderInput) {
            orderInput.value = idx + 1;
        }
    });
}

window.saveSectionManagerForm = function() {
    updateSectionOrderInputs();
    const form = document.getElementById('section-manager-form');
    if (!form) return;

    const formData = new FormData(form);
    const msgEl = document.getElementById('section-manager-msg');

    fetch('/dashboard/sections', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (msgEl) {
                msgEl.style.display = 'block';
                msgEl.innerHTML = `✅ ${data.message} <a href="/" target="_blank" style="color:#4c1d95; font-weight:700; text-decoration:underline; margin-left:8px;">View Live Site ↗</a>`;
                setTimeout(() => { msgEl.style.display = 'none'; }, 4000);
            }
            window.showToast('Section order & visibility saved successfully!', 'success');
        } else {
            window.showToast(data.message || 'Failed to save section settings.', 'error');
        }
    })
    .catch(err => {
        window.showToast('Error saving sections: ' + err.message, 'error');
    });
};

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

    const modal = document.getElementById('order-modal-popup');
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeOrderModal();
            }
        });
    }
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

    // Step 12: Order Submission with Server AJAX & SMTP Email
    const orderForm = document.getElementById('order-form');
    if (orderForm) {
        orderForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const clientName = document.getElementById('contact-name').value.trim();
            const clientEmail = document.getElementById('contact-email').value.trim();
            const clientPhone = document.getElementById('contact-phone').value.trim();
            const submitBtn = document.getElementById('submit-form-btn');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value || '';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerText = '⏳ Submitting & Sending Email...';
            }

            // Collect notes and allergies
            const notesVal = document.getElementById('order-notes')?.value || '';
            const allergiesVal = document.getElementById('order-allergies')?.value || '';

            // Selected Flavors, Frosting, Fillings
            const selectedFlavors = Array.from(document.querySelectorAll('#flavor-list .product.selected')).map(el => el.innerText.trim());
            const selectedFrosting = Array.from(document.querySelectorAll('#frosting-list .product.selected')).map(el => el.innerText.trim());
            const selectedFillings = Array.from(document.querySelectorAll('#filling-list .product.selected')).map(el => el.innerText.trim());
            const selectedSocials = Array.from(document.querySelectorAll('#social-grid .product.selected')).map(el => el.innerText.trim());

            // Selected Time slot radio
            const selectedTimeSlot = document.querySelector('input[name="order-time"]:checked')?.value || '9:30 AM';
            const deliveryAddressVal = document.getElementById('delivery-address')?.value || '';

            const formData = new FormData();
            formData.append('client_name', clientName);
            formData.append('client_email', clientEmail);
            formData.append('client_phone', clientPhone);
            formData.append('due_date', state.selectedDate || '');
            formData.append('time_slot', selectedTimeSlot);
            formData.append('fulfillment_type', state.fulfillment || 'pickup');
            formData.append('delivery_address', deliveryAddressVal);
            formData.append('special_notes', notesVal);
            formData.append('allergies', allergiesVal);
            formData.append('total_price', state.total || 0);

            formData.append('items', JSON.stringify(state.selectedProducts || []));
            formData.append('flavors', JSON.stringify(selectedFlavors || []));
            formData.append('frosting', JSON.stringify(selectedFrosting || []));
            formData.append('fillings', JSON.stringify(selectedFillings || []));
            formData.append('social_follows', JSON.stringify(selectedSocials || []));

            if (state.uploadedFiles && state.uploadedFiles.length > 0) {
                state.uploadedFiles.forEach((file) => {
                    formData.append('inspiration_files[]', file);
                });
            }

            fetch('/order', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('form-container-toggle').style.display = 'none';
                    document.getElementById('thank-you-container').style.display = 'block';

                    const newOrderObj = data.order || {
                        order_number: data.order_number,
                        client_name: clientName,
                        client_email: clientEmail,
                        client_phone: clientPhone,
                        due_date: state.selectedDate || '2026-07-28',
                        time_slot: selectedTimeSlot,
                        fulfillment_type: state.fulfillment,
                        items: state.selectedProducts,
                        total_price: state.total,
                        deposit_amount: state.deposit,
                        status: 'new'
                    };
                    appendOrderToAdminQueue(newOrderObj);
                } else {
                    alert('Error submitting order: ' + (data.message || 'Validation failed.'));
                }
            })
            .catch(err => {
                console.error('Order Submit Error:', err);
                alert('An error occurred while submitting your order.');
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Submit Order';
                }
            });
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

    // Calculate option add-on prices across selected chips
    let optionAddons = 0;
    document.querySelectorAll('.product.selected[data-addon-price]').forEach(el => {
        const addon = parseFloat(el.dataset.addonPrice || 0);
        if (!isNaN(addon) && addon > 0) {
            optionAddons += addon;
        }
    });

    // Calculate option add-on prices across selects
    document.querySelectorAll('select.custom-step-select').forEach(sel => {
        const selectedOpt = sel.options[sel.selectedIndex];
        if (selectedOpt && selectedOpt.dataset && selectedOpt.dataset.addonPrice) {
            const addon = parseFloat(selectedOpt.dataset.addonPrice || 0);
            if (!isNaN(addon) && addon > 0) {
                optionAddons += addon;
            }
        }
    });

    state.total = Math.max(0, state.subtotal + optionAddons - state.discounts);
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
        summaryEl.innerHTML = `Items: ${state.selectedProducts.length} <br> <strong>Total: $${state.total.toFixed(2)}</strong>`;
    }

    if (globalTotalEl) {
        globalTotalEl.innerText = `$${state.total.toFixed(2)}`;
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
        const card = e.target.closest('.product, .option-chip');
        if (!card) return;
        card.classList.toggle('selected');
        const name = card.dataset.cleanName || card.dataset.name || card.innerText.trim();
        
        if (targetArray) {
            if (card.classList.contains('selected')) {
                if (!targetArray.includes(name)) targetArray.push(name);
            } else {
                const idx = targetArray.indexOf(name);
                if (idx > -1) targetArray.splice(idx, 1);
            }
        }

        updateCartSummary();
    });
}

// Universal Chip & Option Click Delegation for Custom Steps
document.addEventListener('click', (e) => {
    const card = e.target.closest('.option-chip, .option-chip-grid .product, [id*="custom-chip-list"] .product');
    if (!card) return;

    // Skip grids with custom click handlers
    if (card.closest('#product-grid, #fulfillment-grid, #social-grid, #flavor-list, #frosting-list, #filling-list')) return;

    card.classList.toggle('selected');
    updateCartSummary();
});

let currentCalYear = new Date().getFullYear();
let currentCalMonth = new Date().getMonth();

function renderInteractiveCalendar() {
    const calGrid = document.getElementById('interactive-calendar-grid');
    if (!calGrid) return;

    calGrid.innerHTML = '';
    
    // Create Header for Month Navigation if it doesn't exist
    let calHeader = document.getElementById('interactive-calendar-header');
    if (!calHeader) {
        calHeader = document.createElement('div');
        calHeader.id = 'interactive-calendar-header';
        calHeader.style.cssText = 'display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; font-weight:600;';
        calGrid.parentNode.insertBefore(calHeader, calGrid);
    }
    
    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    calHeader.innerHTML = `
        <button class="btn btn-outline btn-sm" id="prev-month-btn" style="padding:4px 10px; border-radius:6px;">&larr;</button>
        <span style="font-size:1.1rem; color:var(--dark-text);">${monthNames[currentCalMonth]} ${currentCalYear}</span>
        <button class="btn btn-outline btn-sm" id="next-month-btn" style="padding:4px 10px; border-radius:6px;">&rarr;</button>
    `;
    
    document.getElementById('prev-month-btn').addEventListener('click', (e) => {
        e.preventDefault();
        currentCalMonth--;
        if (currentCalMonth < 0) { currentCalMonth = 11; currentCalYear--; }
        renderInteractiveCalendar();
    });
    
    document.getElementById('next-month-btn').addEventListener('click', (e) => {
        e.preventDefault();
        currentCalMonth++;
        if (currentCalMonth > 11) { currentCalMonth = 0; currentCalYear++; }
        renderInteractiveCalendar();
    });

    const daysInMonth = new Date(currentCalYear, currentCalMonth + 1, 0).getDate();
    const firstDayIndex = new Date(currentCalYear, currentCalMonth, 1).getDay(); // 0 = Sunday

    const bSettings = window._serverBookingSettings || {};
    const leadTimeEnabled = bSettings.lead_time_enabled ?? (localStorage.getItem('lead_time_enabled') !== '0');
    const leadTimeDays = parseInt(bSettings.lead_time_days ?? (localStorage.getItem('lead_time_days') || 3));

    const customBlocked = window.adminCalState?.blockedDates
        ? Array.from(window.adminCalState.blockedDates)
        : (bSettings.blocked_dates || []);

    const recurringClosed = window.adminCalState?.recurringClosedDays || bSettings.recurring_closed_days || [0, 1];

    const today = new Date();
    today.setHours(0,0,0,0);
    const leadCutoff = new Date(today);
    
    // Toggle ON (leadTimeEnabled = true) means standard 3 days. 
    // Toggle OFF means custom number of days.
    const actualLeadDays = leadTimeEnabled ? 3 : leadTimeDays;
    leadCutoff.setDate(today.getDate() + actualLeadDays);

    // Add empty slots for days before the 1st of the month
    for (let i = 0; i < firstDayIndex; i++) {
        const emptyEl = document.createElement('div');
        emptyEl.className = 'cal-day empty';
        emptyEl.style.visibility = 'hidden';
        calGrid.appendChild(emptyEl);
    }

    for (let day = 1; day <= daysInMonth; day++) {
        const dateObj = new Date(currentCalYear, currentCalMonth, day);
        const dayOfWeek = dateObj.getDay();
        const dateStr = `${currentCalYear}-${String(currentCalMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

        const isCustomBlocked = customBlocked.includes(dateStr);
        const isRecurringClosed = recurringClosed.includes(dayOfWeek);
        
        let isLeadTimeBlocked = false;
        if (dateObj < leadCutoff && dateObj >= today) {
            isLeadTimeBlocked = true;
        }
        
        // Also block days in the past
        let isPast = false;
        if (dateObj < today) {
            isPast = true;
        }

        const isBooked = isCustomBlocked || isRecurringClosed || isLeadTimeBlocked || isPast;

        const dayEl = document.createElement('div');
        dayEl.className = `cal-day ${isBooked ? 'booked' : ''}`;
        
        if (isPast) dayEl.title = 'Past Date';
        else if (isLeadTimeBlocked) dayEl.title = `Unavailable (${actualLeadDays}-day lead time required)`;
        else if (isRecurringClosed && !isCustomBlocked) dayEl.title = 'Closed (Weekly)';
        else if (isCustomBlocked) dayEl.title = 'Blocked Date';
        
        dayEl.innerText = day;
        
        // If this date was previously selected, mark it
        if (state.selectedDate === dateStr) {
            dayEl.classList.add('selected');
        }

        if (!isBooked) {
            dayEl.addEventListener('click', () => {
                document.querySelectorAll('#interactive-calendar-grid .cal-day').forEach(d => d.classList.remove('selected'));
                dayEl.classList.add('selected');
                state.selectedDate = dateStr;
                const selDateEl = document.getElementById('selected-date');
                if (selDateEl) selDateEl.innerText = state.selectedDate;
                
                const stepSec = dayEl.closest('.step');
                if (stepSec) {
                    const nextBtn = stepSec.querySelector('.next-btn');
                    if (nextBtn) nextBtn.disabled = false;
                }
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

            if (btn.dataset.tab === 'tab-calendar' && window.initAdminCalendarUI) {
                window.initAdminCalendarUI();
            }

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
    // Device Gallery File Preview & Drag-and-Drop Handler
    const galFileInput = document.getElementById('gal-image-file');
    const galDropzone = document.getElementById('gal-device-dropzone');
    const galPreviewWrap = document.getElementById('gal-upload-preview');
    const galPreviewImg = document.getElementById('gal-preview-img');
    const galDropText = document.getElementById('gal-dropzone-text');

    if (galFileInput && galPreviewImg) {
        const updatePreview = (file) => {
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    galPreviewImg.src = ev.target.result;
                    if (galPreviewWrap) galPreviewWrap.style.display = 'block';
                    if (galDropText) galDropText.innerHTML = `✅ Selected: <strong>${file.name}</strong>`;
                };
                reader.readAsDataURL(file);
            }
        };

        galFileInput.addEventListener('change', (e) => {
            if (e.target.files && e.target.files[0]) {
                updatePreview(e.target.files[0]);
            }
        });

        if (galDropzone) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                galDropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                }, false);
            });

            galDropzone.addEventListener('dragover', () => {
                galDropzone.style.borderColor = '#5c1d37';
                galDropzone.style.background = '#fcebf1';
            });

            ['dragleave', 'drop'].forEach(eventName => {
                galDropzone.addEventListener(eventName, () => {
                    galDropzone.style.borderColor = '#e67399';
                    galDropzone.style.background = '#fff7fa';
                });
            });

            galDropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                if (dt.files && dt.files[0]) {
                    galFileInput.files = dt.files;
                    updatePreview(dt.files[0]);
                }
            });
        }
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
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            if (emailInput && emailInput.value.trim()) {
                const routingEmail = emailInput.value.trim();

                fetch('/dashboard/settings/email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: routingEmail })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (statusEl) {
                            statusEl.style.display = 'block';
                            statusEl.innerText = `✅ Order Notification Email saved live as: ${routingEmail}! All future form entries for your site will route here.`;
                        }
                        alert(`Success: Order notification routing email updated to ${routingEmail}!`);
                    } else {
                        alert('Error saving routing email: ' + (data.message || 'Validation failed.'));
                    }
                })
                .catch(err => {
                    console.error('Save Email Error:', err);
                    alert('An error occurred while saving order routing email.');
                });
            }
        });
    }

    // Dynamic Admin Option Builder Functions
    window.addAdminOptionRow = function(name = '', price = '') {
        const container = document.getElementById('option-rows-container');
        if (!container) return;

        const row = document.createElement('div');
        row.className = 'admin-option-item-row';
        row.style.cssText = 'display:flex; gap:10px; align-items:center; background:#fff; padding:8px 12px; border-radius:12px; border:1px solid #f0e4ea; box-shadow:0 2px 6px rgba(0,0,0,0.03);';
        
        const formattedPrice = (price !== '' && !isNaN(price)) ? parseFloat(price).toFixed(2) : '';

        row.innerHTML = `
            <div style="flex:2; display:flex; flex-direction:column;">
                <span style="font-size:0.75rem; font-weight:700; color:#5c1d37; margin-bottom:2px;">Option Name</span>
                <input type="text" class="admin-opt-name" value="${(name || '').replace(/"/g, '&quot;')}" placeholder="e.g. Chocolate" style="padding:10px 12px; border-radius:10px; border:1px solid #ddd; font-size:0.92rem;">
            </div>
            <div style="flex:1; display:flex; flex-direction:column;">
                <span style="font-size:0.75rem; font-weight:700; color:#e67399; margin-bottom:2px;">Extra Charge ($)</span>
                <div style="display:flex; align-items:center; background:#fff7fa; border:1px solid #f8c6d7; border-radius:10px; padding:0 10px; height:42px;">
                    <span style="color:#e67399; font-weight:700; margin-right:4px;">+$</span>
                    <input type="number" step="0.50" min="0" class="admin-opt-price" value="${formattedPrice}" placeholder="0.00" style="border:none; background:transparent; width:100%; outline:none; font-weight:700; color:#5c1d37; font-size:0.92rem;">
                </div>
            </div>
            <button type="button" class="btn btn-outline btn-sm" onclick="this.parentElement.remove()" style="color:#dc3545; border-color:#f8c6d7; padding:8px 12px; border-radius:10px; align-self:flex-end; height:42px;" title="Remove Option">✕</button>
        `;
        container.appendChild(row);
    };

    window.serializeAdminOptions = function() {
        const rows = document.querySelectorAll('.admin-option-item-row');
        const optionsArray = [];

        rows.forEach(row => {
            const nameInput = row.querySelector('.admin-opt-name');
            const priceInput = row.querySelector('.admin-opt-price');
            if (!nameInput) return;

            let name = nameInput.value.trim();
            if (!name) return;

            const price = parseFloat(priceInput?.value || 0);
            name = name.replace(/\(\+\$?([0-9]+(?:\.[0-9]{1,2})?)\)/gi, '').trim();

            if (!isNaN(price) && price > 0) {
                optionsArray.push(`${name} (+$${price.toFixed(2)})`);
            } else {
                optionsArray.push(name);
            }
        });

        const optionsStr = optionsArray.join(', ');
        const hiddenOptionsInput = document.getElementById('field-options');
        if (hiddenOptionsInput) {
            hiddenOptionsInput.value = optionsStr;
        }
        return optionsStr;
    };

    window.populateAdminOptionRows = function(optionsStr = '') {
        const container = document.getElementById('option-rows-container');
        if (!container) return;
        container.innerHTML = '';

        if (!optionsStr) {
            window.addAdminOptionRow('', '');
            window.addAdminOptionRow('', '');
            return;
        }

        const items = optionsStr.split(',').map(s => s.trim()).filter(Boolean);
        items.forEach(item => {
            let name = item;
            let price = '';

            const pregMatch = item.match(/\(\+\$?([0-9]+(?:\.[0-9]{1,2})?)\)/i);
            if (pregMatch) {
                price = pregMatch[1];
                name = item.replace(/\(\+\$?([0-9]+(?:\.[0-9]{1,2})?)\)/gi, '').trim();
            }

            window.addAdminOptionRow(name, price);
        });

        if (items.length === 0) {
            window.addAdminOptionRow('', '');
            window.addAdminOptionRow('', '');
        }
    };

    // Form Studio Schema & Field Builder Handler
    window.toggleOptionsRow = function(val) {
        const optRow = document.getElementById('field-options-row');
        if (optRow) {
            const needsOptions = ['select', 'flavors', 'frosting', 'fillings', 'social_discount'].includes(val);
            optRow.style.display = needsOptions ? 'block' : 'none';
            if (needsOptions) {
                const container = document.getElementById('option-rows-container');
                if (container && container.children.length === 0) {
                    window.populateAdminOptionRows('');
                }
            }
        }
    };
    setTimeout(() => toggleOptionsRow(document.getElementById('field-type')?.value || 'products'), 100);

    // Initialize Schema from Server Data
    if (window._serverFormSchema && Array.isArray(window._serverFormSchema) && window._serverFormSchema.length > 0) {
        window._customFields = window._serverFormSchema;
    } else {
        window._customFields = [
            { id: 'step_1', type: 'products', title: 'Build Your Order', subtext: 'Select items from our product catalog below', options: '', description: '' },
            { id: 'step_2', type: 'calendar', title: 'Select Your Date', subtext: 'Custom Order Booking', options: '', description: '' },
            { id: 'step_3', type: 'flavors', title: 'Choose Your Flavors', subtext: 'Select all that apply (Luxury flavors +$10)', options: 'Strawberry Bliss, Vanilla Bean, Chocolate Dream, Creamy Hazelnut, Confetti Explosion, Red Velvet, Swirly Marble, Lemon Drop, Golden Carrot, Raspberry Swirl', description: '' },
            { id: 'step_4', type: 'frosting', title: 'Select Frosting', subtext: 'Select your preferred frosting type', options: 'Vanilla buttercream, Cream Cheese, Strawberry buttercream, Oreo buttercream, Chocolate buttercream, Confetti buttercream, Whip Cream', description: '' },
            { id: 'step_5', type: 'fillings', title: 'Choice of Fillings?', subtext: 'Select all that apply', options: 'Fudge, Cookies and cream, Strawberries and cream, Peanut Butter, Nutella, Edible cookie dough, Lemon curd, Plain buttercream/frosting', description: '' },
            { id: 'step_6', type: 'textarea', title: 'Special Requests', subtext: 'Is there anything specific you want me to add or know about your order?', options: '', description: 'Type your notes here...' },
            { id: 'step_7', type: 'fulfillment', title: 'Fulfillment Options', subtext: 'Select pickup or delivery and your preferred time frame', options: '8:30 AM, 9:00 AM, 9:30 AM, 10:00 AM, 10:30 AM', description: '' },
            { id: 'step_8', type: 'allergies', title: 'Any Allergies?', subtext: 'By answering this question you understand any allergies not listed I will not be at fault for.', options: '', description: 'List any allergies here...' },
            { id: 'step_9', type: 'social_discount', title: 'Social Media Follows', subtext: '$5 off for social media you follow or join!', options: 'Instagram: (@Blushed_Crumbs), Facebook group: (Blushed Crumbs), TikTok: (@Blushed_Crumbs), Facebook page: (Blushed Crumbs)', description: '' },
            { id: 'step_10', type: 'file_upload', title: 'Inspiration Files', subtext: 'Have any pictures or designs you\'d like us to use for inspiration?', options: '', description: 'Supports PNG, JPG, JPEG' },
            { id: 'step_11', type: 'terms', title: 'Terms & Conditions', subtext: 'PLEASE READ BEFORE ACCEPTING ‼️‼️‼️', options: '', description: '' },
            { id: 'step_12', type: 'contact_info', title: 'Contact Information', subtext: 'Enter your details to finalize your order request', options: '', description: '' }
        ];
    }

    // Render Form Studio Fields Table
    function renderFieldsTable() {
        const tbody = document.getElementById('custom-fields-tbody');
        if (!tbody) return;

        if (window._customFields.length === 0) {
            tbody.innerHTML = `<tr class="empty-row" id="fields-empty-row">
                <td colspan="6" style="text-align:center; padding:32px; color:#aaa; font-size:0.95rem;">
                    No steps added yet. Use the form above to add your first step!
                </td>
            </tr>`;
            return;
        }

        const typeLabels = {
            'products': '🛒 Product Catalog',
            'calendar': '📅 Booking Calendar',
            'flavors': '🍰 Flavors Grid',
            'frosting': '🧁 Frosting Grid',
            'fillings': '🍫 Fillings Grid',
            'fulfillment': '🚚 Fulfillment & Time Slots',
            'allergies': '⚠️ Allergy Notice',
            'social_discount': '🎁 Social Discounts',
            'file_upload': '📎 Inspiration File Upload',
            'terms': '📜 Terms Agreement',
            'contact_info': '👤 Contact Info & Submit',
            'text': '📝 Single-Line Text',
            'textarea': '📄 Multi-line Textarea',
            'select': '☑️ Select Dropdown',
            'chips': '🏷️ Select Chips',
            'datepicker': '📅 Standard Date Picker',
            'toggle': '🔘 Yes/No Toggle'
        };

        tbody.innerHTML = window._customFields.map((f, i) => `
            <tr class="field-row" draggable="true" data-idx="${i}">
                <td class="drag-handle">⠿</td>
                <td style="color:#e67399; font-weight:800; font-size:0.95rem;">Step ${i + 1}</td>
                <td>
                    <strong style="color:#5c1d37; font-size:0.95rem; border-bottom:1px dashed #ccc; cursor:text; padding:2px;" contenteditable="true" onblur="updateField(${i}, 'title', this.innerText)" title="Click to edit title">${f.title || f.label || 'Step ' + (i+1)}</strong>
                    <br>
                    <span style="font-size:0.8rem; color:#888; border-bottom:1px dashed #ccc; cursor:text; padding:2px;" contenteditable="true" onblur="updateField(${i}, 'subtext', this.innerText)" title="Click to edit subtext">${f.subtext || 'Add subtext...'}</span>
                </td>
                <td><span style="background:#fff0f5; color:#5c1d37; font-weight:700; padding:4px 10px; border-radius:12px; border:1px solid #f8c6d7; font-size:0.8rem;">${typeLabels[f.type] || f.type}</span></td>
                <td style="color:#666; font-size:0.85rem; max-width:260px; word-wrap:break-word;">
                    ${['flavors', 'frosting', 'fillings', 'fulfillment', 'social_discount', 'select', 'chips'].includes(f.type) ?
                        `<span style="border-bottom:1px dashed #ccc; cursor:text; padding:2px; display:inline-block; min-width:50px;" contenteditable="true" onblur="updateField(${i}, 'options', this.innerText)" title="Edit Options (comma separated)">${f.options || '—'}</span>` :
                      (['textarea', 'allergies', 'file_upload', 'text', 'toggle'].includes(f.type) ?
                        `<span style="border-bottom:1px dashed #ccc; cursor:text; padding:2px; display:inline-block; min-width:50px;" contenteditable="true" onblur="updateField(${i}, 'description', this.innerText)" title="Edit Placeholder/Description">${f.description || '—'}</span>` :
                        `<span style="color:#aaa;">—</span>`
                      )
                    }
                </td>
                <td>
                    <div style="display:flex; gap:6px; align-items:center;">
                        <button class="reorder-btn" onclick="moveField(${i}, -1)" title="Move Up" ${i === 0 ? 'disabled' : ''}>↑</button>
                        <button class="reorder-btn" onclick="moveField(${i}, 1)" title="Move Down" ${i === window._customFields.length - 1 ? 'disabled' : ''}>↓</button>
                        <button class="delete-field-btn" onclick="deleteField(${i})" title="Remove Step">✕</button>
                    </div>
                </td>
            </tr>
        `).join('');

        // Drag-and-drop reorder
        const rows = tbody.querySelectorAll('.field-row');
        let dragSrc = null;
        rows.forEach(row => {
            row.addEventListener('dragstart', () => { dragSrc = row; row.style.opacity = '0.5'; });
            row.addEventListener('dragend', () => { row.style.opacity = '1'; });
            row.addEventListener('dragover', e => { e.preventDefault(); });
            row.addEventListener('drop', e => {
                e.preventDefault();
                if (dragSrc === row) return;
                const from = parseInt(dragSrc.dataset.idx);
                const to = parseInt(row.dataset.idx);
                const moved = window._customFields.splice(from, 1)[0];
                window._customFields.splice(to, 0, moved);
                renderFieldsTable();
            });
        });
    }

    window.moveField = function(idx, dir) {
        const arr = window._customFields;
        const newIdx = idx + dir;
        if (newIdx < 0 || newIdx >= arr.length) return;
        [arr[idx], arr[newIdx]] = [arr[newIdx], arr[idx]];
        renderFieldsTable();
    };

    window.deleteField = function(idx) {
        if (window._customFields.length <= 1) {
            alert('Your order form must have at least 1 step!');
            return;
        }
        window._customFields.splice(idx, 1);
        renderFieldsTable();
    };

    window.updateField = function(idx, key, value) {
        if (value === '—' || value === 'Add subtext...') value = '';
        window._customFields[idx][key] = value.trim();
    };

    // Save Form Schema to Server Endpoint
    window.saveFormSchemaToServer = function() {
        const saveBtn = document.getElementById('save-form-schema-btn');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerText = '⏳ Saving Form Steps...';
        }

        fetch('/dashboard/form-builder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ schema: window._customFields })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Success: Order form steps & layout saved live to your storefront!');
            } else {
                alert('Error saving form layout: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error('Save Schema Error:', err);
            alert('An error occurred while saving form layout.');
        })
        .finally(() => {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerText = '💾 Save Order Form Layout Live';
            }
        });
    };

    // Add Step Form Handler
    const fieldForm = document.getElementById('add-field-form');
    if (fieldForm) {
        fieldForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const labelText = document.getElementById('field-label').value.trim();
            const fieldType = document.getElementById('field-type').value;
            
            // Serialize dynamic option rows with extra charges
            const optionsText = window.serializeAdminOptions();
            const descriptionText = (document.getElementById('field-description')?.value || '').trim();

            if (!labelText) return;

            const fieldEntry = {
                id: 'step_' + Date.now(),
                title: labelText,
                subtext: descriptionText,
                type: fieldType,
                options: optionsText,
                description: ''
            };

            window._customFields.push(fieldEntry);
            renderFieldsTable();

            fieldForm.reset();
            window.populateAdminOptionRows('');
            toggleOptionsRow('products');
            alert(`Step "${labelText}" added! Click "Save Order Form Layout Live" to update your storefront.`);
        });
    }

    // Init table on load
    renderFieldsTable();

    // Build a field's HTML based on type template
    function buildFieldHTML(f) {
        const opts = (f.options || '').split(',').map(o => o.trim()).filter(Boolean);

        // Helper: description paragraph shown under label
        const descHtml = f.description ? `<p style="font-size:0.88rem; color:#888; margin:-4px 0 12px 0;">${f.description}</p>` : '';

        switch (f.type) {
            case 'products': {
                // Grab products from DOM or registry
                const domProducts = Array.from(document.querySelectorAll('#product-grid .product'))
                    .map(el => ({
                        name: el.querySelector('strong')?.textContent || el.textContent.trim().split('\n')[0].trim(),
                        price: el.dataset.price || ''
                    }));
                const catalog = (window._adminProductCatalog && window._adminProductCatalog.length)
                    ? window._adminProductCatalog
                    : domProducts;

                const tileHtml = catalog.map(p => `
                    <div class="product" data-name="${p.name}" data-price="${p.price}"
                        style="cursor:pointer; padding:14px 10px; border-radius:14px; border:1.5px solid #f8c6d7; background:#fff; text-align:center; font-size:0.88rem; transition: all 0.18s;"
                        onclick="toggleProductTile(this)">
                        <strong style="display:block; margin-bottom:4px; font-size:0.92rem;">${p.name}</strong>
                        <span style="color:#e67399; font-weight:700;">$${p.price}</span>
                    </div>
                `).join('');

                return `<div class="custom-baker-field">
                    <label>${f.label}</label>
                    <div id="custom-product-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap:12px; margin-top:10px;">
                        ${tileHtml}
                    </div>
                </div>`;
            }

            case 'textarea':
                return `<div class="custom-baker-field">
                    <label>${f.label}</label>
                    ${descHtml}
                    <textarea placeholder="Type your answer here..." style="width:100%; height:110px; padding:12px; border-radius:10px; border:1px solid #ccc; font-family:inherit; resize:vertical;"></textarea>
                </div>`;

            case 'select':
                return `<div class="custom-baker-field">
                    <label>${f.label}</label>
                    <select style="width:100%; padding:12px; border-radius:10px; border:1px solid #ccc; font-family:inherit;">
                        <option value="" disabled selected>Select an option…</option>
                        ${opts.map(o => `<option value="${o}">${o}</option>`).join('')}
                    </select>
                </div>`;

            case 'chips':
                return `<div class="custom-baker-field">
                    <label>${f.label}</label>
                    <div style="display:flex; flex-wrap:wrap; gap:10px; justify-content:center; margin-top:8px;">
                        ${opts.map(o => `<div class="product" style="cursor:pointer; padding:10px 18px; border-radius:50px; border:2px solid #f8c6d7; font-size:0.9rem; font-weight:600; background:#fff;" onclick="this.classList.toggle('selected')">${o}</div>`).join('')}
                    </div>
                </div>`;

            case 'file':
                return `<div class="custom-baker-field">
                    <label>${f.label}</label>
                    <div class="upload-dropzone" style="border:2px dashed #e67399; background:#fff7fa; padding:30px 20px; border-radius:16px; text-align:center; cursor:pointer;" onclick="this.querySelector('input[type=file]').click()">
                        <span style="font-size:2.5rem; color:#e67399; display:block; margin-bottom:8px;">✦</span>
                        <p style="font-size:1rem; font-weight:600; color:#5c1d37; margin-bottom:4px;">Drag &amp; drop files here or <strong>click to browse</strong></p>
                        <span style="font-size:12px; color:#888;">Supports PNG, JPG, JPEG, PDF</span>
                        <input type="file" multiple accept="image/*" style="display:none;">
                    </div>
                </div>`;

            case 'datepicker':
                return `<div class="custom-baker-field">
                    <label>${f.label}</label>
                    <div style="background:#fff; border:1px solid #e0c5d1; border-radius:14px; padding:16px;">
                        <input type="date" style="width:100%; padding:12px; border-radius:10px; border:1px solid #ccc; font-family:inherit; font-size:0.95rem;">
                    </div>
                </div>`;

            case 'toggle':
                return `<div class="custom-baker-field">
                    <label>${f.label}</label>
                    ${descHtml}
                    <div style="display:flex; gap:14px; margin-top:8px; justify-content:center;">
                        <div class="product" style="cursor:pointer; padding:12px 32px; border-radius:50px; font-weight:700;" onclick="this.classList.add('selected'); this.nextElementSibling.classList.remove('selected')">Yes</div>
                        <div class="product" style="cursor:pointer; padding:12px 32px; border-radius:50px; font-weight:700;" onclick="this.classList.add('selected'); this.previousElementSibling.classList.remove('selected')">No</div>
                    </div>
                </div>`;

            default: // text
                return `<div class="custom-baker-field">
                    <label>${f.label}</label>
                    ${descHtml}
                    <input type="text" placeholder="Type your answer here..." style="width:100%; padding:12px; border-radius:10px; border:1px solid #ccc; font-family:inherit;">
                </div>`;
        }
    }

    // Star rating hover helpers (exposed globally)
    window.hoverStars = function(el) {
        const val = parseInt(el.dataset.val);
        el.parentElement.querySelectorAll('.star').forEach((s, i) => {
            s.style.color = i < val ? '#e67399' : '#ddd';
        });
    };
    window.unhoverStars = function(el) {
        const selected = el.parentElement.querySelector('.star.active');
        const val = selected ? parseInt(selected.dataset.val) : 0;
        el.parentElement.querySelectorAll('.star').forEach((s, i) => {
            s.style.color = i < val ? '#e67399' : '#ddd';
        });
    };
    window.selectStars = function(el) {
        const val = parseInt(el.dataset.val);
        el.parentElement.querySelectorAll('.star').forEach((s, i) => {
            s.classList.toggle('active', i < val);
            s.style.color = i < val ? '#e67399' : '#ddd';
        });
    };

    // Product tile toggle helper (used by Product Selection field)
    window.toggleProductTile = function(el) {
        el.classList.toggle('selected');
    };

    // Rebuild all custom fields in the live form
    function rebuildLiveForm() {
        // Remove the custom step section entirely, then recreate it
        const existing = document.getElementById('step-custom-fields');
        if (existing) existing.remove();

        if (window._customFields.length === 0) return;

        // Create a dedicated custom step that sits before the contact step (step-12)
        const customStep = document.createElement('section');
        customStep.className = 'step';
        customStep.id = 'step-custom-fields';
        customStep.innerHTML = `
            <h2>A Few More Details</h2>
            <p style="text-align:center; margin-bottom:20px; font-size:0.9rem; color:#888;">Help us make your order perfect!</p>
            <div id="custom-fields-container"></div>
            <div class="nav-buttons cart-bar">
                <button class="back-btn" id="back-custom">Back</button>
                <button class="next-btn" id="next-custom">Continue</button>
            </div>
        `;

        // Inject each field into the container
        const container = customStep.querySelector('#custom-fields-container');
        window._customFields.forEach(f => {
            const wrapper = document.createElement('div');
            wrapper.innerHTML = buildFieldHTML(f);
            container.appendChild(wrapper.firstElementChild);
        });

        // Insert before step-12 (Contact Info)
        const step12 = document.getElementById('step-12');
        const formContainer = document.getElementById('form-container-toggle');
        if (step12 && formContainer) {
            formContainer.insertBefore(customStep, step12);
        } else if (formContainer) {
            formContainer.appendChild(customStep);
        }

        // Wire up the back/next buttons for this step dynamically
        const backBtn = customStep.querySelector('#back-custom');
        const nextBtn = customStep.querySelector('#next-custom');
        const prevStep = document.getElementById('step-11');
        if (backBtn && prevStep) {
            backBtn.addEventListener('click', () => {
                customStep.classList.remove('active');
                prevStep.classList.add('active');
            });
        }
        if (nextBtn && step12) {
            nextBtn.addEventListener('click', () => {
                customStep.classList.remove('active');
                step12.classList.add('active');
            });
        }
        // Also patch step-11's continue button to go to custom step instead of step-12
        const step11Next = document.getElementById('to-step-12');
        if (step11Next) {
            step11Next.onclick = (e) => {
                e.preventDefault();
                const s11 = document.getElementById('step-11');
                if (s11) s11.classList.remove('active');
                customStep.classList.add('active');
            };
        }
    }


    // Add Product Form
    const prodForm = document.getElementById('add-product-form');
    if (prodForm) {
        prodForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const name = document.getElementById('new-prod-name').value;
            const price = parseFloat(document.getElementById('new-prod-price').value);
            let category = document.getElementById('new-prod-category').value;
            if (category === 'custom_new') {
                category = document.getElementById('new-prod-category-custom').value;
                if (!category) {
                    alert('Please enter a custom category name.');
                    return;
                }
            }

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
                row.style.cssText = 'display:flex; justify-content:space-between; align-items:center; padding:13px 16px; border-bottom:1px solid #f0e4ea;';
                row.innerHTML = `
                    <div>
                        <strong style="color:#5c1d37;">${name}</strong>
                        <span style="background:#f9e0eb; color:#7a2b4a; font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:20px; margin-left:8px;">${category}</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:0.85rem; color:#999;">$</span>
                        <input type="number" class="price-input" value="${price.toFixed(2)}" style="width:80px;">
                        <button class="btn btn-sm btn-secondary" onclick="showToast('Price updated successfully!')">Save</button>
                        <button class="btn btn-sm btn-outline" style="color:#d9534f; border-color:#d9534f;" onclick="this.closest('.product-item-row').remove()">✕</button>
                    </div>
                `;
                adminGrid.prepend(row);
            }

            alert(`Product "${name}" added to order builder & live storefront!`);
            
            // Reset the form
            prodForm.reset();
            document.getElementById('new-prod-category-custom').style.display = 'none';
            document.getElementById('new-prod-category-custom').removeAttribute('required');
        });
    }

    // Add Gallery Image Form (Device Upload Supported via AJAX!)
    const galleryForm = document.getElementById('add-gallery-form');
    if (galleryForm) {
        galleryForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('gal-submit-btn');
            const fileInput = document.getElementById('gal-image-file');

            if (!fileInput || !fileInput.files || !fileInput.files[0]) {
                alert('Please select an image file to upload!');
                return;
            }

            const formData = new FormData(galleryForm);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerText = '⏳ Uploading Photo...';
            }

            fetch('/dashboard/gallery', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.item) {
                    const item = data.item;

                    // Prepend to Admin Gallery List
                    const adminGalleryList = document.getElementById('admin-gallery-list');
                    if (adminGalleryList) {
                        const adminItem = document.createElement('div');
                        adminItem.className = 'admin-gallery-item-row';
                        adminItem.dataset.id = item.id;
                        adminItem.style.cssText = 'display:flex; align-items:center; justify-content:space-between; background:white; padding:12px; border-radius:12px; margin-bottom:10px; box-shadow:0 4px 12px rgba(0,0,0,0.05);';
                        adminItem.innerHTML = `
                            <div style="display:flex; align-items:center; gap:15px;">
                                <img src="${item.image_url}" style="width:55px; height:55px; object-fit:cover; border-radius:10px;">
                                <div>
                                    <strong style="color:#5c1d37;">${item.title}</strong><br>
                                    <span style="font-size:0.8rem; color:#e67399; font-weight:600;">${item.category}</span>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline" style="color:#d9534f; border-color:#d9534f;" onclick="deleteGalleryItem(${item.id}, this)">Delete</button>
                        `;
                        adminGalleryList.prepend(adminItem);
                    }

                    // Prepend to Public Storefront Gallery Grid
                    const mainGalleryGrid = document.getElementById('public-gallery-grid');
                    if (mainGalleryGrid) {
                        // Remove empty placeholder message if present
                        const emptyMsg = mainGalleryGrid.querySelector('div[style*="grid-column"]');
                        if (emptyMsg) emptyMsg.remove();

                        const card = document.createElement('div');
                        card.className = 'gallery-card';
                        card.dataset.category = item.category;
                        card.dataset.id = item.id;
                        card.onclick = () => openLightbox(item.image_url, item.title);
                        card.innerHTML = `
                            <div class="gallery-card-img-wrap">
                                <img src="${item.image_url}" alt="${item.title}">
                            </div>
                            <div class="gallery-card-info">
                                <h4>${item.title}</h4>
                                <span class="gallery-tag">${item.category}</span>
                            </div>
                        `;
                        mainGalleryGrid.prepend(card);
                    }

                    alert(`Success: "${item.title}" published live to your gallery!`);
                    galleryForm.reset();
                    if (galPreviewWrap) galPreviewWrap.style.display = 'none';
                    if (galDropText) galDropText.innerText = 'Click to select photo from device or drag image here';
                } else {
                    alert('Upload Error: ' + (data.message || 'Could not upload image. Please try again.'));
                }
            })
            .catch(err => {
                console.error('Gallery Upload Error:', err);
                alert('An error occurred while uploading. Please check the file format/size and try again.');
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = '🚀 Publish Photo to Live Gallery';
                }
            });
        });
    }

    // Global Delete Gallery Item Function
    window.deleteGalleryItem = function(id, btnElement) {
        if (!confirm('Are you sure you want to delete this gallery photo?')) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        fetch('/dashboard/gallery/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Remove from Admin Dashboard List
                const row = btnElement ? btnElement.closest('.admin-gallery-item-row') : document.querySelector(`.admin-gallery-item-row[data-id="${id}"]`);
                if (row) row.remove();

                // Remove from Public Gallery Grid
                const card = document.querySelector(`#public-gallery-grid .gallery-card[data-id="${id}"]`);
                if (card) card.remove();

                alert('Gallery photo deleted successfully.');
            } else {
                alert('Error: ' + (data.message || 'Could not delete gallery item.'));
            }
        })
        .catch(err => {
            console.error('Delete Gallery Error:', err);
            alert('An error occurred while deleting the gallery photo.');
        });
    };

    // Add Review Form
    const revForm = document.getElementById('add-review-form');
    if (revForm) {
        revForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const name = document.getElementById('rev-client-name').value;
            const text = document.getElementById('rev-text').value;

            fetch('/dashboard/reviews', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    client_name: name,
                    review_text: text,
                    rating: 5,
                    is_featured: true
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('Review published directly to storefront!');
                    window.location.reload();
                } else {
                    alert('Failed to save review.');
                }
            })
            .catch(err => {
                console.error('Error saving review:', err);
                alert('An error occurred.');
            });
        });
    }
}

window.generateInvoiceFromOrder = function(orderId, totalAmount, depositAmount) {
    const editInvId = document.getElementById('edit-invoice-id');
    const editOrderId = document.getElementById('edit-order-id');
    const editTotal = document.getElementById('edit-invoice-total');
    const editDeposit = document.getElementById('edit-invoice-deposit');
    const editNotes = document.getElementById('edit-invoice-notes');
    const modal = document.getElementById('invoice-edit-modal');

    if (editInvId) editInvId.value = '';
    if (editOrderId) editOrderId.value = orderId || '';
    if (editTotal) editTotal.value = parseFloat(totalAmount || 0).toFixed(2);
    if (editDeposit) editDeposit.value = parseFloat(depositAmount || (totalAmount * 0.5)).toFixed(2);
    if (editNotes) editNotes.value = '';
    if (modal) modal.style.display = 'flex';
};

window.sendInvoice = function(invoiceId) {
    if (!confirm('Are you sure you want to send this invoice to the client?')) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    fetch(`/dashboard/invoices/${invoiceId}/send`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Send Invoice Error:', err);
        alert('Failed to send invoice.');
    });
};

window.deleteInvoice = function(invoiceId, btnElement) {
    if (!confirm('Are you sure you want to delete this invoice? This action cannot be undone.')) return;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    
    fetch(`/dashboard/invoices/${invoiceId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (window.showToast) {
                window.showToast(data.message, 'success');
            } else {
                alert(data.message);
            }
            if (btnElement) {
                const tr = btnElement.closest('tr');
                if (tr) tr.remove();
            } else {
                location.reload();
            }
        } else {
            alert('Error deleting invoice: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Delete Invoice Error:', err);
        alert('Failed to delete invoice.');
    });
};



/* ============================================
   SETTINGS TAB — LEAD TIME, COLORS, TYPOGRAPHY
   ============================================ */

// ── Lead Time ──────────────────────────────
window.toggleLeadTimeInput = function(checkbox) {
    const wrapper = document.getElementById('custom-lead-days-wrapper');
    if (!wrapper) return;
    // When UNCHECKED (disabled), show the custom days input
    wrapper.style.display = checkbox.checked ? 'none' : 'block';
    
    // Auto-save when toggled to avoid users not knowing they need to save
    if (typeof saveLeadTime === 'function') saveLeadTime();
};

window.saveLeadTime = function() {
    const enabledInput = document.getElementById('lead-time-enabled');
    const daysInput = document.getElementById('custom-lead-days');
    const enabled = enabledInput ? enabledInput.checked : true;
    const days = daysInput ? parseInt(daysInput.value) : 3;

    window._serverBookingSettings = window._serverBookingSettings || {};
    window._serverBookingSettings.lead_time_enabled = enabled;
    window._serverBookingSettings.lead_time_days = days;

    localStorage.setItem('lead_time_days', days);
    localStorage.setItem('lead_time_enabled', enabled ? '1' : '0');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    fetch('/dashboard/settings/booking', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            lead_time_enabled: enabled,
            lead_time_days: days,
            recurring_closed_days: window.adminCalState ? window.adminCalState.recurringClosedDays : [0, 1],
            blocked_dates: window.adminCalState ? Array.from(window.adminCalState.blockedDates) : []
        })
    })
    .then(res => res.json())
    .then(data => {
        const msg = document.getElementById('lead-time-save-msg');
        if (msg) { msg.style.display = 'inline'; setTimeout(() => msg.style.display = 'none', 2500); }
        if (typeof renderInteractiveCalendar === 'function') renderInteractiveCalendar();
    })
    .catch(err => console.error('Save Lead Time Error:', err));
};

// ── Color Scheme ───────────────────────────
const COLOR_DEFAULTS = {
    primary:   '#e67399',
    secondary: '#5c1d37',
    accent:    '#fcebf1',
    text:      '#4a2133'
};

window.applyColorScheme = function() {
    const root = document.documentElement;
    const p = document.getElementById('cs-primary')?.value   || COLOR_DEFAULTS.primary;
    const s = document.getElementById('cs-secondary')?.value || COLOR_DEFAULTS.secondary;
    const a = document.getElementById('cs-accent')?.value    || COLOR_DEFAULTS.accent;
    const t = document.getElementById('cs-text')?.value      || COLOR_DEFAULTS.text;

    root.style.setProperty('--primary',    p);
    root.style.setProperty('--primary-hover', shadeColor(p, -15));
    root.style.setProperty('--pink-bg',    a);
    root.style.setProperty('--dark-text',  t);
    root.style.setProperty('--burgundy-bg', s);

    // Sync hex text inputs
    syncHex('cs-primary',   p);
    syncHex('cs-secondary', s);
    syncHex('cs-accent',    a);
    syncHex('cs-text',      t);
};

window.syncColorFromText = function(colorId, hexId) {
    const hexInput = document.getElementById(hexId);
    const colorInput = document.getElementById(colorId);
    if (!hexInput || !colorInput) return;
    const val = hexInput.value.trim();
    if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
        colorInput.value = val;
        applyColorScheme();
    }
};

function syncHex(colorId, value) {
    const hexInput = document.getElementById(colorId + '-hex');
    if (hexInput && hexInput !== document.activeElement) hexInput.value = value;
}

function shadeColor(hex, percent) {
    let R = parseInt(hex.slice(1,3), 16);
    let G = parseInt(hex.slice(3,5), 16);
    let B = parseInt(hex.slice(5,7), 16);
    R = Math.max(0, Math.min(255, R + percent));
    G = Math.max(0, Math.min(255, G + percent));
    B = Math.max(0, Math.min(255, B + percent));
    return `#${R.toString(16).padStart(2,'0')}${G.toString(16).padStart(2,'0')}${B.toString(16).padStart(2,'0')}`;
}

window.saveColorScheme = function() {
    const scheme = {
        primary:   document.getElementById('cs-primary')?.value,
        secondary: document.getElementById('cs-secondary')?.value,
        accent:    document.getElementById('cs-accent')?.value,
        text:      document.getElementById('cs-text')?.value
    };
    localStorage.setItem('site_color_scheme', JSON.stringify(scheme));
    const msg = document.getElementById('color-save-msg');
    if (msg) { msg.style.display = 'inline'; setTimeout(() => msg.style.display = 'none', 2500); }
};

window.resetColorScheme = function() {
    const d = COLOR_DEFAULTS;
    const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val; };
    setVal('cs-primary',    d.primary);
    setVal('cs-secondary',  d.secondary);
    setVal('cs-accent',     d.accent);
    setVal('cs-text',       d.text);
    applyColorScheme();
    localStorage.removeItem('site_color_scheme');
};

// Load saved colors on page load
(function loadSavedColors() {
    const saved = localStorage.getItem('site_color_scheme');
    if (!saved) return;
    try {
        const s = JSON.parse(saved);
        const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val; };
        if (s.primary)   setVal('cs-primary',   s.primary);
        if (s.secondary) setVal('cs-secondary',  s.secondary);
        if (s.accent)    setVal('cs-accent',     s.accent);
        if (s.text)      setVal('cs-text',       s.text);
        setTimeout(applyColorScheme, 100);
    } catch(e) {}
})();

// ── Typography ─────────────────────────────
const TYPO_DEFAULTS = {
    h1: { size: '3.2', unit: 'rem', color: '#5c1d37', font: "'Poppins', sans-serif" },
    h2: { size: '2',   unit: 'rem', color: '#5c1d37', font: "'Poppins', sans-serif" },
    h3: { size: '1.35',unit: 'rem', color: '#4a2133', font: "'Poppins', sans-serif" },
    p:  { size: '1',   unit: 'rem', color: '#666666', font: "'Poppins', sans-serif" }
};

window.applyTypography = function() {
    const styleId = 'typo-dynamic-style';
    let styleEl = document.getElementById(styleId);
    if (!styleEl) {
        styleEl = document.createElement('style');
        styleEl.id = styleId;
        document.head.appendChild(styleEl);
    }

    let css = '';
    ['h1','h2','h3','p'].forEach(tag => {
        const fontEl  = document.querySelector(`.typo-font[data-tag="${tag}"]`);
        const sizeEl  = document.querySelector(`.typo-size[data-tag="${tag}"]`);
        const unitEl  = document.querySelector(`.typo-unit[data-tag="${tag}"]`);
        const colorEl = document.querySelector(`.typo-color[data-tag="${tag}"]`);

        if (!fontEl) return;
        const font  = fontEl.value;
        const size  = (sizeEl?.value || TYPO_DEFAULTS[tag].size) + (unitEl?.value || TYPO_DEFAULTS[tag].unit);
        const color = colorEl?.value || TYPO_DEFAULTS[tag].color;

        // Sync hex text box
        const hexEl = document.querySelector(`.typo-color-hex[data-tag="${tag}"]`);
        if (hexEl && hexEl !== document.activeElement) hexEl.value = color;

        if (tag === 'p') {
            css += `body, p, span, li, td { font-family: ${font}; font-size: ${size}; color: ${color}; }\n`;
        } else {
            css += `${tag} { font-family: ${font}; font-size: ${size}; color: ${color}; }\n`;
        }
    });

    styleEl.textContent = css;
};

window.syncTypoColor = function(hexInput) {
    const tag = hexInput.dataset.tag;
    const val = hexInput.value.trim();
    if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
        const colorPicker = document.querySelector(`.typo-color[data-tag="${tag}"]`);
        if (colorPicker) colorPicker.value = val;
        applyTypography();
    }
};

window.saveTypography = function() {
    const data = {};
    ['h1','h2','h3','p'].forEach(tag => {
        data[tag] = {
            font:  document.querySelector(`.typo-font[data-tag="${tag}"]`)?.value,
            size:  document.querySelector(`.typo-size[data-tag="${tag}"]`)?.value,
            unit:  document.querySelector(`.typo-unit[data-tag="${tag}"]`)?.value,
            color: document.querySelector(`.typo-color[data-tag="${tag}"]`)?.value,
        };
    });
    localStorage.setItem('site_typography', JSON.stringify(data));
    const msg = document.getElementById('typo-save-msg');
    if (msg) { msg.style.display = 'inline'; setTimeout(() => msg.style.display = 'none', 2500); }
};

window.resetTypography = function() {
    ['h1','h2','h3','p'].forEach(tag => {
        const d = TYPO_DEFAULTS[tag];
        const fontEl  = document.querySelector(`.typo-font[data-tag="${tag}"]`);
        const sizeEl  = document.querySelector(`.typo-size[data-tag="${tag}"]`);
        const unitEl  = document.querySelector(`.typo-unit[data-tag="${tag}"]`);
        const colorEl = document.querySelector(`.typo-color[data-tag="${tag}"]`);
        if (fontEl)  fontEl.value  = d.font;
        if (sizeEl)  sizeEl.value  = d.size;
        if (unitEl)  unitEl.value  = d.unit;
        if (colorEl) colorEl.value = d.color;
    });
    applyTypography();
    localStorage.removeItem('site_typography');
};

// Load saved typography on page load
(function loadSavedTypography() {
    const saved = localStorage.getItem('site_typography');
    if (!saved) return;
    try {
        const data = JSON.parse(saved);
        ['h1','h2','h3','p'].forEach(tag => {
            if (!data[tag]) return;
            const d = data[tag];
            const setQ = (sel, val) => { const el = document.querySelector(sel); if (el && val) el.value = val; };
            setQ(`.typo-font[data-tag="${tag}"]`,  d.font);
            setQ(`.typo-size[data-tag="${tag}"]`,  d.size);
            setQ(`.typo-unit[data-tag="${tag}"]`,  d.unit);
            setQ(`.typo-color[data-tag="${tag}"]`, d.color);
        });
        setTimeout(applyTypography, 150);
    } catch(e) {}
})();


window.copyClientPayLink = function(identifier, orderId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function doCopy(invNum, ordId) {
        const payUrl = `${window.location.origin}/invoices/${invNum}`;
        try {
            const textarea = document.createElement('textarea');
            textarea.value = payUrl;
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            textarea.style.top = '-9999px';
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        } catch (err) {
            console.error('Copy fallback error:', err);
        }

        if (ordId) {
            window.updateOrderStatus(ordId, 'invoiced');
        }

        if (window.showToast) {
            window.showToast(`Invoice link copied & order set to INVOICED!`, 'success');
        } else {
            alert(`Invoice link copied: ${payUrl}`);
        }
    }

    if (typeof identifier === 'string' && identifier.startsWith('INV-')) {
        doCopy(identifier, orderId);
    } else {
        const targetOrderId = orderId || identifier;
        fetch('/dashboard/invoices', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                order_id: targetOrderId,
                mark_invoiced: true
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.invoice) {
                doCopy(data.invoice.invoice_number, targetOrderId);
            } else {
                alert('Error generating invoice link: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error('Copy Link Error:', err);
            alert('Failed to generate invoice link.');
        });
    }
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
            <select class="status-select status-${order.status || 'new'}" onchange="updateOrderStatus(${order.id}, this.value)">
                <option value="new" ${(!order.status || order.status === 'new') ? 'selected' : ''}>NEW</option>
                <option value="invoiced" ${order.status === 'invoiced' ? 'selected' : ''}>INVOICED</option>
                <option value="paid" ${order.status === 'paid' ? 'selected' : ''}>PAID</option>
                <option value="in_progress" ${order.status === 'in_progress' ? 'selected' : ''}>IN PROGRESS</option>
                <option value="ready" ${order.status === 'ready' ? 'selected' : ''}>READY</option>
                <option value="completed" ${order.status === 'completed' ? 'selected' : ''}>COMPLETED</option>
                <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>CANCELLED</option>
            </select>
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
            <button class="btn btn-sm btn-primary" onclick="generateInvoiceFromOrder(${order.id})">💳 Create Invoice</button>
        </div>
    `;
    queue.prepend(card);
}

/* ============================================
   ADMIN CALENDAR & AVAILABILITY MANAGER LOGIC
   ============================================ */

window.adminCalState = {
    currentYear: new Date().getFullYear(),
    currentMonth: new Date().getMonth(),
    blockedDates: new Set(['2026-07-04', '2026-07-25']),
    recurringClosedDays: [0, 1]
};

(function loadAdminCalendarSettings() {
    try {
        if (window._serverBookingSettings) {
            if (Array.isArray(window._serverBookingSettings.blocked_dates)) {
                window.adminCalState.blockedDates = new Set(window._serverBookingSettings.blocked_dates);
            }
            if (Array.isArray(window._serverBookingSettings.recurring_closed_days)) {
                window.adminCalState.recurringClosedDays = window._serverBookingSettings.recurring_closed_days;
            }
        }
    } catch(e) {}
})();

window.initAdminCalendarUI = function() {
    try {
        if (window._serverBookingSettings) {
            if (Array.isArray(window._serverBookingSettings.blocked_dates)) {
                window.adminCalState.blockedDates = new Set(window._serverBookingSettings.blocked_dates);
            }
            if (Array.isArray(window._serverBookingSettings.recurring_closed_days)) {
                window.adminCalState.recurringClosedDays = window._serverBookingSettings.recurring_closed_days;
            }
        }
    } catch(e) {}

    const grid = document.getElementById('admin-calendar-grid');
    if (!grid) return;

    // Check checkboxes matching recurringClosedDays
    document.querySelectorAll('.recurring-closed-checkbox').forEach(cb => {
        cb.checked = window.adminCalState.recurringClosedDays.includes(parseInt(cb.value));
    });

    renderAdminCalendarGrid();
    renderBlockedDatesList();
};

window.changeAdminCalMonth = function(delta) {
    window.adminCalState.currentMonth += delta;
    if (window.adminCalState.currentMonth > 11) {
        window.adminCalState.currentMonth = 0;
        window.adminCalState.currentYear++;
    } else if (window.adminCalState.currentMonth < 0) {
        window.adminCalState.currentMonth = 11;
        window.adminCalState.currentYear--;
    }
    renderAdminCalendarGrid();
};

function renderAdminCalendarGrid() {
    const grid = document.getElementById('admin-calendar-grid');
    const label = document.getElementById('admin-cal-month-year');
    if (!grid) return;

    grid.innerHTML = '';

    const year = window.adminCalState.currentYear;
    const month = window.adminCalState.currentMonth;

    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    if (label) label.innerText = `${monthNames[month]} ${year}`;

    // Header Day Names
    const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    dayHeaders.forEach(dh => {
        const hEl = document.createElement('div');
        hEl.className = 'admin-cal-header-day';
        hEl.innerText = dh;
        grid.appendChild(hEl);
    });

    const firstDayIndex = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    // Empty slot padding
    for (let i = 0; i < firstDayIndex; i++) {
        const emptyEl = document.createElement('div');
        emptyEl.className = 'admin-cal-day empty-slot';
        grid.appendChild(emptyEl);
    }

    // Day Cells
    for (let day = 1; day <= daysInMonth; day++) {
        const dateObj = new Date(year, month, day);
        const dayOfWeek = dateObj.getDay();
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

        const isBlocked = window.adminCalState.blockedDates.has(dateStr);
        const isWeeklyClosed = window.adminCalState.recurringClosedDays.includes(dayOfWeek);

        const dayEl = document.createElement('div');
        let statusClass = 'available';
        if (isBlocked) {
            statusClass = 'blocked';
        } else if (isWeeklyClosed) {
            statusClass = 'weekly-closed';
        }

        dayEl.className = `admin-cal-day ${statusClass}`;
        dayEl.innerText = day;
        dayEl.title = isBlocked ? `Blocked (${dateStr}) - Click to Unblock` : (isWeeklyClosed ? `Closed (${dayHeaders[dayOfWeek]}) - Click to Override Block` : `Available - Click to Block`);

        dayEl.onclick = function() {
            window.toggleAdminCalDate(dateStr);
        };

        grid.appendChild(dayEl);
    }
}

window.toggleAdminCalDate = function(dateStr) {
    if (window.adminCalState.blockedDates.has(dateStr)) {
        window.adminCalState.blockedDates.delete(dateStr);
    } else {
        window.adminCalState.blockedDates.add(dateStr);
    }
    saveAdminCalendarState();
    renderAdminCalendarGrid();
    renderBlockedDatesList();
    if (window.renderInteractiveCalendar) renderInteractiveCalendar();
};

function renderBlockedDatesList() {
    const list = document.getElementById('admin-blocked-dates-list');
    if (!list) return;

    list.innerHTML = '';
    const sorted = Array.from(window.adminCalState.blockedDates).sort();

    if (sorted.length === 0) {
        list.innerHTML = '<span style="color:#aaa; font-size:0.9rem;">No custom blocked dates added yet. Click any calendar date above or use the manual input!</span>';
        return;
    }

    sorted.forEach(dateStr => {
        const badge = document.createElement('div');
        badge.className = 'blocked-date-badge';
        badge.innerHTML = `
            <span>🚫 ${dateStr}</span>
            <button title="Unblock Date" onclick="removeBlockedDate('${dateStr}')">✕</button>
        `;
        list.appendChild(badge);
    });
}

window.removeBlockedDate = function(dateStr) {
    window.adminCalState.blockedDates.delete(dateStr);
    saveAdminCalendarState();
    renderAdminCalendarGrid();
    renderBlockedDatesList();
    if (window.renderInteractiveCalendar) renderInteractiveCalendar();
};

window.addManualBlockedDate = function() {
    const input = document.getElementById('manual-block-date');
    if (!input || !input.value) {
        alert('Please pick a date first!');
        return;
    }
    const val = input.value;
    window.adminCalState.blockedDates.add(val);
    saveAdminCalendarState();
    renderAdminCalendarGrid();
    renderBlockedDatesList();
    if (window.renderInteractiveCalendar) renderInteractiveCalendar();
    input.value = '';
};

window.saveRecurringClosedDays = function() {
    const selected = [];
    document.querySelectorAll('.recurring-closed-checkbox:checked').forEach(cb => {
        selected.push(parseInt(cb.value));
    });
    window.adminCalState.recurringClosedDays = selected;

    saveAdminCalendarState();

    const msg = document.getElementById('recurring-save-msg');
    if (msg) {
        msg.style.display = 'inline';
        setTimeout(() => msg.style.display = 'none', 2500);
    }
    if (window.showToast) {
        window.showToast('Recurring availability schedule saved!', 'success');
    }

    renderAdminCalendarGrid();
    if (window.renderInteractiveCalendar) renderInteractiveCalendar();
};

function saveAdminCalendarState() {
    const arr = Array.from(window.adminCalState.blockedDates);
    localStorage.setItem('admin_blocked_dates', JSON.stringify(arr));
    localStorage.setItem('admin_recurring_closed', JSON.stringify(window.adminCalState.recurringClosedDays));

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    fetch('/dashboard/settings/booking', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            lead_time_enabled: true,
            lead_time_days: 3,
            recurring_closed_days: window.adminCalState.recurringClosedDays,
            blocked_dates: arr
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.settings) {
            window._serverBookingSettings = data.settings;
        }
    })
    .catch(err => console.error('Booking save error:', err));
}

// Global Bakery Theme Switcher
window.selectBakeryTheme = function(themeId, cardEl) {
    document.querySelectorAll('.bakery-theme-card').forEach(c => {
        c.style.borderColor = '#ddd';
        const badge = c.querySelector('.theme-badge');
        if (badge) badge.style.display = 'none';
    });

    if (cardEl) {
        cardEl.style.borderColor = '#e67399';
        const badge = cardEl.querySelector('.theme-badge');
        if (badge) badge.style.display = 'inline-block';
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    fetch('/dashboard/theme', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken || '',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ theme_id: themeId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.body.className = `theme-${themeId}`;
            const msgEl = document.getElementById('theme-status-msg');
            if (msgEl) {
                msgEl.style.display = 'inline-block';
                msgEl.innerHTML = `✅ Theme updated to <strong>${themeId.replace('_', ' ').toUpperCase()}</strong>! <a href="/" target="_blank" style="color:#e67399; font-weight:700; text-decoration:underline; margin-left:8px;">View Live Storefront ↗</a>`;
            }
        }
    })
    .catch(err => {
        console.error('Theme update error:', err);
    });
};

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        window.initAdminCalendarUI();
    }, 200);
});

// Update order status via AJAX
window.updateOrderStatus = function(orderId, status) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    fetch(`/dashboard/orders/${orderId}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showToast(data.message, 'success');
            
            // Dynamically update the select box color class
            const selectBox = document.querySelector(`select[onchange="updateOrderStatus(${orderId}, this.value)"]`);
            if (selectBox) {
                selectBox.value = data.status;
                selectBox.className = `status-select status-${data.status}`;
            }
        } else {
            window.showToast(data.message || 'Failed to update status', 'error');
        }
    })
    .catch(err => {
        window.showToast('Error updating status: ' + err.message, 'error');
    });
};

// Update invoice status via AJAX
window.updateInvoiceStatus = function(invoiceId, status) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    fetch(`/dashboard/invoices/${invoiceId}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showToast(data.message, 'success');
            const selectBox = document.querySelector(`select[onchange="updateInvoiceStatus(${invoiceId}, this.value)"]`);
            if (selectBox) {
                selectBox.value = data.status;
                selectBox.className = `status-select status-${data.status}`;
            }
        } else {
            window.showToast(data.message || 'Failed to update status', 'error');
        }
    })
    .catch(err => {
        window.showToast('Error updating status: ' + err.message, 'error');
    });
};

window.openInvoiceEditModal = function(invoiceId, totalAmount, depositAmount, notes, orderId) {
    document.getElementById('edit-invoice-id').value = invoiceId || '';
    document.getElementById('edit-order-id').value = orderId || '';
    document.getElementById('edit-invoice-total').value = parseFloat(totalAmount || 0).toFixed(2);
    document.getElementById('edit-invoice-deposit').value = parseFloat(depositAmount || 0).toFixed(2);
    document.getElementById('edit-invoice-notes').value = notes || '';
    
    document.getElementById('invoice-edit-modal').style.display = 'flex';
};

window.closeInvoiceEditModal = function() {
    document.getElementById('invoice-edit-modal').style.display = 'none';
};

window.saveInvoiceEdits = function() {
    submitInvoiceEdits(false);
};

window.saveAndSendInvoice = function() {
    submitInvoiceEdits(true);
};

function submitInvoiceEdits(sendAfter) {
    const invoiceId = document.getElementById('edit-invoice-id').value;
    const orderId = document.getElementById('edit-order-id').value;
    const totalAmount = document.getElementById('edit-invoice-total').value;
    const depositAmount = document.getElementById('edit-invoice-deposit').value;
    const notes = document.getElementById('edit-invoice-notes').value;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    if (invoiceId) {
        fetch(`/dashboard/invoices/${invoiceId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                total_amount: totalAmount,
                deposit_amount: depositAmount,
                notes: notes
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeInvoiceEditModal();
                if (sendAfter) {
                    if (orderId) {
                        window.updateOrderStatus(orderId, 'invoiced');
                    }
                    window.sendInvoice(invoiceId);
                } else {
                    if (window.showToast) {
                        window.showToast('Invoice updated & saved!', 'success');
                    }
                    setTimeout(() => location.reload(), 1000);
                }
            } else {
                alert('Error updating invoice: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Invoice Update Error:', err);
            alert('Failed to update invoice.');
        });
    } else {
        fetch('/dashboard/invoices', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                order_id: orderId,
                total_amount: totalAmount,
                deposit_amount: depositAmount,
                notes: notes,
                mark_invoiced: sendAfter
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeInvoiceEditModal();
                if (sendAfter) {
                    if (orderId) {
                        window.updateOrderStatus(orderId, 'invoiced');
                    }
                    if (data.invoice && data.invoice.id) {
                        window.sendInvoice(data.invoice.id);
                    }
                } else {
                    if (window.showToast) {
                        window.showToast('Invoice created & saved!', 'success');
                    }
                    setTimeout(() => location.reload(), 1000);
                }
            } else {
                alert('Error creating invoice: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Invoice Creation Error:', err);
            alert('Failed to create invoice.');
        });
    }
}
