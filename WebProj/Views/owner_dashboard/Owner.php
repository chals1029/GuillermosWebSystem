<?php
require_once __DIR__ . '/../../Controllers/OwnerController.php';

$ownerController = new OwnerController();

if (isset($_GET['action']) || isset($_POST['action'])) {
  $ownerController->handleAjax();
  exit;
}

$dashboardStats = $ownerController->getDashboardStats();
$inventoryData = $ownerController->getInventory();
$productPerformance = $ownerController->getProductPerformance();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Guillermo’s Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    body {font-family:'Poppins',sans-serif;background:#fefcf7;margin:0;padding:0;}
   .sidebar{width:250px;height:100vh;background:#fff;border-right:1px solid #dcdcdc;position:fixed;top:0;left:0;padding:1rem;}
    .sidebar h4{font-weight:bold;font-size:50px;color:#4d2e00;font-family:'Brush Script MT',cursive;}
    .sidebar .nav-link{color:#4d2e00;font-weight:500;margin-bottom:.5rem;border-radius:6px;cursor:pointer;}
    .sidebar .nav-link.active{background:#c1976b;color:#fff;}
    .header{background:#6B4F3F;color:#fff;padding:20px 40px;position:fixed;left:250px;right:0;top:0;z-index:10;
            box-shadow:0 2px 10px rgba(0,0,0,.1);display:flex;justify-content:space-between;align-items:center;}
    .header .title p{margin:0;font-size:1rem;}
    .main{margin-left:250px;margin-top:100px;padding:20px 40px;transition: margin-left .3s;}

    /* Smooth page transition */
    .page {display: none; opacity: 0; transition: opacity 0.3s ease;}
    .page.active {display: block; opacity: 1;}

    /* ==== USER PROFILE DROPDOWN ==== */
    .user-profile-dropdown .dropdown-toggle {
      background: none;
      border: none;
      color: #fff;
      font-size: 1.5rem;
      padding: 0;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .user-profile-dropdown .dropdown-toggle::after { display: none; }
    .user-profile-dropdown .dropdown-menu {
      border-radius: 16px;
      box-shadow: 0 4px 15px rgba(0,0,0,.1);
      min-width: 220px;
      padding: 12px 0;
      border: none;
    }
    .user-profile-dropdown .dropdown-item {
      padding: 10px 20px;
      font-size: .95rem;
      color: #4d2e00;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .user-profile-dropdown .dropdown-item:hover {
      background: #e2c9a7;
      color: #4d2e00;
    }
    .user-profile-dropdown .user-info {
      padding: 15px 20px;
      border-bottom: 1px solid #eee;
      margin-bottom: 8px;
    }
    .user-profile-dropdown .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: #e2c9a7;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      color: #4d2e00;
      font-size: 1rem;
    }
    .user-profile-dropdown .user-details h6 {
      margin: 0;
      font-size: .95rem;
      font-weight: 600;
      color: #4d2e00;
    }
    .user-profile-dropdown .user-details p {
      margin: 0;
      font-size: .8rem;
      color: #8d6e63;
    }

    /* Cards */
    .card-box{background:#d7b79a;border-radius:20px;padding:25px;color:#fff;display:flex;align-items:center;justify-content:space-between;box-shadow:0 4px 12px rgba(0,0,0,.1);transition:transform .2s;height:130px;}
    .card-box:hover{transform:translateY(-5px);}
    .card-box img{width:60px;height:60px;}
    .card-text p{margin:0;margin-top:6vh;font-size:1rem;font-weight:700;color:#3b2c23;}

    /* Order Summary */
    .order-summary-card{background:#f5e6d3;border-radius:20px;padding:25px;box-shadow:0 4px 12px rgba(0,0,0,.1);text-align:center;height:100%;display:flex;flex-direction:column;justify-content:flex-start;transition:transform .2s;}
    .order-summary-card:hover{transform:translateY(-5px);}
    .order-summary-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;font-weight:700;color:#4d2e00;}
    .order-tabs{display:flex;gap:15px;position:relative;}
    .order-tab{cursor:pointer;padding:5px 10px;font-size:0.9rem;color:#8d6e63;transition:color .3s;}
    .order-tab.active{color:#4d2e00;}
    .underline{position:absolute;bottom:-8px;height:2px;background:#4d2e00;transition:all .3s ease;border-radius:2px;}
    .no-orders-msg{margin-top:30px;color:#8d6e63;font-size:1.1rem;}
    .no-orders-msg i{font-size:2.5rem;color:#d7b79a;margin-bottom:10px;}

    /* Funding */
    .no-funding{text-align:center;padding:40px;color:#8d6e63;font-size:1.2rem;}
    .no-funding i{font-size:3rem;color:#d7b79a;margin-bottom:15px;}

    /* Revenue & Charts */
    .revenue-section,.chart-placeholder{background:#fff;border-radius:16px;padding:25px;box-shadow:0 4px 10px rgba(0,0,0,.05);}
    .chart-placeholder{height:300px;display:flex;justify-content:center;align-items:center;color:#b08968;font-weight:600;font-size:1.1rem;}

    /* ---- Product Performance ---- */
    .perf-header{display:flex;gap:12px;align-items:center;margin-bottom:20px;flex-wrap:wrap;}
    .cat-btn{padding:6px 14px;border-radius:20px;background:#fff;color:#4d2e00;font-weight:600;cursor:pointer;transition:.2s;}
    .cat-btn.active,.cat-btn:hover{background:#e2c9a7;}
    .dropdown-menu{--bs-dropdown-min-width:220px;}
    .product-card{background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,.06);display:flex;align-items:center;gap:16px;margin-bottom:12px;}
    .product-img{flex:0 0 70px;height:70px;background:#e9ecef;border-radius:12px;}
    .product-info{flex:1;}
    .product-name{font-weight:600;color:#3b2c23;margin-bottom:4px;}
    .product-sales{font-size:.9rem;color:#8d6e63;}
    .rating{font-size:.85rem;color:#ffb400;}
    .quantity{font-weight:600;color:#4d2e00;}
    .empty-msg{text-align:center;color:#8d6e63;margin-top:40px;font-size:1.1rem;}
    .empty-msg i{font-size:2.5rem;color:#d7b79a;margin-bottom:12px;display:block;}
    .pagination{margin-top:20px;display:flex;gap:8px;justify-content:center;}
    .page-link{padding:6px 12px;border-radius:8px;background:#fff;color:#4d2e00;border:1px solid #ddd;cursor:pointer;}
    .page-link.active{background:#e2c9a7;border-color:#e2c9a7;}
    .page-link.disabled{color:#bbb;cursor:not-allowed;}

    /* ---- Inventory Page ---- */
    .inventory-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;}
    .add-product-btn{background:#4d2e00;color:#fff;padding:8px 16px;border-radius:20px;font-weight:600;cursor:pointer;transition:.2s;}
    .add-product-btn:hover{background:#3a2300;}
    .inventory-table{width:100%;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 10px rgba(0,0,0,.05);}
    .inventory-table table{width:100%;border-collapse:collapse;}
    .inventory-table th{background:#f5e6d3;padding:16px;text-align:left;font-weight:600;color:#4d2e00;}
    .inventory-table td{padding:14px;font-size:.95rem;color:#3b2c23;}
    .inventory-table tr{border-bottom:1px solid #eee;}
    .inventory-table tr:hover{background:#fdf9f0;}
    .stock-low{color:#d32f2f;font-weight:600;}
    .filter-select{padding:6px 12px;border-radius:20px;border:1px solid #ddd;font-size:.9rem;}
    .action-btn{background:none;border:none;color:#6c757d;cursor:pointer;font-size:1.1rem;}
    .action-btn.edit{color:#4d2e00;}
    .action-btn.delete{color:#d32f2f;}

    .modal-content{border-radius:16px;}
    .modal-header{border-bottom:none;padding:20px 24px;}
    .modal-title{font-weight:700;color:#4d2e00;}
    .modal-body{padding:20px 24px;}
    .form-label{font-weight:600;color:#4d2e00;}
    .form-control, .form-select{border-radius:12px;}
    .btn-primary{background:#4d2e00;border:none;border-radius:12px;padding:10px 20px;}
    .btn-primary:hover{background:#3a2300;}

    /* Live Clock Styling */
    #datetime {
      font-size: 0.85rem;
      color: #ffffffff;
      margin-top: 4px;
    }
  </style>
</head>
<body>

  <!-- Sidebar-same as staff & inserted logo, changes, 11-17-25-->
<div class="sidebar d-flex flex-column">

  <!-- Centered Logo -->
  <div class="text-center mb-5">
    <img src="bg/guill.png" alt="Guillermo's Logo" class="img-fluid" style="width:100px; height:auto;">
  </div>

  <!-- Navigation -->
 <ul class="nav nav-pills flex-column mb-auto">
    <li class="nav-item">
      <a class="nav-link active" data-page="dashboard">
        <img src="icons/dasshboard.png" alt="Dashboard" class="me-3" width="22" height="22">
        Dashboard
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-page="funding">
        <img src="icons/funding.png" alt="Funding" class="me-3" width="22" height="22">
        Funding Projections
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-page="performance">
        <img src="icons/performance.png" alt="Performance" class="me-3" width="22" height="22">
        Product Performance
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-page="inventory">
        <img src="icons/inventory.png" alt="Inventory" class="me-3" width="22" height="22">
        Inventory
      </a>
    </li>
  </ul>
</div>

  <!-- Header -->
  <div class="header">
    <div class="title">
      <p>WELCOME BACK, Owner</p>
      <p id="datetime"></p>
    </div>

    <!-- USER PROFILE ICON (TOP-RIGHT) -->
    <div class="user-profile-dropdown">
      <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-person-circle"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
          
        <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> My Profile</a></li>
        <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> Settings</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="#" id="logoutBtn"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
      </ul>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main" id="page-content">

    <!-- ==================== DASHBOARD ==================== -->
    <div class="page active" id="dashboard">
      <div class="row g-4 mb-4">
        <div class="col-md-3">
          <div class="card-box">
            <div class="card-text">
              <p>Total Customer<br><strong><?= number_format($dashboardStats['total_customers'] ?? 0) ?></strong></p>
            </div>
            <img src="icons/customer.png" alt="">
          </div>
        </div>
        <div class="col-md-3">
          <div class="card-box">
            <div class="card-text">
              <p>Total Orders<br><strong><?= number_format($dashboardStats['total_orders'] ?? 0) ?></strong></p>
            </div>
            <img src="icons/orders.png" alt="">
          </div>
        </div>
        <div class="col-md-3">
          <div class="card-box">
            <div class="card-text">
              <p>Total Delivered<br><strong><?= number_format($dashboardStats['total_delivered'] ?? 0) ?></strong></p>
            </div>
            <img src="icons/delivered.png" alt="">
          </div>
        </div>
        <div class="col-md-3">
          <div class="card-box">
            <div class="card-text">
              <p>Total Revenue<br><strong>₱<?= number_format($dashboardStats['total_revenue'] ?? 0, 2) ?></strong></p>
            </div>
            <img src="icons/revenue.png" alt="">
          </div>
        </div>
      </div>

      <div class="row g-4 mt-2">
        <div class="col-md-4">
          <div class="order-summary-card">
            <div class="order-summary-header">
              <h5 class="mb-0">Order Summary</h5>
              <div class="order-tabs">
                <div class="order-tab active">Today</div>
                <div class="order-tab">Weekly</div>
                <div class="order-tab">Monthly</div>
                <div class="underline" style="width:50px; left:10px;"></div>
              </div>
            </div>
            <div class="no-orders-msg">
              <i class="bi bi-inbox"></i>
              <div>There is no orders yet</div>
            </div>
          </div>
        </div>

        <div class="col-md-8">
          <div class="revenue-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="fw-bold mb-0">Revenue</h5>
              <select><option>Today</option><option>Weekly</option><option>Monthly</option><option>Yearly</option></select>
            </div>
            <div class="chart-placeholder">[ Revenue Chart Placeholder ]</div>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== FUNDING PROJECTIONS ==================== -->
    <div class="page" id="funding">
      <h3 class="mb-4" style="color: #4d2e00; font-weight: 700;">Funding Projections</h3>
      <div class="no-funding">
        <i class="bi bi-graph-up"></i>
        <div>There is nothing yet of funding projections</div>
      </div>
    </div>

    <!-- ==================== PRODUCT PERFORMANCE ==================== -->
    <div class="page" id="performance">
      <h3 class="mb-4" style="color:#4d2e00;font-weight:700;">Most Selling Items</h3>

      <div class="perf-header">
        <div class="cat-btn active" data-cat="all">All Categories</div>
        <div class="cat-btn" data-cat="Pizza">Pizza</div>
        <div class="cat-btn" data-cat="Cakes">Cakes</div>

        <div class="dropdown">
          <button id="more-btn" class="cat-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
            More
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" data-cat="Pasta">Pasta</a></li>
            <li><a class="dropdown-item" href="#" data-cat="Rice Meals">Rice Meals</a></li>
            <li><a class="dropdown-item" href="#" data-cat="Coffee Beverages">Coffee Beverages</a></li>
            <li><a class="dropdown-item" href="#" data-cat="NonCoffee">Non-Coffee</a></li>
            <li><a class="dropdown-item" href="#" data-cat="Sandwiches & Salad">Sandwiches & Salad</a></li>
            <li><a class="dropdown-item" href="#" data-cat="Chips">Chips</a></li>
            <li><a class="dropdown-item" href="#" data-cat="Lemon Series">Lemon Series</a></li>
            <li><a class="dropdown-item" href="#" data-cat="Breads">Breads</a></li>
            <li><a class="dropdown-item" href="#" data-cat="Pie, Cookies, Bar">Pie- Cookies- Bar</a></li>
          </ul>
        </div>
      </div>

      <div id="products-list"></div>
      <div class="pagination" id="pagination" style="display:none;">
        <span class="page-link disabled">Previous</span>
        <span class="page-link active">1</span>
        <span class="page-link">2</span>
        <span class="page-link">3</span>
        <span class="page-link">4</span>
        <span class="page-link">Next</span>
      </div>
    </div>

    <!-- ==================== INVENTORY ==================== -->
    <div class="page" id="inventory">
      <h3 class="mb-4" style="display:none;">
      <h4 class="mb-4">Inventory</h4>

      <div class="inventory-header">
        <select id="category-filter" class="filter-select">
          <option value="">All Categories</option>
          <option value="Pasta">Pasta</option>
          <option value="Rice Meals">Rice Meals</option>
          <option value="Coffee Beverages">Coffee Beverages</option>
          <option value="NonCoffee">Non-Coffee</option>
          <option value="Pizza">Pizza</option>
          <option value="Cakes">Cakes</option>
          <option value="Sandwiches & Salad">Sandwiches & Salad</option>
          <option value="Chips">Chips</option>
          <option value="Lemon Series">Lemon Series</option>
          <option value="Breads">Breads</option>
          <option value="Pie, Cookies, Bar">Pie- Cookies- Bar</option>
        </select>

        <button class="add-product-btn" data-bs-toggle="modal" data-bs-target="#productModal">
          Add Product
        </button>
      </div>

      <div class="inventory-table">
        <table id="inventory-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Product Name</th>
              <th>Category</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Low Stock Alert</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- ADD / EDIT PRODUCT MODAL (LOW STOCK AUTO COMPUTE, CHANGES 11-16-25) -->
  <div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Add New Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="productForm">
            <input type="hidden" name="Product_ID" id="productID">
            <div class="mb-3">
              <label class="form-label">Product Name</label>
              <input type="text" class="form-control" name="Product_Name" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="Description" rows="3"></textarea>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Category</label>
                <select class="form-select" name="Category" required>
                  <option value="">Select Category</option>
                  <option value="Pasta">Pasta</option>
                  <option value="Rice Meals">Rice Meals</option>
                  <option value="Coffee Beverages">Coffee Beverages</option>
                  <option value="NonCoffee">Non-Coffee</option>
                  <option value="Pizza">Pizza</option>
                  <option value="Cakes">Cakes</option>
                  <option value="Sandwiches & Salad">Sandwiches & Salad</option>
                  <option value="Chips">Chips</option>
                  <option value="Lemon Series">Lemon Series</option>
                  <option value="Breads">Breads</option>
                  <option value="Pie, Cookies, Bar">Pie- Cookies- Bar</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Sub Category</label>
                <select class="form-select" name="Sub_category">
                  <option value="">None</option>
                  <option value="Hot">Hot</option>
                  <option value="Iced">Iced</option>
                  <option value="Fruit Tea">Fruit Tea</option>
                  <option value="Yogurt">Yogurt</option>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Price (₱)</label>
                <input type="number" step="0.01" class="form-control" name="Price" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Stock Quantity</label>
                <input type="number" class="form-control" name="Stock_Quantity" min="0" required>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveProductBtn">Save Product</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>

    /* ---------- Sidebar & Page Switch (CHANGES, 11-17-25) ---------- */
    const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
    const pages = document.querySelectorAll('.page');

    function showPage(targetId) {
      pages.forEach(p => p.classList.remove('active'));
      const targetPage = document.getElementById(targetId);
      if (targetPage) setTimeout(() => targetPage.classList.add('active'), 50);

      sidebarLinks.forEach(l => l.classList.remove('active'));
      const activeLink = document.querySelector(`.sidebar .nav-link[data-page="${targetId}"]`);
      if (activeLink) activeLink.classList.add('active');

      history.pushState({}, '', `?page=${targetId}`);
    }

    sidebarLinks.forEach(link => {
      link.addEventListener('click', function (e) {
        const page = this.getAttribute('data-page');
        if (page) {
          e.preventDefault();
          showPage(page);
        }
      });
    });

    // Load page from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('page') || 'dashboard';
    showPage(currentPage);

    const dashboardStats = <?= json_encode($dashboardStats, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const performanceData = <?= json_encode($productPerformance, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    let inventory = <?= json_encode($inventoryData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    if (!Array.isArray(inventory)) inventory = [];

    /* ---------- Live Clock ( Welcome Back, Owner) ---------- */
    function updateDateTime() {
      const now = new Date();
      const options = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZone: 'Asia/Manila'
      };
      document.getElementById('datetime').textContent = now.toLocaleString('en-PH', options);
    }
    setInterval(updateDateTime, 1000);
    updateDateTime();

    /* ---------- LOGOUT DEMO ---------- */
    document.getElementById('logoutBtn').addEventListener('click', function(e) {
      e.preventDefault();
      if (confirm('Are you sure you want to logout?')) {
        alert('Logged out successfully!');
      }
    });

    /* ---------- Product Performance Logic ---------- */
    const productContainer = document.getElementById('products-list');
    const paginationEl = document.getElementById('pagination');
    const catButtons = document.querySelectorAll('.cat-btn:not(.dropdown-toggle)');
    const dropdownItems = document.querySelectorAll('.dropdown-item[data-cat]');
    const moreBtn = document.getElementById('more-btn');
    let activeCat = 'all';

    const fallbackProducts = [
      { id: 1, name: "Spicy Tuna Pasta", cat: "Pasta", price: 160, sales: 245, rating: 4.5, reviews: 89 },
      { id: 2, name: "Margherita Pizza", cat: "Pizza", price: 320, sales: 412, rating: 4.8, reviews: 156 },
      { id: 3, name: "Chocolate Cake Slice", cat: "Cakes", price: 120, sales: 189, rating: 4.7, reviews: 67 },
      { id: 4, name: "Iced Latte", cat: "Coffee Beverages", price: 130, sales: 567, rating: 4.6, reviews: 210 },
      { id: 5, name: "Ham & Cheese Sandwich", cat: "Sandwiches & Salad", price: 95, sales: 134, rating: 4.3, reviews: 45 },
      { id: 6, name: "Lemon Bar", cat: "Pie, Cookies, Bar", price: 80, sales: 98, rating: 4.4, reviews: 32 }
    ];

    const dummyProducts = Array.isArray(performanceData) && performanceData.length
      ? performanceData.map(item => ({
          id: item.id ?? null,
          name: item.name ?? 'Unnamed Product',
          cat: item.cat ?? 'Uncategorized',
          price: Number(item.price ?? 0),
          sales: Number(item.sales ?? 0),
          rating: Number(item.rating ?? 0) || 4.5,
          reviews: Number(item.reviews ?? 0)
        }))
      : fallbackProducts;

    function renderProducts(filter = 'all') {
      const isAll = filter === 'all';
      const filtered = isAll ? dummyProducts : dummyProducts.filter(p => p.cat === filter);

      if (filtered.length === 0) {
        productContainer.innerHTML = `<div class="empty-msg"><i class="bi bi-inbox"></i><div>No products in this category yet</div></div>`;
        paginationEl.style.display = 'none';
        return;
      }

      const html = filtered.map(p => `
        <div class="product-card">
          <div class="product-img"></div>
          <div class="product-info">
            <div class="product-name">${p.name}</div>
            <div class="product-sales">${p.sales} Total Sales</div>
            <div class="rating">${'★'.repeat(Math.floor(p.rating))}${'☆'.repeat(5-Math.floor(p.rating))} (${p.reviews} reviews)</div>
          </div>
          <div class="quantity">${p.sales} pcs</div>
        </div>`).join('');

      productContainer.innerHTML = html;
      paginationEl.style.display = filtered.length >= 6 ? 'flex' : 'none';
    }

    catButtons.forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        activeCat = this.getAttribute('data-cat');
        renderProducts(activeCat);
      });
    });

    dropdownItems.forEach(item => {
      item.addEventListener('click', function (e) {
        e.preventDefault();
        const cat = this.getAttribute('data-cat');
        moreBtn.innerHTML = `${cat} <i class="bi bi-chevron-down ms-1"></i>`;
        document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
        moreBtn.classList.add('active');
        activeCat = cat;
        renderProducts(activeCat);
      });
    });

    renderProducts();

    /* ---------- Inventory Logic ---------- */
    const ownerEndpoint = window.location.pathname;
    const productIdField = document.getElementById('productID');
    const inventoryTable = document.getElementById('inventory-table').querySelector('tbody');
    const categoryFilter = document.getElementById('category-filter');
    const modal = new bootstrap.Modal(document.getElementById('productModal'));
    const modalTitle = document.getElementById('modalTitle');
    const productForm = document.getElementById('productForm');
    const saveBtn = document.getElementById('saveProductBtn');

    function normalizeProduct(product) {
      const id = Number.parseInt(product.Product_ID ?? product.product_id ?? 0, 10) || 0;
      const nameValue = (product.Product_Name ?? product.product_name ?? 'Unnamed Product').toString().trim();
      const description = (product.Description ?? product.description ?? '').toString().trim() || null;
      const category = (product.Category ?? product.category ?? '').toString().trim();
      const subCategory = (product.Sub_category ?? product.sub_category ?? '').toString().trim() || null;
      const price = Number(product.Price ?? product.price ?? 0) || 0;
      const stock = Number.parseInt(product.Stock_Quantity ?? product.stock_quantity ?? 0, 10);
      const lowAlert = (product.Low_Stock_Alert ?? product.low_stock_alert ?? 'Safe').toString().trim() || 'Safe';

      return {
        Product_ID: id,
        Product_Name: nameValue === '' ? 'Unnamed Product' : nameValue,
        Description: description,
        Category: category,
        Sub_category: subCategory,
        Price: price,
        Stock_Quantity: Number.isInteger(stock) ? stock : 0,
        Low_Stock_Alert: lowAlert,
      };
    }

    inventory = inventory.map(normalizeProduct);
//AUTOMATED LOW STOCK ALERT COMPUTE, CHANGES 11-17-25//
    function computeStockAlert(stock) {
    if (stock >= 20) return 'Safe';
    if (stock >= 10) return 'Low';
    if (stock >= 1) return 'Critical';
    return 'Out of Stock';
}

    function showEmptyState(message = 'There are no products in inventory yet.') {
      inventoryTable.innerHTML = `
        <tr>
          <td colspan="7" class="text-center py-5 text-muted">
            <i class="bi bi-inbox" style="font-size:2.5rem;color:#d7b79a;display:block;margin-bottom:10px;"></i>
            ${message}
          </td>
        </tr>`;
    }

    function renderInventory(filter = '') {
      const filtered = filter ? inventory.filter(p => p.Category === filter) : [...inventory];

      if (!filtered.length) {
        const message = filter ? 'No products found in this category.' : 'There are no products in inventory yet.';
        showEmptyState(message);
        return;
      }

      const rows = filtered.map(p => {
        const lowClass = ['low', 'critical'].includes(p.Low_Stock_Alert.toLowerCase()) ? 'stock-low' : '';
        return `
        <tr data-id="${p.Product_ID}">
          <td>#${p.Product_ID}</td>
          <td>${p.Product_Name}</td>
          <td>${p.Category || 'Uncategorized'}</td>
          <td>₱${Number(p.Price).toFixed(2)}</td>
          <td>${p.Stock_Quantity}</td>
          <td class="${lowClass}">${p.Low_Stock_Alert}</td>
          <td>
            <button class="action-btn edit" title="Edit"><i class="bi bi-pencil"></i></button>
            <button class="action-btn delete" title="Delete"><i class="bi bi-trash"></i></button>
          </td>
        </tr>`;
      }).join('');

      inventoryTable.innerHTML = rows;
    }

    function resetFormDefaults() {
      productForm.reset();
      productIdField.value = '';
    }

    async function callInventoryApi(action, payload = {}) {
      const response = await fetch(`${ownerEndpoint}?action=${encodeURIComponent(action)}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      let body;
      try { body = await response.json(); } catch { throw new Error('Invalid response.'); }

      if (!response.ok || body.status !== 'success') {
        throw new Error(body.message || 'Request failed.');
      }
      return body;
    }

    categoryFilter.addEventListener('change', () => renderInventory(categoryFilter.value));

    document.querySelector('.add-product-btn').addEventListener('click', () => {
      modalTitle.textContent = 'Add New Product';
      resetFormDefaults();
      modal.show();
    });

    inventoryTable.addEventListener('click', async e => {
      const row = e.target.closest('tr');
      if (!row) return;
      const id = Number.parseInt(row.dataset.id, 10);
      if (!Number.isInteger(id)) return;

      const editBtn = e.target.closest('.edit');
      const deleteBtn = e.target.closest('.delete');

      if (editBtn) {
        const product = inventory.find(item => item.Product_ID === id);
        if (!product) return;

        modalTitle.textContent = 'Edit Product';
        productIdField.value = product.Product_ID;
        productForm.Product_Name.value = product.Product_Name;
        productForm.Description.value = product.Description || '';
        productForm.Category.value = product.Category;
        productForm.Sub_category.value = product.Sub_category || '';
        productForm.Price.value = product.Price;
        productForm.Stock_Quantity.value = product.Stock_Quantity;
        modal.show();
        return;
      }

      if (deleteBtn && confirm('Delete this product permanently?')) {
        try {
          await callInventoryApi('delete-product', { Product_ID: id });
          inventory = inventory.filter(item => item.Product_ID !== id);
          renderInventory(categoryFilter.value);
          alert('Product deleted successfully.');
        } catch (error) {
          alert(error.message || 'Unable to delete product.');
        }
      }
    });

    saveBtn.addEventListener('click', async () => {
      const formData = new FormData(productForm);
      const payload = Object.fromEntries(formData.entries());

      payload.Product_Name = (payload.Product_Name ?? '').trim();
      if (!payload.Product_Name) return alert('Please enter a product name.');

      payload.Category = (payload.Category ?? '').trim();
      if (!payload.Category) return alert('Please select a category.');

      payload.Price = Number.parseFloat(payload.Price);
      if (!Number.isFinite(payload.Price) || payload.Price < 0) return alert('Please provide a valid price.');

      payload.Stock_Quantity = Number.parseInt(payload.Stock_Quantity, 10);
      if (!Number.isInteger(payload.Stock_Quantity) || payload.Stock_Quantity < 0) return alert('Please provide a valid stock quantity.');

      // Auto-compute Low Stock Alert, CHANGES 11-17-25 //
     payload.Low_Stock_Alert = computeStockAlert(payload.Stock_Quantity);

      payload.Description = (payload.Description ?? '').trim() || null;
      payload.Sub_category = (payload.Sub_category ?? '').trim() || null;

      const isUpdate = Boolean(payload.Product_ID);
      if (!isUpdate) delete payload.Product_ID;

      try {
        saveBtn.disabled = true;
        const action = isUpdate ? 'update-product' : 'create-product';
        const { data } = await callInventoryApi(action, payload);
        const product = normalizeProduct(data);

        if (isUpdate) {
          const index = inventory.findIndex(item => item.Product_ID === product.Product_ID);
          if (index !== -1) inventory[index] = product;
          else inventory.push(product);
        } else {
          inventory.push(product);
        }

        inventory.sort((a, b) => a.Product_ID - b.Product_ID);
        modal.hide();
        resetFormDefaults();
        renderInventory(categoryFilter.value);
        alert('Product saved successfully.');
      } catch (error) {
        alert(error.message || 'Unable to save product.');
      } finally {
        saveBtn.disabled = false;
      }
    });

    renderInventory();
  </script>
</body>
</html>