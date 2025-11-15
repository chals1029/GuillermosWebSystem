<?php
require_once __DIR__ . '/../../Controllers/CustomerController.php';

$controller = new CustomerController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->handleAjax();
    exit;
}

$selectedCategory = $_GET['category'] ?? 'all';
$products = $controller->getProductsByCategory($selectedCategory);
$cart = $controller->getCart();
$cart_count = $controller->countCartItems($cart);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Guillermo’s Café</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600; 700&display=swap" rel="stylesheet">
<style>
    body{font-family:'Poppins',sans-serif;background:#fefcf7;margin:0;padding:0;}
    header{background:#6B4F3F;color:#fff;display:flex;justify-content:space-between;align-items:center;padding:15px 30px;position:relative;}
    .logo{font-size:1.8rem;font-weight:700;cursor:pointer;}
    .icon-btn{background:transparent;border:none;cursor:pointer;position:relative;}
    .container{padding:30px 60px;}
    h2{margin-bottom:10px;color:#4d2e00;}
    .filter{margin-bottom:20px;}
    select{padding:8px;border:1px solid #d4bca7;border-radius:5px;background:#fff;}
    .product-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:20px;}
    .product{background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,.1);padding:18px;text-align:center;height:150px;display:flex;flex-direction:column;justify-content:space-between;}
    .product-name{font-size:16px;font-weight:600;color:#3b2a19;}
    .product-desc{font-size:13px;color:#666;margin:5px 0;}
    .product-bottom{display:flex;justify-content:space-between;align-items:center;}
    .price{color:#000;font-weight:600;font-size:15px;}
    .add-btn{background:#b57b46;color:#fff;border:none;border-radius:5px;padding:6px 10px;cursor:pointer;}
    .add-btn:hover{background:#a66d3d;}
    #cart-count{position:absolute;top:-5px;right:-5px;background:red;color:#fff;font-size:12px;font-weight:bold;border-radius:50%;padding:2px 6px;display:<?= $cart_count>0?'inline':'none' ?>;}

    /* ---------- OVERLAYS ---------- */
    .overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.6);z-index:9998;display:none;align-items:center;justify-content:center;}
    .overlay-content{background:#f9f1e8;width:90%;max-width:800px;max-height:90vh;overflow-y:auto;padding:30px;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,.2);position:relative;}
    .close-btn{position:absolute;top:15px;right:15px;background:none;border:none;font-size:24px;color:#666;cursor:pointer;}

    /* ---------- CART OVERLAY ---------- */
    .overlay-header h2{font-size:22px;color:#4d2e00;margin:0;}
    .overlay-header p{font-size:14px;color:#666;margin:5px 0 20px;}
    .cart-item{display:flex;align-items:center;gap:15px;padding:15px 0;border-bottom:1px solid #eee;}
    .cart-item-img{width:60px;height:60px;border-radius:50%;overflow:hidden;background:#eee;}
    .cart-item-img img{width:100%;height:100%;object-fit:cover;}
    .cart-item-details{flex:1;}
    .cart-item-name{font-weight:600;color:#3b2a19;font-size:16px;}
    .cart-item-price{font-size:13px;color:#666;margin-top:2px;}
    .quantity-controls{display:flex;align-items:center;gap:8px;font-weight:bold;}
    .quantity-btn{background:#e6d5c3;color:#5b3a1e;padding:4px 10px;border-radius:5px;text-decoration:none;font-size:14px;cursor:pointer;}
    .quantity{display:inline-block;min-width:30px;text-align:center;}
    .line-total{font-weight:600;color:#3b2a19;margin-right:10px;}
    .remove-btn{color:red;font-weight:bold;font-size:18px;cursor:pointer;margin-left:10px;}
    .total{font-weight:bold;font-size:18px;margin:25px 0;text-align:right;color:#3b2a19;}
    .cart-actions{display:flex;justify-content:flex-end;gap:15px;margin-top:25px;}
    .btn{padding:12px 24px;border:none;border-radius:8px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block;}
    .btn-continue{background:#fff;border:2px solid #6B4F3F;color:#6B4F3F;}
    .btn-continue:hover{background:#f1e5d6;}
    .btn-checkout{background:#6B4F3F;color:#fff;}
    .btn-checkout:hover{background:#5a3e30;}

    /* ---------- CHECKOUT UI ---------- */
    .checkout-modern {
        background: linear-gradient(135deg, #fefcf7 0%, #f5ece2 100%);
        padding: 20px;
        border-radius: 20px;
        max-width: 480px;
        width: 100%;
        margin: 0 auto;
        box-shadow: 0 15px 35px rgba(107, 79, 63, 0.15);
    }

    .checkout-card {
        background: #fff;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }

    .checkout-card-header {
        background: linear-gradient(135deg, #6B4F3F, #8B6F5F);
        color: #fff;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
    }

    .checkout-card-header h1 {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 600;
    }

    .checkout-card-header .back-btn {
        background: none;
        border: none;
        color: #fff;
        cursor: pointer;
        padding: 4px;
        border-radius: 50%;
        transition: background 0.2s;
    }

    .checkout-card-header .back-btn:hover {
        background: rgba(255,255,255,0.2);
    }

    .cart-badge {
        background: #fff;
        color: #6B4F3F;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.8rem;
        position: relative;
    }

    .cart-badge span {
        position: absolute;
        top: -6px;
        right: -6px;
        background: #e74c3c;
        color: #fff;
        font-size: 0.65rem;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .checkout-card-body {
        padding: 24px;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #4d2e00;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .form-select,
    .form-control,
    .form-control[type="text"],
    .form-control[type="textarea"] {
        width: 100%;
        padding: 12px 14px;
        border: 1.5px solid #ddd;
        border-radius: 12px;
        font-size: 15px;
        background: #fff;
        transition: all 0.2s;
    }

    .form-select:focus,
    .form-control:focus {
        outline: none;
        border-color: #6B4F3F;
        box-shadow: 0 0 0 3px rgba(107, 79, 63, 0.15);
    }

    .form-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236B4F3F' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
    }

    .toggle-reservation {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .toggle-reservation label {
        font-weight: 600;
        color: #4d2e00;
        margin: 0;
        cursor: pointer;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 26px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .3s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }

    input:checked + .slider {
        background-color: #6B4F3F;
    }

    input:checked + .slider:before {
        transform: translateX(22px);
    }

    .order-summary {
        background: #f9f1e8;
        padding: 16px;
        border-radius: 12px;
        margin: 20px 0;
        font-size: 0.95rem;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        color: #5a3e30;
    }

    .summary-row.total {
        font-weight: bold;
        font-size: 1.1rem;
        color: #3b2a19;
        padding-top: 8px;
        border-top: 1px dashed #ccc;
        margin-top: 8px;
    }

    .btn-place-order {
        background: linear-gradient(135deg, #6B4F3F, #8B6F5F);
        color: #fff;
        border: none;
        width: 100%;
        padding: 14px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.05rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(107, 79, 63, 0.3);
    }

    .btn-place-order:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(107, 79, 63, 0.4);
    }

    .btn-place-order:active {
        transform: translateY(0);
    }

    /* ---------- THANK YOU SCREEN ---------- */
    .thankyou-screen{text-align:center;padding:40px;background:#fefcf7;border-radius:12px;max-width:600px;margin:0 auto;}
    .thankyou-img{margin-bottom:20px;}
    .thankyou-img img{width:120px;}
    .thankyou-title{font-size:1.8rem;font-weight:700;color:#4d2e00;margin-bottom:15px;}
    .thankyou-msg{color:#666;line-height:1.6;margin-bottom:30px;}
    .thankyou-actions{display:flex;gap:15px;justify-content:center;}
    .thankyou-btn{background:#6B4F3F;color:#fff;padding:12px 24px;border-radius:50px;font-weight:600;cursor:pointer;}
    .thankyou-btn:hover{background:#5a3e30;}

    /* Success Toast */
    .success-message {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #4CAF50;
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 9999;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.4s ease;
    }

    .success-message.show {
        opacity: 1;
        transform: translateX(0);
    }
</style>
</head>
<body>

<!-- Success Toast -->
<div id="success-message" class="success-message">
    Order placed successfully!
</div>

<header>
    <div class="logo" onclick="location.reload()">Guillermo’s</div>
    <div class="icons">
        <button class="icon-btn" id="open-cart">
            <img src="icons/cart.png" alt="Cart" width="30" height="30">
            <span id="cart-count"><?= $cart_count ?></span>
        </button>
    </div>
</header>

<!-- CART OVERLAY -->
<div id="cart-overlay" class="overlay">
    <div class="overlay-content">
        <button class="close-btn" id="close-cart">X</button>
        <div class="overlay-header">
            <h2>My Cart</h2>
            <p>Review your items before checkout</p>
        </div>
        <div id="cart-items"></div>
        <div class="total">TOTAL: <span id="cart-total">₱0.00</span></div>
        <div class="cart-actions">
            <button class="btn btn-continue" id="continue-shopping">Continue Shopping</button>
            <button class="btn btn-checkout" id="proceed-checkout">Proceed to Checkout</button>
        </div>
    </div>
</div>

<!-- CHECKOUT SCREEN -->
<div id="checkout-screen" class="overlay" style="display:none;">
    <div class="checkout-modern">
        <div class="checkout-card">
            <div class="checkout-card-header">
                <button class="back-btn" id="back-to-cart">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                </button>
                <h1>Complete Your Order</h1>
                <div class="cart-badge">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    <span id="checkout-cart-count">0</span>
                </div>
            </div>

            <div class="checkout-card-body">

                <!-- Order Type -->
                <div class="form-group">
                    <label class="form-label">Order Type</label>
                    <select class="form-select" id="order_type_select">
                        <option value="dine-in">Dine-In</option>
                        <option value="delivery">Delivery</option>
                        <option value="pickup">Pick-Up</option>
                    </select>
                </div>

                <!-- Reservation Toggle -->
                <div class="toggle-reservation">
                    <label>Is this a reservation?</label>
                    <label class="switch">
                        <input type="checkbox" id="is_reservation">
                        <span class="slider"></span>
                    </label>
                </div>

                <!-- Customer Name -->
                <div class="form-group">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="customer_name" placeholder="e.g. Juan Dela Cruz" required>
                </div>

                <!-- Delivery Address (Only for Delivery) -->
                <div class="form-group" id="address_group" style="display:none;">
                    <label class="form-label">Delivery Address <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="delivery_address" rows="2" placeholder="Street, Barangay, City"></textarea>
                </div>

                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="checkout-subtotal">₱0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Delivery Fee</span>
                        <span id="delivery-fee">₱0.00</span>
                    </div>
                    <div class="summary-row total">
                        <strong>Total</strong>
                        <strong id="checkout-total">₱0.00</strong>
                    </div>
                </div>

                <button class="btn-place-order" id="finalize-order">
                    <span>Place Order</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- THANK YOU SCREEN -->
<div id="thankyou-screen" class="overlay" style="display:none;">
    <div class="thankyou-screen">
        <div class="thankyou-img">
            <img src="icons/coffee-cup.png" alt="Thank you">
        </div>
        <div class="thankyou-title">ORDER PLACED SUCCESSFULLY!</div>
        <div class="thankyou-msg" id="delivery-message">
            Your order has been confirmed and is now being prepared.<br>
            Thank you for choosing Guillermo’s Café!
        </div>
        <div class="thankyou-actions">
            <button class="thankyou-btn" id="back-home">BACK TO HOME</button>
            <button class="thankyou-btn" id="order-again">ORDER AGAIN</button>
        </div>
    </div>
</div>

<div class="container">
    <h2>Our Authentic Products</h2>
    <p>Find pasta, cakes, coffee, and more made from quality and traditional recipes.</p>

    <form method="GET" class="filter">
        <label for="category">Category: </label>
        <select name="category" id="category" onchange="this.form.submit()">
            <option value="all" <?= $selectedCategory === 'all' ? 'selected' : '' ?>>All</option>
            <option value="Pasta" <?= $selectedCategory === 'Pasta' ? 'selected' : '' ?>>Pasta</option>
            <option value="Rice Meals" <?= $selectedCategory === 'Rice Meals' ? 'selected' : '' ?>>Rice Meals</option>
            <option value="Pizza Menu" <?= $selectedCategory === 'Pizza Menu' ? 'selected' : '' ?>>Pizza</option>
            <option value="Sandwich salads" <?= $selectedCategory === 'Sandwich salads' ? 'selected' : '' ?>>Sandwich & Salad</option>
            <option value="Coffee Beverages" <?= $selectedCategory === 'Coffee Beverages' ? 'selected' : '' ?>>Coffee</option>
            <option value="NonCoffee" <?= $selectedCategory === 'NonCoffee' ? 'selected' : '' ?>>Non-Coffee</option>
            <option value="Breads" <?= $selectedCategory === 'Breads' ? 'selected' : '' ?>>Breads</option>
            <option value="Cakes" <?= $selectedCategory === 'Cakes' ? 'selected' : '' ?>>Cakes</option>
            <option value="Cookies" <?= $selectedCategory === 'Cookies' ? 'selected' : '' ?>>Pie, Cookies & Bar</option>
        </select>
    </form>

    <div class="product-grid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $row): ?>
                <div class="product">
                    <h3 class="product-name"><?= htmlspecialchars($row['Product_Name']) ?></h3>
                    <p class="product-desc"><?= htmlspecialchars($row['Description']) ?></p>
                    <div class="product-bottom">
                        <p class="price">₱<?= number_format($row['Price'], 2) ?></p>
                        <button class="add-btn" onclick="addToCart('<?= addslashes($row['Product_Name']) ?>', <?= $row['Price'] ?>)">Add to Cart</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No products available in this category.</p>
        <?php endif; ?>
    </div>
</div>

<script>
/* ---------- CART STATE ---------- */
let cart = <?= json_encode($cart) ?>;

/* ---------- HELPERS ---------- */
const fmt = p => '₱' + Number(p).toFixed(2);
const totalItems = () => Object.values(cart).reduce((s,i)=>s+i.quantity,0);
const calcSubtotal = () => Object.values(cart).reduce((s,i)=>s+(i.price*i.quantity),0);

/* ---------- DOM ---------- */
const $ = id => document.getElementById(id);
const badge = $('cart-count');
const checkoutBadge = $('checkout-cart-count');

/* ---------- UPDATE BADGES ---------- */
function updateBadge(){
    const cnt = totalItems();
    badge.textContent = cnt;
    badge.style.display = cnt ? 'inline' : 'none';
    if (checkoutBadge) checkoutBadge.textContent = cnt;
}

/* ---------- RENDER CART & SUMMARY ---------- */
function renderCart(){
    const items = $('cart-items');
    const totalEl = $('cart-total');
    const subtotalEl = $('checkout-subtotal');
    const totalCheckoutEl = $('checkout-total');
    const feeEl = $('delivery-fee');

    if (!Object.keys(cart).length){
        items.innerHTML = '<p style="text-align:center;color:#888;padding:40px 0;">Your cart is empty</p>';
        totalEl.textContent = '₱0.00';
        subtotalEl.textContent = '₱0.00';
        totalCheckoutEl.textContent = '₱0.00';
        feeEl.textContent = '₱0.00';
        return;
    }

    let html = '', subtotal = 0;
    for (const [name, it] of Object.entries(cart)){
        const line = it.price * it.quantity;
        subtotal += line;
        const imgSrc = 'https://via.placeholder.com/60?text=Img';
        html += `
            <div class="cart-item">
                <div class="cart-item-img"><img src="${imgSrc}" alt="${name}"></div>
                <div class="cart-item-details">
                    <div class="cart-item-name">${name}</div>
                    <div class="cart-item-price">${fmt(it.price)} each</div>
                </div>
                <div class="quantity-controls">
                    <button type="button" class="quantity-btn" data-action="decrease" data-product="${name}">−</button>
                    <span class="quantity">${it.quantity}</span>
                    <button type="button" class="quantity-btn" data-action="increase" data-product="${name}">+</button>
                </div>
                <div class="line-total">${fmt(line)}</div>
                <button type="button" class="remove-btn" data-action="remove" data-product="${name}">×</button>
            </div>`;
    }
    items.innerHTML = html;
    totalEl.textContent = fmt(subtotal);
    subtotalEl.textContent = fmt(subtotal);

    const orderType = $('order_type_select')?.value || 'dine-in';
    const isDelivery = orderType === 'delivery';
    const deliveryFee = isDelivery ? 50 : 0;
    feeEl.textContent = fmt(deliveryFee);
    totalCheckoutEl.textContent = fmt(subtotal + deliveryFee);
}

/* ---------- SHOW SUCCESS TOAST ---------- */
function showSuccess() {
    const toast = $('success-message');
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

/* ---------- CART ACTIONS ---------- */
document.getElementById('cart-items').addEventListener('click', e => {
    const btn = e.target;
    if (!btn.matches('.quantity-btn, .remove-btn')) return;
    const action = btn.dataset.action;
    const product = btn.dataset.product;
    const fd = new FormData();
    fd.append('action', action);
    fd.append('product', product);
    fetch('', {method:'POST', body:fd})
        .then(r=>r.text())
        .then(() => {
            if (action==='increase') cart[product].quantity++;
            else if (action==='decrease'){
                cart[product].quantity--;
                if (cart[product].quantity <= 0) delete cart[product];
            }
            else if (action==='remove') delete cart[product];
            updateBadge(); renderCart();
        });
});

/* ---------- ADD TO CART ---------- */
function addToCart(name, price){
    const fd = new FormData();
    fd.append('action', 'increase');
    fd.append('product', name);
    fetch('', {method:'POST', body:fd})
        .then(() => {
            if (!cart[name]) cart[name] = {price, quantity:0};
            cart[name].quantity++;
            updateBadge();
            alert('Added to cart!');
        });
}

/* ---------- NAVIGATION ---------- */
$('open-cart').onclick = () => { $('cart-overlay').style.display='flex'; renderCart(); };
$('close-cart').onclick = () => $('cart-overlay').style.display='none';
$('continue-shopping').onclick = () => $('cart-overlay').style.display='none';
$('proceed-checkout').onclick = () => {
    $('cart-overlay').style.display='none';
    $('checkout-screen').style.display='flex';
    renderCart();
    updateBadge();
    updateCheckoutFields();
};
$('back-to-cart').onclick = () => {
    $('checkout-screen').style.display='none';
    $('cart-overlay').style.display='flex';
};

/* ---------- TOGGLE FIELDS ---------- */
function updateCheckoutFields() {
    const orderType = $('order_type_select').value;
    const isDelivery = orderType === 'delivery';
    $('address_group').style.display = isDelivery ? 'block' : 'none';
    if (!isDelivery) $('delivery_address').value = '';
}

$('order_type_select').addEventListener('change', updateCheckoutFields);

/* ---------- PLACE ORDER ---------- */
$('finalize-order').onclick = () => {
    const name = $('customer_name').value.trim();
    if (!name) return alert('Please enter your full name.');

    const orderType = $('order_type_select').value;
    if (orderType === 'delivery' && !$('delivery_address').value.trim()) {
        return alert('Delivery address is required.');
    }

    const order = {
        customer_name: name,
        order_type: orderType,
        is_reservation: $('is_reservation').checked,
        delivery_address: orderType === 'delivery' ? $('delivery_address').value.trim() : '',
        payment_method: 'cash', // Always cash
        items: cart,
        subtotal: calcSubtotal(),
        delivery_fee: orderType === 'delivery' ? 50 : 0
    };

    const fd = new FormData();
    fd.append('checkout', '1');
    fd.append('order_data', JSON.stringify(order));

    fetch('', {method:'POST', body:fd})
        .then(r => r.json())
        .then(res => {
            if (res.status === 'ok') {
                cart = {};
                updateBadge();
                $('checkout-screen').style.display = 'none';
                $('thankyou-screen').style.display = 'flex';

                // Show delivery address in thank you message
                const msg = $('delivery-message');
                if (order.order_type === 'delivery' && res.address) {
                    msg.innerHTML = `Your order has been placed and will be delivered to:<br><strong>${res.address}</strong><br><br>Thank you for choosing Guillermo’s Café!`;
                } else {
                    msg.innerHTML = `Your order has been confirmed and is now being prepared.<br>Thank you for choosing Guillermo’s Café!`;
                }

                showSuccess();
            } else {
                alert('Order failed: ' + (res.message || 'Try again.'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Network error. Check console.');
        });
};

/* ---------- THANK YOU ---------- */
$('back-home').onclick = () => location.reload();
$('order-again').onclick = () => {
    $('thankyou-screen').style.display='none';
    $('cart-overlay').style.display='flex';
    renderCart();
};

/* ---------- INIT ---------- */
updateBadge();
updateCheckoutFields();
</script>
</body>
</html>