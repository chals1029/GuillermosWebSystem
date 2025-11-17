<?php
session_start();
require_once __DIR__ . '/../../Controllers/StaffController.php';

if (isset($_GET['action']) || isset($_POST['action'])) {
    $controller = new StaffController();
    $controller->handleAjax();
    exit;
}
// Load products for inventory
$controller = new StaffController();
$products = $controller->getInventoryProducts();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Guillermo’s Staff Dashboard</title>

  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
     body {font-family:'Poppins',sans-serif;background:#fefcf7;margin:0;padding:0;}
    .sidebar{width:250px;height:100vh;background:#fff;border-right:1px solid #dcdcdc;position:fixed;top:0;left:0;padding:1rem;}
    .sidebar h4{font-weight:bold;font-size:50px;color:#4d2e00;font-family:'Brush Script MT',cursive;}
    .sidebar .nav-link{color:#4d2e00;font-weight:500;margin-bottom:.5rem;border-radius:6px;cursor:pointer;}
    .sidebar .nav-link.active{background:#c1976b;color:#fff;}
    .submenu{padding-left:20px;}
    .submenu a{display:block;color:#4d2e00;text-decoration:none;padding:4px 0;font-size:15px;}
    .submenu a:hover{color:#c1976b;}
    .header{
  background:#6B4F3F;
  color:#fff;
  padding:20px 40px;
  position:fixed;
  left:250px;
  right:0;
  top:0;
  z-index:10;
  box-shadow:0 2px 10px rgba(0,0,0,.1);
  display:flex;
  justify-content:space-between;
  align-items:center;
}
.header .title p{margin:0;font-size:1rem;}
    .content{
  margin-left:250px;
  margin-top:100px;          
  padding:20px 40px;
  transition:margin-left .3s;
}
    .card-custom{background:#d7b79a;color:#3b2c23;border:none;border-radius:10px;text-align:center;padding:1.5rem;}
    .card-custom h5{margin-top:.5rem;}

    /* QUICK ADD CARD */
    .quick-add {
      background:#fff;
      background-color: #f5e6d3;
      border:1px solid #b9af7eff;
      border-radius:14px;
      padding:1.75rem;
      text-align:center;
      box-shadow:0 4px 15px rgba(0,0,0,.08);
      transition:all .2s ease;
    }
    .quick-add:hover{box-shadow:0 8px 25px rgba(0,0,0,.12);transform:translateY(-2px);}

    .btn-quick{background:#6f4e37;color:#fff;border-radius:30px;padding:10px 28px;font-weight:500;}
    .btn-quick:hover{background:#c1976b;color:#fff;transform:scale(1.03);transition:all .2s;}

    .product-card{border:1px solid #ddd;border-radius:10px;overflow:hidden;transition:all .2s;cursor:pointer;text-align:center;}
    .product-card:hover{box-shadow:0 4px 12px rgba(0,0,0,.1);transform:translateY(-2px);}
    .product-card img{width:100%;height:100px;object-fit:cover;}
    .product-card .price{font-weight:bold;color:#6f4e37;}
    .order-summary-table th{background:#6f4e37;color:#fff;}

    /* Inline Order Card */
    #inlineOrderCard{display:none;margin-top:1.5rem;background:#fff;border-radius:14px;padding:2rem;box-shadow:0 6px 25px rgba(0,0,0,.1);border:1px solid #eee;}
    #inlineOrderCard.show{display:block;}

    /* Scrollable product list */
    .product-list-wrapper{max-height:500px;overflow-y:auto;padding-right:8px;}
    .product-list-wrapper::-webkit-scrollbar{width:6px;}
    .product-list-wrapper::-webkit-scrollbar-thumb{background:#c1976b;border-radius:3px;}

    /* Category filter */
    .category-filter{margin-bottom:1rem;}
    .category-filter select{width:100%;max-width:300px;}

    /* Inventory Table */
    .inventory-table thead {
      background-color: #f5d9b3;
      color: #4d2e00;
      font-weight: 600;
    }
    .inventory-table th, .inventory-table td {
      vertical-align: middle;
      font-size: 14px;
    }
    .stock-badge {
      font-weight: 600;
    }
    .empty-state {
      text-align: center;
      padding: 3rem 1rem;
      color: #6c757d;
    }
    .empty-state i {
      font-size: 2.5rem;
      color: #c1976b;
      margin-bottom: 1rem;
    }
    /* Sidebar Icon Styling */
.sidebar .nav-link {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  transition: all 0.3s ease;
  position: relative;
}

.sidebar .nav-link .icon-img {
  filter: brightness(0) invert(27%) sepia(80%) saturate(300%) hue-rotate(335deg) brightness(90%);
  
}

.sidebar .nav-link.active .icon-img,
.sidebar .nav-link:hover .icon-img {
  
}

.sidebar .nav-link .bi-chevron-down {
  font-size: 0.9rem;
  
}
.sidebar .nav-link[aria-expanded="true"] .bi-chevron-down {
  
}

/* Submenu icons (Bootstrap Icons) */
.submenu a {
  display: flex;
  align-items: center;
  padding: 0.6rem 1rem 0.6rem 3.5rem !important;
  font-size: 0.95rem;
}
.submenu a i {
  font-size: 1rem;
  color: #8d6e63;
}
.submenu a:hover i {
  color: #c1976b;
}
  </style>
</head>
<body>

  <!-- Sidebar- inserting logo, changes 11-17-25-->
  <div class="sidebar d-flex flex-column">

  <!-- Centered Logo -->
  <div class="text-center mb-5">
    <img src="bg/guill.png" alt="Guillermo's Logo" class="img-fluid" style="width:100px; height:auto;">
  </div>

    <ul class="nav nav-pills flex-column mb-auto">
     <!-- Dashboard -->
    <li class="nav-item">
      <a class="nav-link active" onclick="showContent('dashboard')">
        <img src="icons/dasshboard.png" alt="Dashboard" class="me-3 icon-img" width="22" height="22">
        Dashboard
      </a>
    </li>
    <!-- Order Management (with submenu) -->
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#orderMenu" role="button">
        <img src="icons/orderss.png" alt="Orders" class="me-3 icon-img" width="22" height="22">
        Order Management 
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <div class="collapse submenu" id="orderMenu">
        <a href="#" onclick="showContent('process-bulk')">
          <i class="bi bi-box me-2"></i> Process Bulk
        </a>
        <a href="#" onclick="showContent('advance-reservation')">
          <i class="bi bi-calendar-check me-2"></i> Advance Reservation
        </a>
      </div>
    </li>
    <!-- Inventory -->
    <li class="nav-item">
      <a class="nav-link" onclick="showContent('inventory')">
        <img src="icons/inventory.png" alt="Inventory" class="me-3 icon-img" width="22" height="22">
        Inventory
      </a>
    </li>

  </ul>
</div>

  <!-- Topbar-inserting liveclock, changes, 11-17-25 -->
  <div class="header">
  <div class="title">
    <p>WELCOME BACK, Staff</p>
    <p id="datetime" style="font-size:0.85rem;color:#fff;margin-top:4px;"></p>
  </div>

  <div class="d-flex align-items-center">
    <!-- Bell -->
    <i class="bi bi-bell me-3" style="font-size:1.5rem;cursor:pointer;" title="Notifications"></i>

    <!-- User icon  -->
    <i class="bi bi-person-circle" 
       style="font-size:1.5rem;cursor:pointer;" 
       data-bs-toggle="modal" 
       data-bs-target="#profileModal"></i>
  </div>
</div>
  

  <!-- Content -->
  <div class="content">

    <!-- Dashboard Section -->
    <div id="dashboard-section">

      <!-- Stat Cards -->
      <div class="row mb-4">
        <div class="col-md-4"><div class="card card-custom"><h3>Pending Orders</h3></div></div>
        <div class="col-md-4"><div class="card card-custom"><h3>Complete Today</h3></div></div>
        <div class="col-md-4"><div class="card card-custom"><h3>Reserve Orders</h3></div></div>
      </div>

      <!-- QUICK ADD CARD -->
      <div class="quick-add mt-4">
        <h5 class="mb-3 text-dark fw-semibold">Quick Add</h5>
        <button id="toggleOrderCard" class="btn btn-quick">
          Add Customer Order
        </button>

        <!-- Inline Order Card -->
        <div id="inlineOrderCard">
          <h5 class="mb-3">Add Customer Order</h5>

          <div class="row">
            <!-- LEFT – Product Grid + Filter -->
            <div class="col-lg-8">

              <!-- Category Filter -->
              <div class="category-filter d-flex align-items-center">
                <label class="me-2 fw-semibold">Category:</label>
                <select id="categoryFilter" class="form-select category-filter">
                  <option value="all">All</option>
                  <option value="pasta">Pasta</option>
                  <option value="rice-meals">Rice Meals</option>
                  <option value="coffee">Coffee Beverages</option>
                  <option value="noncoffee">NonCoffee</option>
                  <option value="pizza">Pizza</option>
                  <option value="cakes">Cakes</option>
                  <option value="sandwiches">Sandwiches &amp; Salad</option>
                  <option value="chips">Chips</option>
                  <option value="lemon">Lemon Series</option>
                  <option value="breads">Breads</option>
                  <option value="pie- cookies- bar">Pie- Cookies- Bar</option>
                </select>
              </div>

              <!-- Product Grid (scrollable) -->
              <div class="product-list-wrapper">
                <div class="row g-3" id="productGrid">
                  <!-- ALL PRODUCTS – each has data-category attribute -->
                  <!-- Pasta -->
                  <div class="col-md-4 product-item" data-category="pasta">
                    <div class="product-card" data-name="Spaghetti" data-price="180">
                      <img src="https://via.placeholder.com/150/FF6B6B/FFFFFF?text=Spaghetti">
                      <div class="p-2"><h6>Spaghetti</h6><p class="price">₱180</p></div>
                    </div>
                  </div>
                  <div class="col-md-4 product-item" data-category="pasta">
                    <div class="product-card" data-name="Carbonara" data-price="220">
                      <img src="https://via.placeholder.com/150/4ECDC4/FFFFFF?text=Carbonara">
                      <div class="p-2"><h6>Carbonara</h6><p class="price">₱220</p></div>
                    </div>
                  </div>

                  <!-- Rice Meals -->
                  <div class="col-md-4 product-item" data-category="rice-meals">
                    <div class="product-card" data-name="Beef Caldereta" data-price="200">
                      <img src="https://via.placeholder.com/150/FFA726/FFFFFF?text=Caldereta">
                      <div class="p-2"><h6>Beef Caldereta</h6><p class="price">₱200</p></div>
                    </div>
                  </div>
                  <div class="col-md-4 product-item" data-category="rice-meals">
                    <div class="product-card" data-name="Chicken Adobo" data-price="180">
                      <img src="https://via.placeholder.com/150/66BB6A/FFFFFF?text=Adobo">
                      <div class="p-2"><h6>Chicken Adobo</h6><p class="price">₱180</p></div>
                    </div>
                  </div>

                  <!-- Coffee Beverages -->
                  <div class="col-md-4 product-item" data-category="coffee">
                    <div class="product-card" data-name="Americano" data-price="120">
                      <img src="https://via.placeholder.com/150/3E2723/FFFFFF?text=Americano">
                      <div class="p-2"><h6>Americano</h6><p class="price">₱120</p></div>
                    </div>
                  </div>
                  <div class="col-md-4 product-item" data-category="coffee">
                    <div class="product-card" data-name="Latte" data-price="150">
                      <img src="https://via.placeholder.com/150/6D4C41/FFFFFF?text=Latte">
                      <div class="p-2"><h6>Latte</h6><p class="price">₱150</p></div>
                    </div>
                  </div>

                  <!-- NonCoffee -->
                  <div class="col-md-4 product-item" data-category="noncoffee">
                    <div class="product-card" data-name="Iced Tea" data-price="60">
                      <img src="https://via.placeholder.com/150/29B6F6/FFFFFF?text=Iced+Tea">
                      <div class="p-2"><h6>Iced Tea</h6><p class="price">₱60</p></div>
                    </div>
                  </div>
                  <div class="col-md-4 product-item" data-category="noncoffee">
                    <div class="product-card" data-name="Lemonade" data-price="80">
                      <img src="https://via.placeholder.com/150/FFEB3B/FFFFFF?text=Lemonade">
                      <div class="p-2"><h6>Lemonade</h6><p class="price">₱80</p></div>
                    </div>
                  </div>

                  <!-- Pizza -->
                  <div class="col-md-4 product-item" data-category="pizza">
                    <div class="product-card" data-name="Hawaiian" data-price="350">
                      <img src="https://via.placeholder.com/150/FF5252/FFFFFF?text=Hawaiian">
                      <div class="p-2"><h6>Hawaiian</h6><p class="price">₱350</p></div>
                    </div>
                  </div>

                  <!-- Cakes -->
                  <div class="col-md-4 product-item" data-category="cakes">
                    <div class="product-card" data-name="Chocolate Cake" data-price="850">
                      <img src="https://via.placeholder.com/150/8D6E63/FFFFFF?text=Choco+Cake">
                      <div class="p-2"><h6>Chocolate Cake</h6><p class="price">₱850</p></div>
                    </div>
                  </div>

                  <!-- Sandwiches & Salad -->
                  <div class="col-md-4 product-item" data-category="sandwiches">
                    <div class="product-card" data-name="Clubhouse" data-price="180">
                      <img src="https://via.placeholder.com/150/FF9800/FFFFFF?text=Clubhouse">
                      <div class="p-2"><h6>Clubhouse</h6><p class="price">₱180</p></div>
                    </div>
                  </div>
                  <div class="col-md-4 product-item" data-category="sandwiches">
                    <div class="product-card" data-name="Caesar Salad" data-price="150">
                      <img src="https://via.placeholder.com/150/4CAF50/FFFFFF?text=Salad">
                      <div class="p-2"><h6>Caesar Salad</h6><p class="price">₱150</p></div>
                    </div>
                  </div>

                  <!-- Chips -->
                  <div class="col-md-4 product-item" data-category="chips">
                    <div class="product-card" data-name="Potato Chips" data-price="50">
                      <img src="https://via.placeholder.com/150/FFC107/FFFFFF?text=Chips">
                      <div class="p-2"><h6>Potato Chips</h6><p class="price">₱50</p></div>
                    </div>
                  </div>

                  <!-- Lemon Series -->
                  <div class="col-md-4 product-item" data-category="lemon">
                    <div class="product-card" data-name="Lemon Bar" data-price="90">
                      <img src="https://via.placeholder.com/150/FFEB3B/FFFFFF?text=Lemon+Bar">
                      <div class="p-2"><h6>Lemon Bar</h6><p class="price">₱90</p></div>
                    </div>
                  </div>

                  <!-- Fruits & Yogurt -->
                  <div class="col-md-4 product-item" data-category="fruits">
                    <div class="product-card" data-name="Fruit Parfait" data-price="130">
                      <img src="https://via.placeholder.com/150/FF4081/FFFFFF?text=Parfait">
                      <div class="p-2"><h6>Fruit Parfait</h6><p class="price">₱130</p></div>
                    </div>
                  </div>

                  <!-- Breads -->
                  <div class="col-md-4 product-item" data-category="breads">
                    <div class="product-card" data-name="Pandesal" data-price="40">
                      <img src="https://via.placeholder.com/150/8D6E63/FFFFFF?text=Pandesal">
                      <div class="p-2"><h6>Pandesal</h6><p class="price">₱40</p></div>
                    </div>
                  </div>

                  <!-- Pie -->
                  <div class="col-md-4 product-item" data-category="pie">
                    <div class="product-card" data-name="Apple Pie" data-price="280">
                      <img src="https://via.placeholder.com/150/F44336/FFFFFF?text=Apple+Pie">
                      <div class="p-2"><h6>Apple Pie</h6><p class="price">₱280</p></div>
                    </div>
                  </div>

                  <!-- Cookies -->
                  <div class="col-md-4 product-item" data-category="cookies">
                    <div class="product-card" data-name="Choco Chip" data-price="60">
                      <img src="https://via.placeholder.com/150/795548/FFFFFF?text=Choco+Chip">
                      <div class="p-2"><h6>Choco Chip</h6><p class="price">₱60</p></div>
                    </div>
                  </div>

                  <!-- Bar -->
                  <div class="col-md-4 product-item" data-category="bar">
                    <div class="product-card" data-name="Granola Bar" data-price="75">
                      <img src="https://via.placeholder.com/150/607D8B/FFFFFF?text=Granola">
                      <div class="p-2"><h6>Granola Bar</h6><p class="price">₱75</p></div>
                    </div>
                  </div>

                </div>
              </div>
            </div>

            <!-- RIGHT – Order Summary -->
            <div class="col-lg-4">
              <h6>Order Summary</h6>
              <div class="border rounded p-3 mb-3" style="max-height:350px;overflow-y:auto;">
                <table class="table table-sm order-summary-table">
                  <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th></th></tr></thead>
                  <tbody id="order-items-list"></tbody>
                </table>
              </div>

              <div class="mb-3"><label>Customer Name</label><input type="text" class="form-control" id="customerName" placeholder="Enter name"></div>
              <div class="mb-3"><label>Order Type</label>
                <select class="form-select" id="orderType">
                  <option>Dine In</option><option>Take Out</option>
                </select>
              </div>
              <div class="mb-3"><label>Total Amount</label><h5 class="text-end text-success" id="totalAmount">₱0.00</h5></div>
              <div class="mb-3"><label>Notes</label><textarea class="form-control" rows="2" placeholder="Special instructions"></textarea></div>

              <div class="d-flex gap-2">
                <button class="btn btn-secondary flex-fill" onclick="hideInlineOrder()">Cancel</button>
                <button class="btn btn-quick flex-fill" onclick="submitOrder()">Submit Order</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Process Bulk Section -->
    <div id="process-bulk-section" style="display:none;">
      <h4 class="mb-4">Process Bulk Orders</h4>
      <div class="row mb-4">
        <div class="col-md-3"><div class="card card-custom text-center"><h5>Total Items:</h5></div></div>
        <div class="col-md-3"><div class="card card-custom text-center"><h5>Value in Stock:</h5></div></div>
        <div class="col-md-3"><div class="card card-custom text-center"><h5>Low Stock:</h5></div></div>
        <div class="col-md-3"><div class="card card-custom text-center"><h5>Items to Re-Order:</h5></div></div>
      </div>
      <div class="card card-custom p-4 text-start">
        <h5 class="mb-3">Stock Level Overview:</h5>
        <div class="d-flex justify-content-between text-center fw-semibold">
          <div>Sun</div><div>Mon</div><div>Tues</div><div>Wed</div><div>Thur</div><div>Fri</div><div>Sat</div>
        </div>
        <div class="mt-3" style="height:160px; background:#d2aa7d; border-radius:10px; opacity:.7;"></div>
      </div>
    </div>

    <!-- Advance Reservation Section -->
    <div id="advance-reservation-section" style="display:none;">
      <h4 class="mb-4">Advance Reservation</h4>
      <div class="row mb-4">
        <div class="col-md-3"><div class="card card-custom text-center p-3"><h5>Total of Reservations</h5><h3 class="fw-bold" id="totalReservations">69</h3></div></div>
        <div class="col-md-3"><div class="card card-custom text-center p-3"><h5>Pending Confirmations</h5><h3 class="fw-bold" id="pendingCount">96</h3></div></div>
        <div class="col-md-3"><div class="card card-custom text-center p-3"><h5>Completed Orders</h5><h3 class="fw-bold" id="completedCount">69</h3></div></div>
        <div class="col-md-3"><div class="card card-custom text-center p-3"><h5>Cancelled Reservations</h5><h3 class="fw-bold" id="cancelledCount">96</h3></div></div>
      </div>
      <div class="card card-custom p-4 text-start mb-4">
        <h6 class="fw-semibold mb-3">Reservation Status Overview:</h6>
        <div class="d-flex gap-3 align-items-center mb-3">
          <span class="badge rounded-pill bg-warning">Pending</span>
          <span class="badge rounded-pill bg-success">Confirmed</span>
          <span class="badge rounded-pill bg-danger">Cancelled</span>
        </div>
        <div class="d-flex justify-content-between fw-semibold text-center">
          <div>jan</div><div>feb</div><div>mar</div><div>apr</div><div>may</div><div>june</div>
        </div>
        <div class="mt-3" style="height:160px; background:#d2aa7d; border-radius:10px; opacity:0.7;"></div>
      </div>
      <div class="d-flex align-items-center mb-3">
        <div class="input-group" style="max-width:400px;">
          <span class="input-group-text bg-light border-end-0">Search</span>
          <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Quick Search">
        </div>
        <button class="btn btn-quick ms-3" id="searchBtn">Find Customer Reservations</button>
      </div>
      <div class="table-responsive">
        <table id="reservationTable" class="table align-middle text-center" style="background-color:#f5e9dd;">
          <thead class="table-light">
            <tr>
              <th>Reservation ID</th>
              <th>Customer Name</th>
              <th>Date & Time</th>
              <th>Status</th>
              <th>Action</th>
              <th>Confirm & Cancel</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>R001</td>
              <td>Juan Dela Cruz</td>
              <td>Nov 14, 2025 - 2:00 PM</td>
              <td class="status">Pending</td>
              <td><button class="btn btn-sm btn-quick view-btn">View Details</button></td>
              <td>
                <button class="btn btn-sm btn-success me-2 confirm-btn">Confirm</button>
                <button class="btn btn-sm btn-danger cancel-btn">Cancel</button>
              </td>
            </tr>
            <tr>
              <td>R002</td>
              <td>Maria Santos</td>
              <td>Nov 15, 2025 - 1:00 PM</td>
              <td class="status">Confirmed</td>
              <td><button class="btn btn-sm btn-quick view-btn">View Details</button></td>
              <td>
                <button class="btn btn-sm btn-success me-2 confirm-btn">Confirm</button>
                <button class="btn btn-sm btn-danger cancel-btn">Cancel</button>
              </td>
            </tr>
            <tr>
              <td>R003</td>
              <td>Carlos Reyes</td>
              <td>Nov 16, 2025 - 5:00 PM</td>
              <td class="status">Cancelled</td>
              <td><button class="btn btn-sm btn-quick view-btn">View Details</button></td>
              <td>
                <button class="btn btn-sm btn-success me-2 confirm-btn">Confirm</button>
                <button class="btn btn-sm btn-danger cancel-btn">Cancel</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Inventory Section -->
    <div id="inventory-section" style="display:none;">
      <h4 class="mb-4">Inventory</h4>

      <!-- Category Filter -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="category-filter d-flex align-items-center">
          <label class="me-2 fw-semibold">Category:</label>
          <select id="inventoryCategoryFilter" class="form-select" style="max-width:250px;">
            <option value="all">All Categories</option>
            <option value="pasta">Pasta</option>
            <option value="rice-meals">Rice Meals</option>
            <option value="coffee">Coffee Beverages</option>
            <option value="noncoffee">NonCoffee</option>
            <option value="pizza">Pizza</option>
            <option value="cakes">Cakes</option>
            <option value="sandwiches">Sandwiches & Salad</option>
            <option value="chips">Chips</option>
            <option value="lemon">Lemon Series</option>
            <option value="breads">Breads</option>
            <option value="pie- cookies- bar">Pie- Cookies- Bar</option>
          </select>
        </div>
      </div>

      <!-- Inventory Table -->
      <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0 inventory-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Product Name</th>
                  <th>Category</th>
                  <th>Price</th>
                  <th>Stock</th>
                  <th>Low Stock Alert</th>
                </tr>
              </thead>
              <tbody id="inventoryTableBody">
                <?php foreach ($products as $p): 
                  $badge = $p['Low_Stock_Alert'] === 'Critical' ? 'bg-danger' : 
                          ($p['Low_Stock_Alert'] === 'Low' ? 'bg-warning' : 'bg-success');
                  $stockClass = $p['Stock_Quantity'] <= 5 ? 'text-danger' : 
                               ($p['Stock_Quantity'] <= 10 ? 'text-warning' : 'text-success');
                  $dataCat = strtolower(str_replace([' ', '&', '-'], '', $p['Category']));
                  if ($p['Category'] === 'Pie-Cookies-Bar') $dataCat = 'pie-cookies-bar';
                ?>
                  <tr data-category="<?= $dataCat ?>">
                    <td>P<?= str_pad($p['Product_ID'], 3, '0', STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($p['Product_Name']) ?></td>
                    <td><?= $p['Category'] ?></td>
                    <td>₱<?= number_format($p['Price'], 2) ?></td>
                    <td><span class="stock-badge <?= $stockClass ?> fw-bold"><?= $p['Stock_Quantity'] ?></span></td>
                    <td><span class="badge <?= $badge ?>"><?= $p['Low_Stock_Alert'] ?: 'Safe' ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div id="emptyInventory" class="empty-state" style="display:none;">
            <i class="bi bi-box-seam"></i>
            <p>No products match the selected category.</p>
          </div>
        </div>
      </div>
    </div>

  <!-- Staff Profile Modal -->
  <div class="modal fade" id="profileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content p-4">
        <div class="modal-header border-0">
          <h5 class="modal-title">Staff Profile</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-4 text-center">
              <img src="https://via.placeholder.com/150" id="profile-picture" class="rounded-circle shadow" width="150" height="150">
              <input type="file" id="profile-upload" class="form-control mt-2" accept="image/*" style="display:none;">
              <button class="btn btn-sm btn-quick mt-2" onclick="document.getElementById('profile-upload').click();">
                Upload Picture
              </button>
            </div>
            <div class="col-md-8">
              <form id="profile-form">
                <div class="mb-3"><label>Full Name</label><input type="text" class="form-control" value="Michelle G. Vivas"></div>
                <div class="mb-3"><label>Role</label><input type="text" class="form-control" value="Staff" readonly></div>
                <div class="mb-3"><label>Email</label><input type="email" class="form-control" value="michelle@example.com" readonly></div>
                <div class="d-flex justify-content-between mt-4">
                  <button type="button" class="btn btn-quick" onclick="saveProfile()">Save Changes</button>
                  <button type="button" class="btn btn-danger" onclick="logout()">Logout</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // ---------- Section Navigation ----------
    function showContent(section) {
      const sections = ['dashboard', 'process-bulk', 'advance-reservation', 'inventory'];
      sections.forEach(s => {
        const el = document.getElementById(s + '-section');
        if (el) el.style.display = 'none';
      });
      document.querySelectorAll('[id$="-section"]').forEach(s => s.style.display = 'none');
      document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
      document.getElementById(section + '-section').style.display = 'block';
      event.currentTarget.classList.add('active');
    }
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

    // ---------- Profile ----------
    document.getElementById('profile-upload').addEventListener('change', function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('profile-picture').src = e.target.result;
        reader.readAsDataURL(file);
      }
    });
    function saveProfile() { alert('Profile saved!'); }
    function logout() { if (confirm('Logout?')) window.location.href = 'login.php'; }

    // ---------- Inline Order Card ----------
    document.getElementById('toggleOrderCard').addEventListener('click', function (e) {
      e.preventDefault();
      const card = document.getElementById('inlineOrderCard');
      card.classList.toggle('show');
    });

    function hideInlineOrder() {
      const card = document.getElementById('inlineOrderCard');
      card.classList.remove('show');
      setTimeout(resetOrderForm, 300);
    }

    // ---------- Category Filter (Quick Add) ----------
    const filterSelect = document.getElementById('categoryFilter');
    const productItems = document.querySelectorAll('.product-item');

    filterSelect.addEventListener('change', function () {
      const selected = this.value;
      productItems.forEach(item => {
        const cat = item.dataset.category;
        if (selected === 'all' || cat === selected) {
          item.style.display = '';
        } else {
          item.style.display = 'none';
        }
      });
    });

    // ---------- Inventory Category Filter ----------
    document.getElementById('inventoryCategoryFilter').addEventListener('change', function() {
      const val = this.value.toLowerCase();
      const rows = document.querySelectorAll('#inventoryTableBody tr');
      const empty = document.getElementById('emptyInventory');
      let visible = 0;
      rows.forEach(r => {
        if (val === 'all' || r.dataset.category.includes(val.replace(/[^a-z]/g,''))) {
          r.style.display = ''; visible++;
        } else r.style.display = 'none';
      });
      empty.style.display = visible === 0 ? 'block' : 'none';
    });
    // ---------- Order Logic ----------
    let orderItems = [];

    document.querySelectorAll('.product-card').forEach(card => {
      card.addEventListener('click', function (e) {
        e.preventDefault();
        const name = this.dataset.name;
        const price = parseFloat(this.dataset.price);
        const existing = orderItems.find(i => i.name === name);
        if (existing) existing.qty += 1;
        else orderItems.push({ name, price, qty: 1 });
        updateOrderSummary();
      });
    });

    function updateOrderSummary() {
      const tbody = document.getElementById('order-items-list');
      tbody.innerHTML = '';
      let total = 0;
      orderItems.forEach((item, idx) => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${item.name}</td>
          <td><input type="number" class="form-control form-control-sm" value="${item.qty}" min="1" style="width:60px;" onchange="updateQty(${idx},this.value)"></td>
          <td>₱${(item.price * item.qty).toFixed(2)}</td>
          <td><button class="btn btn-sm btn-danger" onclick="removeItem(${idx})">X</button></td>
        `;
        tbody.appendChild(row);
        total += item.price * item.qty;
      });
      document.getElementById('totalAmount').textContent = `₱${total.toFixed(2)}`;
    }

    function updateQty(idx, val) {
      const qty = parseInt(val) || 1;
      orderItems[idx].qty = qty;
      updateOrderSummary();
    }

    function removeItem(idx) {
      orderItems.splice(idx, 1);
      updateOrderSummary();
    }

    function resetOrderForm() {
      orderItems = [];
      document.getElementById('customerName').value = '';
      document.getElementById('orderType').selectedIndex = 0;
      updateOrderSummary();
    }

    function submitOrder() {
      const customer = document.getElementById('customerName').value.trim();
      const orderType = document.getElementById('orderType').value;
      if (!customer) return alert('Please enter customer name.');
      if (orderItems.length === 0) return alert('Please add at least one item.');

      const itemsList = orderItems.map(i => `${i.name} x${i.qty}`).join(', ');
      const total = orderItems.reduce((s, i) => s + i.price * i.qty, 0);

      alert(`Order Submitted!\nCustomer: ${customer}\nType: ${orderType}\nItems: ${itemsList}\nTotal: ₱${total.toFixed(2)}`);

      hideInlineOrder();
    }

    document.getElementById('inlineOrderCard').addEventListener('transitionend', function () {
      if (!this.classList.contains('show')) resetOrderForm();
    });

    // ---------- Reservation Search ----------
    document.getElementById('searchBtn').addEventListener('click', function() {
      const input = document.getElementById('searchInput').value.toLowerCase();
      const rows = document.querySelectorAll('#reservationTable tbody tr');
      rows.forEach(row => {
        const name = row.cells[1].textContent.toLowerCase();
        row.style.display = name.includes(input) ? '' : 'none';
      });
    });

    // View Details
    document.querySelectorAll('.view-btn').forEach(button => {
      button.addEventListener('click', function() {
        const row = this.closest('tr');
        const id = row.cells[0].textContent;
        const name = row.cells[1].textContent;
        const date = row.cells[2].textContent;
        const status = row.cells[3].textContent;
        alert(`Reservation Details:\n\nID: ${id}\nCustomer: ${name}\nDate & Time: ${date}\nStatus: ${status}`);
      });
    });

    // Confirm and Cancel
    document.querySelectorAll('.confirm-btn').forEach(button => {
      button.addEventListener('click', function() {
        const row = this.closest('tr');
        const statusCell = row.querySelector('.status');
        statusCell.textContent = 'Confirmed';
        statusCell.className = 'status text-success';
        alert('Reservation confirmed successfully!');
      });
    });

    document.querySelectorAll('.cancel-btn').forEach(button => {
      button.addEventListener('click', function() {
        const row = this.closest('tr');
        const statusCell = row.querySelector('.status');
        statusCell.textContent = 'Cancelled';
        statusCell.className = 'status text-danger';
        alert('Reservation cancelled successfully!');
      });
    });

    
  </script>
</body>
</html>