// Blushed Crumbs Bakehouse & BakeBox SaaS Interactive Engine

document.addEventListener('DOMContentLoaded', () => {
    init12StepOrderForm();
    initAdminPortal();
    renderInteractiveCalendar();
    initOrderModalTrigger();
});

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
    deposit: 0
};

// Modal Order Form Triggers
function initOrderModalTrigger() {
    // Open order modal from any Order button
    document.querySelectorAll('.trigger-order-modal').forEach(btn => {
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

// 12-Step Form Navigation Logic
function init12StepOrderForm() {
    const productGrid = document.getElementById('product-grid');
    if (productGrid) {
        productGrid.addEventListener('click', (e) => {
            const card = e.target.closest('.product');
            if (!card) return;

            const name = card.dataset.name;
            const price = parseFloat(card.dataset.price);

            card.classList.toggle('selected');
            
            if (card.classList.contains('selected')) {
                state.selectedProducts.push({ name, price });
            } else {
                state.selectedProducts = state.selectedProducts.filter(p => p.name !== name);
            }

            updateCartSummary();
        });
    }

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

    const socialGrid = document.getElementById('social-grid');
    if (socialGrid) {
        socialGrid.addEventListener('click', (e) => {
            const card = e.target.closest('.product');
            if (!card) return;
            card.classList.toggle('selected');
            calculateDiscounts();
        });
    }

    const termsCheck = document.getElementById('terms-agree-checkbox');
    const step11Next = document.getElementById('to-step-12');
    if (termsCheck && step11Next) {
        termsCheck.addEventListener('change', () => {
            step11Next.disabled = !termsCheck.checked;
        });
    }

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
                due_date: state.selectedDate || '2026-07-25',
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

    const listEl = document.getElementById('cart-items-list');
    const summaryEl = document.getElementById('cart-summary');
    const step1Next = document.getElementById('to-step-2');

    if (listEl) {
        listEl.innerHTML = state.selectedProducts.length > 0 
            ? state.selectedProducts.map(p => p.name).join(', ')
            : 'No items selected';
    }

    if (summaryEl) {
        summaryEl.innerHTML = `Items: ${state.selectedProducts.length} <br> <strong>Total: $${state.total.toFixed(0)}</strong>`;
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
                document.getElementById('to-step-3').disabled = false;
            });
        }

        calGrid.appendChild(dayEl);
    }
}

// Bakesy Mobile Admin Controller & Visual Form Builder
function initAdminPortal() {
    const tabBtns = document.querySelectorAll('.admin-tabs .tab-btn');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));

            btn.classList.add('active');
            const targetTab = document.getElementById(btn.dataset.tab);
            if (targetTab) targetTab.classList.add('active');
        });
    });

    // Form & Product Builder Form
    const prodForm = document.getElementById('add-product-form');
    if (prodForm) {
        prodForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const name = document.getElementById('new-prod-name').value;
            const price = parseFloat(document.getElementById('new-prod-price').value);
            const category = document.getElementById('new-prod-category').value;

            // Dynamically add to product grid in Order Form Step 1!
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
                row.innerHTML = `
                    <span><strong>${name}</strong> (${category})</span>
                    <div>
                        <input type="number" class="price-input" value="${price.toFixed(2)}">
                        <button class="btn btn-sm btn-secondary" onclick="updateProductPrice(this)">Save Price</button>
                    </div>
                `;
                adminGrid.prepend(row);
            }

            alert(`Product "${name}" added to order builder & live storefront!`);
            prodForm.reset();
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
