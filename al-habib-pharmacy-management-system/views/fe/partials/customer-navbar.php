<?php
$currentPage = basename($_SERVER["PHP_SELF"]);
$cartCount = current_cart_count();
?>
<header class="site-navbar">
  <a href="home.php" class="site-logo" aria-label="Al-Habib Pharmacy Home">
    <span class="site-logo-mark">Al-Habib<br>Pharmacy</span>
    <span class="site-logo-text">
      <strong>Al-Habib</strong>
      <small>Pharmacy</small>
    </span>
  </a>

  <nav class="site-nav" aria-label="Main navigation">
    <a href="home.php" class="<?php echo $currentPage === 'home.php' ? 'active' : ''; ?>">Home</a>
    <a href="products.php" class="<?php echo $currentPage === 'products.php' || $currentPage === 'product-details.php' ? 'active' : ''; ?>">Products</a>

    <?php if (is_customer_user()): ?>
      <a href="cart.php" class="site-cart-link <?php echo $currentPage === 'cart.php' ? 'active' : ''; ?>">
        Cart
        <?php if ($cartCount > 0): ?><span class="site-cart-badge"><?php echo (int) $cartCount; ?></span><?php endif; ?>
      </a>
      <a href="upload.php" class="<?php echo $currentPage === 'upload.php' ? 'active' : ''; ?>">Upload Prescription</a>
      <a href="orders.php" class="<?php echo $currentPage === 'orders.php' || $currentPage === 'order-tracking.php' ? 'active' : ''; ?>">My Orders</a>
    <?php endif; ?>

    <a href="contact.php" class="<?php echo $currentPage === 'contact.php' ? 'active' : ''; ?>">Contact Us</a>

    <?php if (is_admin_user()): ?>
      <a href="admin-dashboard.php">Admin Panel</a>
    <?php endif; ?>

    <?php if (is_logged_in()): ?>
      <a href="account.php" class="<?php echo $currentPage === 'account.php' ? 'active' : ''; ?>">Account</a>
      <a href="logout.php" class="site-logout-link">Logout</a>
    <?php else: ?>
      <a href="login.php" class="<?php echo $currentPage === 'login.php' ? 'active' : ''; ?>">Login</a>
      <a href="register.php" class="<?php echo $currentPage === 'register.php' ? 'active' : ''; ?>">Register</a>
    <?php endif; ?>
  </nav>
</header>
