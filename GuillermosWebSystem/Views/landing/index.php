<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Guillermo's Café</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body, html {
      height: 100%;
      margin: 0;
      font-family: 'Poppins', sans-serif;
      scroll-behavior: smooth;
      background: url('bg/wallpaper.jpg') top center repeat-y;
      background-size: cover;
      background-attachment: fixed;
    }

    .bg-overlay {
      background-color: rgba(0, 0, 0, 0.4);
      width: 100%;
      min-height: 100vh;
    }

    /* HEADER NAVBAR */
    .navbar {
      background: rgba(0, 0, 0, 0.4);
      backdrop-filter: blur(10px);
      position: fixed;
      top: 0;
      width: 100%;
      z-index: 10;
      padding: 15px 40px;
    }
    .navbar-brand {
      color: #fff;
      font-weight: bold;
      font-size: 1.3rem;
      letter-spacing: 1px;
    }
    .navbar-brand:hover {
      color: #d2a679;
    }
    .nav-link {
      color: #fff !important;
      margin: 0 10px;
      font-weight: 500;
      transition: 0.3s;
    }
    .nav-link:hover {
      color: #d2a679 !important;
    }
    .btn-login-nav {
      border: 2px solid #fff;
      border-radius: 50px;
      padding: 6px 20px;
      color: #fff;
      font-weight: bold;
      transition: 0.3s;
      text-decoration: none;
    }
    .btn-login-nav:hover {
      background-color: #d2a679;
      color: #fff;
      border-color: #d2a679;
    }

    /* HERO */
    .center-content {
      position: relative;
      z-index: 2;
      height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      color: #fff;
      text-align: center;
      padding: 0 20px;
      transition: opacity 0.4s ease;
    }

    .fade-hidden { opacity: 0.2; transition: opacity 0.4s ease; }

    .guill-tm {
      display: flex;
      align-items: flex-start;
      justify-content: center;
      gap: 5px;
      flex-wrap: nowrap;
    }

    .guill-tm img {
      width: 70%;
      max-width: 800px;
      height: auto;
      margin-top: -15vh;
    }

    .guill-tm span {
      font-size: clamp(1rem, 2vw, 1.5rem);
      font-weight: bold;
      margin-top: 4vh;
    }

    .center-content h5 {
      font-size: clamp(1rem, 2.5vw, 1.5rem);
      margin-top: -200px;
      margin-left: 50vh;
      margin-bottom: 0px;
    }

    .center-content p {
      max-width: 500px;
      font-size: clamp(0.9rem, 2.2vw, 1rem);
      margin-top: 5vh;
      margin-bottom: 25px;
    }
    
    .social-icons {
      position: absolute;
      bottom: 20px;
      left: 20px;
      z-index: 2;
      transition: opacity 0.4s ease;
    }

    .social-icons a {
      color: #fff;
      margin-right: 15px;
      font-size: 1.5rem;
      text-decoration: none;
      transition: 0.3s;
    }

    .social-icons a:hover {
      color: #d2a679;
    }

    /* Modal Styles */
    .modal-content {
      border-radius: 25px;
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.4);
      color: #fff;
      box-shadow: 0 0 15px 5px rgba(255, 255, 255, 0.3), 0 4px 30px rgba(0,0,0,0.3);
      transition: all 0.3s ease;
    }

    .modal-content:hover {
      box-shadow: 0 0 20px 8px rgba(255, 255, 255, 0.4), 0 4px 30px rgba(0,0,0,0.3);
    }

    .modal-header { border-bottom: none; text-align: center; width: 100%; }
    .modal-title { font-weight: 700; color: #f5f5f5; width: 100%; }

    /* Top Deals */
    #topDeals { padding: 100px 0; min-height: 100vh; }
    #topDeals h2 { font-weight: 700; margin-bottom: 100px; text-align:center; color:#fff; }
    #topDeals .card { border: none; border-radius: 20px; overflow: hidden; text-align: center; background: #fff; }
    #topDeals .card img { height: 180px; object-fit: cover; border-radius: 50%; width: 180px; margin: 20px auto; }
    #topDeals .badge { background-color: #d2a679; color: #fff; font-size: 0.8rem; border-radius: 10px; }
    #topDeals .btn-order { background-color: #d2a679; border: none; border-radius: 10px; color: #fff; font-weight: 600; padding: 8px 20px; }
    #topDeals .btn-order:hover { background-color: #b58961; }

    /* Price & Rating */
    .card-body .price-rating {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: -5px;
      margin-bottom: 10px;
    }
    .card-body .price-rating .price {
      font-weight: 600;
      color: #000;
    }
    .card-body .price-rating .rating {
      font-weight: 600;
      color: #d2a679;
    }

    /* Featured Drinks */
    #featuredDrinks {
      padding: 100px 0;
      color: #fff;
      text-align: center;
      min-height: 100vh;
    }

    #featuredDrinks h2 {
      font-weight: 700;
      margin-bottom: 100px;
      text-align: center;
      color: #fff;
    }

    #featuredDrinks .card {
      border: none;
      border-radius: 20px;
      overflow: hidden;
      text-align: center;
      background: #fff;
      padding: 1rem;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    #featuredDrinks .card img {
      height: 180px;
      object-fit: cover;
      border-radius: 20px;
      width: 180px;
      margin: 20px auto;
    }

    #featuredDrinks .badge {
      background-color: #d2a679;
      color: #fff;
      font-size: 0.8rem;
      border-radius: 10px;
      position: absolute;
      top: 0;
      left: 0;
      margin: 0.5rem;
    }

    #featuredDrinks .btn-order {
      background-color: #d2a679;
      border: none;
      border-radius: 10px;
      color: #fff;
      font-weight: 600;
      padding: 8px 20px;
      transition: 0.3s;
    }

    #featuredDrinks .btn-order:hover {
      background-color: #b58961;
    }

    #featuredDrinks .price-rating {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: -5px;
      margin-bottom: 10px;
    }

    #featuredDrinks .price-rating .price {
      font-weight: 600;
      color: #000;
    }

    #featuredDrinks .price-rating .rating {
      font-weight: 600;
      color: #d2a679;
    }

    /* About */
    #about {
    padding: 100px 0;
    color: #fff;
    text-align: center;
    min-height: 100vh;

    /* Center content vertically and horizontally */
    display: flex;
    justify-content: center; /* horizontal center */
    align-items: center;     /* vertical center */
    flex-direction: column;  /* stack h2 and p vertically */
  }

    #about .container {
      max-width: 700px; /* optional: control text width */
    }


    .form-label { color: #fff; }
    .form-control {
      background: rgba(255, 255, 255, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: #fff;
      border-radius: 10px;
      transition: 0.3s;
    }
    .form-control::placeholder { color: rgba(255, 255, 255, 0.7); }
    .btn-login {
      background-color: #d2a679;
      border: none;
      color: white;
      font-weight: bold;
      border-radius: 30px;
      padding: 10px;
      transition: 0.3s;
    }
    .btn-login:hover { background-color: #b58961; transform: scale(1.05); }
    .forgot-link, .register-link { color:#fff; cursor:pointer; }
    .forgot-link:hover, .register-link:hover { color:#d2a679; text-decoration:underline; }
    .key-icon {
      font-size: 2rem;
      color: #d2a679;
      margin-bottom: 10px;
    }

    .form-message {
      display: none;
      margin-bottom: 16px;
      padding: 10px 14px;
      border-radius: 12px;
      font-size: 0.9rem;
      text-align: center;
      font-weight: 500;
    }

    .form-message[data-state] {
      display: block;
    }

    .form-message[data-state='info'] {
      background: rgba(33, 150, 243, 0.18);
      border: 1px solid rgba(33, 150, 243, 0.35);
      color: #dbeefe;
    }

    .form-message[data-state='success'] {
      background: rgba(76, 175, 80, 0.2);
      border: 1px solid rgba(76, 175, 80, 0.45);
      color: #e7ffe6;
    }

    .form-message[data-state='error'] {
      background: rgba(217, 83, 79, 0.2);
      border: 1px solid rgba(217, 83, 79, 0.45);
      color: #ffe3e0;
    }

    .btn-login.is-loading {
      opacity: 0.75;
      pointer-events: none;
    }
    
  </style>
</head>

<body>
  <div class="bg-overlay">
    <!-- HEADER -->
    <nav class="navbar navbar-expand-lg">
      <a class="navbar-brand" href="#">Guillermo's Café</a>
      <div class="ms-auto d-flex align-items-center">
        <a href="#topDeals" class="nav-link">Top Deals</a>
        <a href="#featuredDrinks" class="nav-link">Featured Drinks</a>
        <a href="#about" class="nav-link">About</a>
        <a href="#" class="btn-login-nav ms-3" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
      </div>
    </nav>

    <!-- HERO -->
    <div class="center-content" id="mainContent">
      <div class="guill-tm">
        <img src="bg/guill.png" alt="Guillermo's Logo" class="img-fluid">
        <span>TM</span>
      </div>

      <h5>SINCE 2020</h5>
      <p>Welcome to Guillermo's Café! Enjoy freshly baked, handcrafted treats made with love to brighten your day in cozy comfort.</p>
    </div>

    <!-- SOCIAL ICONS -->
    <div class="social-icons" id="socialIcons">
      <a href="https://www.facebook.com/share/1EpCnwYXfb/" target="_blank"><i class="bi bi-facebook"></i></a>
      <a href="tel:123456789"><i class="bi bi-telephone"></i></a>
    </div>

    <!-- TOP DEALS -->
    <section id="topDeals">
      <div class="container">
        <h2>TOP DEALS</h2>
        <div class="row justify-content-center g-4">
          <div class="col-md-3 col-sm-6">
            <div class="card p-3 shadow-sm position-relative">
              <span class="badge position-absolute top-0 start-0 m-2">Student Meal</span>
              <img src="Rice_meal/adobo.jpg" alt="Chicken Adobo Flakes">
              <div class="card-body">
                <h5 class="card-title">Chicken Adobo Flakes</h5>
                <p class="text-muted">Nice and crispy chicken adobo flakes</p>
                <div class="price-rating">
                  <span class="price">₱150.00 <small class="text-muted">Bowl</small></span>
                  <span class="rating">4.8/5 ⭐</span>
                </div>
                <button class="btn btn-order">Order Now</button>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="card p-3 shadow-sm position-relative">
              <span class="badge position-absolute top-0 start-0 m-2">Most Loved</span>
              <img src="Pastries/choco.jpg" alt="Chocolate Cake">
              <div class="card-body">
                <h5 class="card-title">Chocolate Cake</h5>
                <p class="text-muted">A decadent, dark chocolate cake with a rich, fudge frosting</p>
                <div class="price-rating">
                  <span class="price">₱550.00</span>
                  <span class="rating">4.8/5 ⭐</span>
                </div>
                <button class="btn btn-order">Order Now</button>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="card p-3 shadow-sm position-relative">
              <span class="badge position-absolute top-0 start-0 m-2">Premium Dish</span>
              <img src="Pasta/aglio.jpg" alt="Seafood Aglio Olio">
              <div class="card-body">
                <h5 class="card-title">Seafood Aglio Olio</h5>
                <p class="text-muted">Olive oil, garlic, parmesan cheese, shrimps, mussels</p>
                <div class="price-rating">
                  <span class="price">₱190.00</span>
                  <span class="rating">4.8/5 ⭐</span>
                </div>
                <button class="btn btn-order">Order Now</button>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="card p-3 shadow-sm position-relative">
              <span class="badge position-absolute top-0 start-0 m-2">#Best Seller</span>
              <img src="Pizza/pizza.jpg" alt="Beef & Mushroom Pizza">
              <div class="card-body">
                <h5 class="card-title">Beef & Mushroom Pizza</h5>
                <p class="text-muted">Ground beef rich in spices, mushrooms and cheese</p>
                <div class="price-rating">
                  <span class="price">₱260.00</span>
                  <span class="rating">4.8/5 ⭐</span>
                </div>
                <button class="btn btn-order">Order Now</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FEATURED DRINKS -->
    <section id="featuredDrinks">
      <div class="container">
        <h2>FEATURED DRINKS</h2>
        <div class="row justify-content-center g-4">
          <div class="col-md-3 col-sm-6">
            <div class="card p-3 shadow-sm position-relative">
              <span class="badge position-absolute top-0 start-0 m-2">Student Favorite</span>
              <img src="drinks/spanishlatte.jpg" alt="Spanish Latte">
              <div class="card-body">
                <h5 class="card-title">Spanish Latte</h5>
                <p class="text-muted">Rich espresso latte with a touch of cinnamon</p>
                <div class="price-rating">
                  <span class="price">₱140.00</span>
                  <span class="rating">4.7/5 ⭐</span>
                </div>
                <button class="btn btn-order">Order Now</button>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6">
            <div class="card p-3 shadow-sm position-relative">
              <span class="badge position-absolute top-0 start-0 m-2">Most Loved</span>
              <img src="drinks/Caramel Macchiato.jpg" alt="Caramel Macchiato">
              <div class="card-body">
                <h5 class="card-title">Caramel Macchiato</h5>
                <p class="text-muted">Smooth espresso with caramel drizzle and milk foam</p>
                <div class="price-rating">
                  <span class="price">₱150.00</span>
                  <span class="rating">4.8/5 ⭐</span>
                </div>
                <button class="btn btn-order">Order Now</button>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6">
            <div class="card p-3 shadow-sm position-relative">
              <span class="badge position-absolute top-0 start-0 m-2">Premium</span>
              <img src="drinks/Matcha Latte.jpg" alt="Matcha Latte">
              <div class="card-body">
                <h5 class="card-title">Matcha Latte</h5>
                <p class="text-muted">Creamy Japanese green tea latte with foam</p>
                <div class="price-rating">
                  <span class="price">₱150.00</span>
                  <span class="rating">4.9/5 ⭐</span>
                </div>
                <button class="btn btn-order">Order Now</button>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6">
            <div class="card p-3 shadow-sm position-relative">
              <span class="badge position-absolute top-0 start-0 m-2">#Best Seller</span>
              <img src="drinks/Lemon berry.jpg" alt="Lemon berry">
              <div class="card-body">
                <h5 class="card-title">Lemon Berry</h5>
                <p class="text-muted">WALA PA ITONG <br> DESCRIPTION</p>
                <div class="price-rating">
                  <span class="price">₱80.00</span>
                  <span class="rating">4.8/5 ⭐</span>
                </div>
                <button class="btn btn-order">Order Now</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>



    <!-- ABOUT -->
    <section id="about">
      <div class="container">
        <h2>ABOUT US</h2>
        <p>Guillermo's Café, established locally in 2020, is a coffee shop, bakery, <br>
                and restaurant dedicated to serving high-quality, homemade products <br>
                made with love and passion. We offer a wide variety of food and <br>
                beverages, including coffee, milk tea, breads, pastries, pasta, pizza, and <br>
                burgers. Committed to excellence and customer satisfaction, <br>
                Guillermo's Café continues to provide a warm and enjoyable dining <br>
                experience for everyone.</p>
      </div>
    </section>
  </div>

  <!-- LOGIN/REGISTER/FORGOT/VERIFICATION MODAL -->
  <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content p-4">
        <div class="modal-header border-0">
          <h5 class="modal-title w-100 text-center" id="loginModalLabel">LOGIN</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="loginForm">
            <div class="mb-3">
              <label class="form-label fw-semibold">Email address</label>
              <input type="email" class="form-control" placeholder="Enter your email">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Password</label>
              <input type="password" class="form-control" placeholder="Enter your password">
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="rememberMe">
                <label class="form-check-label" for="rememberMe" style="color:#fff;">Remember me</label>
              </div>
              <span class="forgot-link">Forgot password?</span>
            </div>
            <button type="submit" class="btn btn-login w-100">Log In</button>

            <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
              <span>or signed with</span>
              <a href="#" class="btn btn-primary d-flex align-items-center gap-2 px-3 py-1" 
                 style="background-color:#db4437; border:none; border-radius:30px; color:#fff;">
                <i class="bi bi-google text-white"></i>
                <span>Google</span>
              </a>

            </div>

            <div class="text-center mt-2">
              <p>Don’t have an account? <span class="register-link">Register</span></p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    const loginModal = document.getElementById('loginModal');
    const mainContent = document.getElementById('mainContent');
    const socialIcons = document.getElementById('socialIcons');

    loginModal.addEventListener('show.bs.modal', () => {
      mainContent.classList.add('fade-hidden');
      socialIcons.classList.add('fade-hidden');
    });

    loginModal.addEventListener('hidden.bs.modal', () => {
      mainContent.classList.remove('fade-hidden');
      socialIcons.classList.remove('fade-hidden');
    });

    const modalBody = loginModal.querySelector('.modal-body');
    const modalTitle = loginModal.querySelector('.modal-title');

    const titles = {
      login: 'LOGIN',
      register: 'REGISTER',
      forgot: 'FORGOT PASSWORD',
      reset: 'RESET PASSWORD',
      verify: 'VERIFY EMAIL'
    };

    function escapeHtml(value) {
      return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    const templates = {
      login: () => `
        <form id="loginForm" action="/GuillermosWebSystem/Controllers/AuthController.php?action=login" method="POST">
          <div class="mb-3">
            <label class="form-label fw-semibold">Email or Username</label>
            <input type="text" class="form-control" name="identity" required placeholder="Enter your email or username">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Password</label>
            <input type="password" class="form-control" name="password" required placeholder="Enter your password">
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="rememberMe">
              <label class="form-check-label" for="rememberMe" style="color:#fff;">Remember me</label>
            </div>
            <span class="forgot-link js-view-link" data-view="forgot">Forgot password?</span>
          </div>
          <div class="form-message" data-message></div>
          <button type="submit" class="btn btn-login w-100">Log In</button>

          <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
            <span>or sign in with</span>
            <a href="/GuillermosWebSystem/Controllers/GoogleAuthController.php" class="btn btn-primary d-flex align-items-center gap-2 px-3 py-1" 
               style="background-color:#db4437; border:none; border-radius:30px; color:#fff;">
              <i class="bi bi-google text-white"></i>
              <span>Google</span>
            </a>
          </div>

          <div class="text-center mt-2">
            <p>Don’t have an account? <span class="register-link js-view-link" data-view="register">Register</span></p>
          </div>
        </form>
      `,
      register: () => `
        <form id="registerForm" action="/GuillermosWebSystem/Controllers/AuthController.php?action=register" method="POST" novalidate>
          <div class="mb-3">
            <label class="form-label fw-semibold">Username</label>
            <input type="text" class="form-control" name="username" required placeholder="Enter your username">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" class="form-control" name="email" required placeholder="Enter your email">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Password</label>
            <input type="password" class="form-control" name="password" required placeholder="Enter your password">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Contact Number</label>
            <input type="tel" class="form-control" name="phonenumber" required placeholder="Enter your contact number" pattern="[0-9]{11}" maxlength="11">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Full Name</label>
            <input type="text" class="form-control" name="name" required placeholder="Enter your full name">
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="termsCheck" required>
            <label class="form-check-label" for="termsCheck">
              I agree to the <a href="terms.html" target="_blank" style="color:#d2a679;">Terms & Conditions</a>
            </label>
          </div>
          <div class="form-message" data-message></div>
          <button type="submit" class="btn btn-login w-100">Register</button>

          <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
            <span>or register with</span>
            <a href="/GuillermosWebSystem/Controllers/GoogleAuthController.php" class="btn btn-primary d-flex align-items-center gap-2 px-3 py-1" 
               style="background-color:#db4437; border:none; border-radius:30px; color:#fff;">
              <i class="bi bi-google text-white"></i>
              <span>Google</span>
            </a>
          </div>

          <div class="text-center mt-2">
            <p>Already have an account? <span class="register-link js-view-link" data-view="login">Login</span></p>
          </div>
        </form>
      `,
      forgot: () => `
        <form id="forgotForm" action="/GuillermosWebSystem/Controllers/AuthController.php?action=forgot-password" method="POST">
          <div class="mb-3">
            <label class="form-label fw-semibold">Email address</label>
            <input type="email" class="form-control" name="email" required placeholder="Enter your registered email">
          </div>
          <div class="form-message" data-message></div>
          <button type="submit" class="btn btn-login w-100">Send Verification Code</button>
          <div class="text-center mt-3">
            <p>Back to <span class="register-link js-view-link" data-view="login">Login</span></p>
          </div>
        </form>
      `,
      reset: ({ email = '' } = {}) => {
        const safeEmail = escapeHtml(email);
        const displayEmail = safeEmail || 'your email';
        return `
          <form id="resetForm" action="/GuillermosWebSystem/Controllers/AuthController.php?action=reset-password" method="POST" novalidate>
            <input type="hidden" name="email" value="${safeEmail}">
            <p class="small text-center mb-3">Enter the code sent to <strong>${displayEmail}</strong> and choose a new password.</p>
            <div class="form-message" data-message></div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Reset Code</label>
              <input type="text" class="form-control" name="reset_code" required placeholder="Enter 6-digit code" pattern="[0-9]{6}" maxlength="6">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">New Password</label>
              <input type="password" class="form-control" name="new_password" required placeholder="Enter new password" minlength="6">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Confirm New Password</label>
              <input type="password" class="form-control" name="confirm_password" required placeholder="Confirm new password" minlength="6">
            </div>
            <button type="submit" class="btn btn-login w-100">Update Password</button>
            <div class="text-center mt-3">
              <p><span class="register-link js-view-link" data-view="login">Back to Login</span></p>
            </div>
          </form>
        `;
      },
      verify: ({ email = '' } = {}) => {
        const safeEmail = escapeHtml(email);
        const displayEmail = safeEmail || 'your email';
        return `
          <form id="verifyForm" action="/GuillermosWebSystem/Controllers/AuthController.php?action=verify-email" method="POST" novalidate>
            <input type="hidden" name="email" value="${safeEmail}">
            <div class="text-center">
              <i class="bi bi-key key-icon"></i>
            </div>
            <p class="text-center small mb-3">Enter the 6-digit code sent to <strong>${displayEmail}</strong>.</p>
            <div class="form-message" data-message></div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Verification Code</label>
              <input type="text" class="form-control" name="verification_code" required placeholder="Enter 6-digit code" pattern="[0-9]{6}" maxlength="6">
            </div>
            <button type="submit" class="btn btn-login w-100">Verify Email</button>
            <div class="text-center mt-3">
              <p>Didn’t receive the code? <a href="#" data-role="resend">Resend</a></p>
            </div>
            <div class="text-center mt-2">
              <p><span class="register-link js-view-link" data-view="login">Back to Login</span></p>
            </div>
          </form>
        `;
      }
    };

    let currentView = 'login';
    let pendingEmail = '';
    let pendingResetEmail = '';

    function renderView(view, options = {}) {
      currentView = view;
      modalTitle.textContent = titles[view] || 'Modal';
      modalBody.innerHTML = templates[view](options);
      wireLinks();
      attachFormHandlers(view, options);
      const messageBox = modalBody.querySelector('[data-message]');
      if (messageBox) {
        if (options.message) {
          setMessage(messageBox, options.message, options.state || 'info');
        } else {
          setMessage(messageBox, '');
        }
      }
    }

    function wireLinks() {
      modalBody.querySelectorAll('.js-view-link').forEach(link => {
        link.addEventListener('click', event => {
          event.preventDefault();
          const targetView = link.dataset.view;
          if (!targetView) {
            return;
          }
          if (targetView === 'verify') {
            if (pendingEmail) {
              renderView('verify', { email: pendingEmail });
            }
            return;
          }
          renderView(targetView);
        });
      });
    }

    function attachFormHandlers(view, options = {}) {
      if (view === 'login') {
        const form = modalBody.querySelector('#loginForm');
        if (form) {
          form.addEventListener('submit', handleLoginSubmit);
        }
      }

      if (view === 'register') {
        const form = modalBody.querySelector('#registerForm');
        if (form) {
          form.addEventListener('submit', handleRegisterSubmit);
        }
      }

      if (view === 'verify') {
        const form = modalBody.querySelector('#verifyForm');
        if (form) {
          const emailInput = form.querySelector('input[name="email"]');
          if (emailInput && !emailInput.value && pendingEmail) {
            emailInput.value = pendingEmail;
          }
          form.addEventListener('submit', handleVerifySubmit);
          const resendLink = form.querySelector('[data-role="resend"]');
          if (resendLink) {
            resendLink.addEventListener('click', handleResendClick);
          }
        }
      }

      if (view === 'forgot') {
        const form = modalBody.querySelector('#forgotForm');
        if (form) {
          form.addEventListener('submit', handleForgotSubmit);
        }
      }

      if (view === 'reset') {
        const form = modalBody.querySelector('#resetForm');
        if (form) {
          form.addEventListener('submit', handleResetSubmit);
        }
      }
    }

    function setMessage(box, text, state = 'info') {
      if (!box) {
        return;
      }
      if (!text) {
        box.textContent = '';
        delete box.dataset.state;
        return;
      }
      box.textContent = text;
      box.dataset.state = state;
    }

    function setButtonLoading(button, loadingText) {
      if (!button) {
        return;
      }
      if (!button.dataset.initialText) {
        button.dataset.initialText = button.textContent.trim();
      }
      button.classList.add('is-loading');
      button.disabled = true;
      if (loadingText) {
        button.textContent = loadingText;
      }
    }

    function clearButtonLoading(button) {
      if (!button) {
        return;
      }
      button.classList.remove('is-loading');
      button.disabled = false;
      if (button.dataset.initialText) {
        button.textContent = button.dataset.initialText;
        delete button.dataset.initialText;
      }
    }

    async function submitWithJson(url, formData) {
      const response = await fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        credentials: 'same-origin'
      });
      let data = null;
      try {
        data = await response.json();
      } catch (error) {
        data = null;
      }
      return { response, data };
    }

    async function handleRegisterSubmit(event) {
      event.preventDefault();
      const form = event.currentTarget;
      const messageBox = form.querySelector('[data-message]');
      const submitButton = form.querySelector('button[type="submit"]');

      setMessage(messageBox, 'Sending verification code...', 'info');
      setButtonLoading(submitButton, 'Sending...');

      const formData = new FormData(form);

      try {
        const { response, data } = await submitWithJson(form.action, formData);
        if (response.ok && data && data.status === 'success') {
          pendingEmail = data.email || formData.get('email') || '';
          renderView('verify', {
            email: pendingEmail,
            message: data.message || 'Verification code sent. Please check your email.',
            state: 'info'
          });
        } else {
          const errorMessage = data?.message || 'Registration failed. Please try again.';
          setMessage(messageBox, errorMessage, 'error');
        }
      } catch (error) {
        console.error('Registration error:', error);
        setMessage(messageBox, 'Unable to send verification email right now. Please try again.', 'error');
      } finally {
        clearButtonLoading(submitButton);
      }
    }

    async function handleLoginSubmit(event) {
      event.preventDefault();
      const form = event.currentTarget;
      const messageBox = form.querySelector('[data-message]');
      const submitButton = form.querySelector('button[type="submit"]');

      setMessage(messageBox, 'Signing you in...', 'info');
      setButtonLoading(submitButton, 'Signing in...');

      const formData = new FormData(form);

      try {
        const { response, data } = await submitWithJson(form.action, formData);
        if (response.ok && data && data.status === 'success' && data.redirect) {
          setMessage(messageBox, 'Login successful. Redirecting...', 'success');
          window.location.href = data.redirect;
          return;
        }

        const errorMessage = data?.message || 'Login failed. Please check your credentials and try again.';
        setMessage(messageBox, errorMessage, 'error');
      } catch (error) {
        console.error('Login error:', error);
        setMessage(messageBox, 'Unable to sign in right now. Please try again.', 'error');
      } finally {
        clearButtonLoading(submitButton);
      }
    }

    async function handleForgotSubmit(event) {
      event.preventDefault();
      const form = event.currentTarget;
      const messageBox = form.querySelector('[data-message]');
      const submitButton = form.querySelector('button[type="submit"]');

      setMessage(messageBox, 'Sending reset code...', 'info');
      setButtonLoading(submitButton, 'Sending...');

      const formData = new FormData(form);

      try {
        const { response, data } = await submitWithJson(form.action, formData);
        if (response.ok && data && data.status === 'success') {
          pendingResetEmail = data.email || formData.get('email') || '';
          setMessage(messageBox, 'Reset code sent! Please check your email.', 'success');
          setTimeout(() => {
            renderView('reset', {
              email: pendingResetEmail,
              message: 'Enter the code we sent to complete your password reset.',
              state: 'info'
            });
          }, 800);
        } else {
          const errorMessage = data?.message || 'Unable to send reset code. Please try again.';
          setMessage(messageBox, errorMessage, 'error');
        }
      } catch (error) {
        console.error('Forgot password error:', error);
        setMessage(messageBox, 'Unable to send reset code right now. Please try again.', 'error');
      } finally {
        clearButtonLoading(submitButton);
      }
    }

    async function handleResetSubmit(event) {
      event.preventDefault();
      const form = event.currentTarget;
      const messageBox = form.querySelector('[data-message]');
      const submitButton = form.querySelector('button[type="submit"]');

      const emailField = form.querySelector('input[name="email"]');
      if (emailField && !emailField.value && pendingResetEmail) {
        emailField.value = pendingResetEmail;
      }

      const newPassword = form.querySelector('input[name="new_password"]').value;
      const confirmPassword = form.querySelector('input[name="confirm_password"]').value;

      if (newPassword !== confirmPassword) {
        setMessage(messageBox, 'Passwords do not match. Please try again.', 'error');
        return;
      }

      setMessage(messageBox, 'Updating password...', 'info');
      setButtonLoading(submitButton, 'Updating...');

      const formData = new FormData(form);

      try {
        const { response, data } = await submitWithJson(form.action, formData);
        if (response.ok && data && data.status === 'success') {
          setMessage(messageBox, 'Password updated! Redirecting to login...', 'success');
          pendingResetEmail = '';
          setTimeout(() => {
            renderView('login', {
              message: 'Password updated successfully. Please sign in with your new password.',
              state: 'success'
            });
          }, 1000);
        } else {
          const errorMessage = data?.message || 'Unable to update password. Please try again.';
          setMessage(messageBox, errorMessage, 'error');
        }
      } catch (error) {
        console.error('Reset password error:', error);
        setMessage(messageBox, 'Unable to update password right now. Please try again.', 'error');
      } finally {
        clearButtonLoading(submitButton);
      }
    }

    async function handleVerifySubmit(event) {
      event.preventDefault();
      const form = event.currentTarget;
      const messageBox = form.querySelector('[data-message]');
      const submitButton = form.querySelector('button[type="submit"]');

      setMessage(messageBox, 'Verifying code...', 'info');
      setButtonLoading(submitButton, 'Verifying...');

      const formData = new FormData(form);
      if (!formData.get('email') && pendingEmail) {
        formData.set('email', pendingEmail);
      }

      try {
        const { response, data } = await submitWithJson(form.action, formData);
        if (response.ok && data && data.status === 'success') {
          setMessage(messageBox, 'Verification successful! Redirecting to login...', 'success');
          setTimeout(() => {
            pendingEmail = '';
            renderView('login', {
              message: 'Account verified successfully! Please log in.',
              state: 'success'
            });
          }, 1200);
        } else {
          const errorMessage = data?.message || 'Invalid verification code. Please try again.';
          setMessage(messageBox, errorMessage, 'error');
        }
      } catch (error) {
        console.error('Verification error:', error);
        setMessage(messageBox, 'We could not verify the code. Please try again.', 'error');
      } finally {
        clearButtonLoading(submitButton);
      }
    }

    async function handleResendClick(event) {
      event.preventDefault();
      const link = event.currentTarget;
      if (link.dataset.loading === '1') {
        return;
      }

      const form = modalBody.querySelector('#verifyForm');
      if (!form) {
        return;
      }

      const messageBox = form.querySelector('[data-message]');

      const emailInput = form.querySelector('input[name="email"]');
      const emailToUse = (emailInput?.value || pendingEmail || '').trim();
      if (!emailToUse) {
        setMessage(messageBox, 'No email address available. Please register again.', 'error');
        return;
      }

      setMessage(messageBox, 'Sending a new verification code...', 'info');

      const originalText = link.textContent;
      link.dataset.loading = '1';
      link.textContent = 'Sending...';

      const formData = new FormData();
      formData.append('email', emailToUse);

      try {
        const { response, data } = await submitWithJson('/GuillermosWebSystem/Controllers/AuthController.php?action=resend-code', formData);
        if (response.ok && data && data.status === 'success') {
          pendingEmail = emailToUse;
          setMessage(messageBox, data.message || 'A new verification code has been sent.', 'success');
        } else {
          const errorMessage = data?.message || 'Unable to resend the verification code. Please try again later.';
          setMessage(messageBox, errorMessage, 'error');
        }
      } catch (error) {
        console.error('Resend code error:', error);
        setMessage(messageBox, 'Unable to resend the verification code right now. Please try again.', 'error');
      } finally {
        link.textContent = originalText;
        delete link.dataset.loading;
      }
    }

    renderView('login');
  </script>
</body>
</html>

