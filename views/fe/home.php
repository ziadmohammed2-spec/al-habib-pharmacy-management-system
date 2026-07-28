<?php
require_once __DIR__ . "/_app.php";
require_once __DIR__ . "/../../controllers/CartController.php";

$isLoggedIn = is_logged_in();
$userRole = $_SESSION["role"] ?? "";
$userName = $_SESSION["name"] ?? "User";
$message = "";
$messageType = "success";

if (isset($_GET["added"])) {
    $message = "Product added to cart successfully. You can open the cart from the cart icon above.";
    $messageType = "success";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_to_cart"])) {
    if (!is_customer_user()) {
        redirect_to("login.php?error=login_required");
    }

    $cartController = new CartController(app_db());
    $added = $cartController->addToCart(current_customer_id(), $_POST["product_id"] ?? 0, $_POST["quantity"] ?? 1);

    if ($added) {
        redirect_to("home.php?added=1#featured-products");
    }

    $message = "Unable to add this product. Please check stock.";
    $messageType = "error";
}

$pdo = app_db();
$featuredStmt = $pdo->query("SELECT product_id, name, price, stock, image_url FROM products ORDER BY product_id DESC LIMIT 6");
$featuredProducts = $featuredStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Al-Habib Pharmacy</title>

  <?php echo shared_css_links(); ?>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../assets/css/pages/home.css?v=1001">
</head>

<body class="home-page">
<div class="home-shell">

  <?php include __DIR__ . "/partials/customer-navbar.php"; ?>

  <main class="home-main">

    <section class="hero">
      <div class="hero-text">
        <h1>Welcome to <br><span>Al-Habib Pharmacy</span></h1>

        <?php if ($isLoggedIn): ?>
          <p>Hello, <?php echo e($userName); ?>.<br>Your trusted partner in health and wellness.</p>
        <?php else: ?>
          <p>Your trusted partner in health and wellness.<br>Quality medicines, delivered to your door.</p>
        <?php endif; ?>
      </div>

      <img class="hero-photo" src="../../assets/images/banners/hero-pharmacist.jpg" alt="Al-Habib pharmacy team">

      <form class="search-box" method="GET" action="products.php">
        <input type="text" name="search" placeholder="Search for medicines, health products...">
        <button type="submit">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
      </form>
    </section>

    <?php if ($message !== ""): ?>
      <div class="<?php echo $messageType === "success" ? "alert-success" : "alert-error"; ?>">
        <?php echo e($message); ?>
      </div>
    <?php endif; ?>

    <section class="features">
      <div class="feature">
        <div class="feature-icon">
          <i class="fa-solid fa-truck-fast"></i>
        </div>
        <div>
          <h4>Fast & Reliable Delivery</h4>
          <p>On-time delivery<br>at your doorstep</p>
        </div>
      </div>

      <div class="feature">
        <div class="feature-icon">
          <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div>
          <h4>100% Genuine Medicines</h4>
          <p>Authentic products<br>you can trust</p>
        </div>
      </div>

      <div class="feature">
        <div class="feature-icon">
          <i class="fa-solid fa-file-prescription"></i>
        </div>
        <div>
          <h4>Upload Prescription</h4>
          <p>Easy upload and<br>quick verification</p>
        </div>
      </div>

      <div class="feature">
        <div class="feature-icon">
          <i class="fa-solid fa-headset"></i>
        </div>
        <div>
          <h4>24/7 Customer Support</h4>
          <p>We're here to help<br>you anytime</p>
        </div>
      </div>
    </section>

    <section class="pharmacy-banner pharmacy-care-panel">
  <div class="pharmacy-banner-text">
    <span class="care-kicker">Al-Habib Pharmacy</span>
    <h2>Local Pharmacy Care</h2>
    <p>Browse genuine medicines, upload prescriptions and get support from the Al-Habib team.</p>

    <div class="care-points">
      <div>
        <i class="fa-solid fa-capsules"></i>
        <span>Genuine medicines</span>
      </div>

      <div>
        <i class="fa-solid fa-file-prescription"></i>
        <span>Prescription support</span>
      </div>

      <div>
        <i class="fa-solid fa-truck-fast"></i>
        <span>Reliable delivery</span>
      </div>
    </div>
  </div>
</section>

    <div class="section-title">
      <h2>Browse by Category</h2>
      <a href="products.php">View All &gt;</a>
    </div>

    <section class="categories">
      <a href="products.php?search=pain" class="category-card">
        <img src="../../assets/images/categories/pain-relief.jpg" alt="Pain relief medicines">
        <h3>Pain Relief</h3>
      </a>

      <a href="products.php?search=vitamin" class="category-card">
        <img src="../../assets/images/categories/vitamins.jpg" alt="Vitamin supplements">
        <h3>Vitamins &<br>Supplements</h3>
      </a>

      <a href="products.php?search=antibiotic" class="category-card">
        <img src="../../assets/images/categories/antibiotics.jpg" alt="Antibiotic medicines">
        <h3>Antibiotics</h3>
      </a>

      <a href="products.php?search=gastric" class="category-card">
        <img src="../../assets/images/categories/digestive-care.jpg" alt="Digestive care medicine">
        <h3>Digestive Care</h3>
      </a>

      <a href="products.php?search=care" class="category-card">
        <img src="../../assets/images/categories/personal-care.jpg" alt="Personal care products">
        <h3>Personal Care</h3>
      </a>

      <a href="products.php" class="category-card">
        <img src="../../assets/images/categories/first-aid.jpg" alt="First aid kit">
        <h3>First Aid</h3>
      </a>
    </section>

    <div class="section-title">
      <h2>Featured Products</h2>
      <a href="products.php">View All &gt;</a>
    </div>

    <section class="products" id="featured-products">
      <?php if (empty($featuredProducts)): ?>
        <p class="empty-products">
          No products in database yet. <a href="admin-products.php">Add some</a>.
        </p>
      <?php else: ?>
        <?php foreach ($featuredProducts as $fp): ?>
          <div class="product-card">
            <?php $imageSrc = product_image_src($fp["image_url"] ?? "", ($fp["product_id"] ?? "") . " " . ($fp["name"] ?? "")); ?>

            <img class="product-photo" src="<?php echo e($imageSrc); ?>" alt="<?php echo e($fp["name"]); ?>">

            <h3><?php echo e($fp["name"]); ?></h3>
            <p>Pharmacy Product</p>
            <h4>EGP <?php echo number_format((float)$fp["price"], 2); ?></h4>

            <?php if ((int)$fp["stock"] > 0): ?>
              <form method="POST" action="home.php#featured-products" class="product-form">
                <input type="hidden" name="product_id" value="<?php echo (int)$fp["product_id"]; ?>">
                <input type="hidden" name="quantity" value="1">

                <button type="submit" name="add_to_cart">Add to Cart</button>
              </form>

              <a href="product-details.php?id=<?php echo (int)$fp["product_id"]; ?>" class="details-link">
                View Details
              </a>
            <?php else: ?>
              <button disabled class="out-stock-btn">Out of Stock</button>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>

  </main>

  <?php include __DIR__ . "/partials/site-footer.php"; ?>

</div>

<?php echo shared_js_scripts("cart"); ?>
</body>
</html>