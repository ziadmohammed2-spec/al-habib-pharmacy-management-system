<?php

require_once __DIR__ . "/_app.php";
require_once __DIR__ . "/../../controllers/AuthController.php";

if (is_logged_in()) {
    redirect_to(is_admin_user() ? "admin-dashboard.php" : "home.php");
}

$authController = new AuthController(app_db());
$message = "";
$messageType = "error";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $result = $authController->register(
        $_POST["name"] ?? "",
        $_POST["email"] ?? "",
        $_POST["phone"] ?? "",
        $_POST["password"] ?? "",
        $_POST["confirm_password"] ?? ""
    );

    $message = $result["message"];
    $messageType = $result["success"] ? "success" : "error";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Al-Habib Pharmacy</title>
  <?php echo shared_css_links(); ?>
  <link rel="stylesheet" href="../../assets/css/auth.css?v=900">
</head>

<body class="auth-page">
<div class="page-wrapper">

  <?php include __DIR__ . "/partials/customer-navbar.php"; ?>

  <div class="blue-strip"></div>

  <main class="auth-main">
    <section class="auth-card register-card">
      <div class="auth-title">
        <h2>Register</h2>
        <span></span>
      </div>

      <?php if ($message !== ""): ?>
        <div class="<?php echo $messageType === "success" ? "alert-success" : "alert-error"; ?>">
          <?php echo e($message); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="register.php" novalidate>
        <div class="form-group">
          <label>Full Name</label>
          <div class="input-box">
            <span>U</span>
            <input type="text" name="name" value="<?php echo e($_POST["name"] ?? ""); ?>" placeholder="Enter your full name" required>
          </div>
        </div>

        <div class="form-group">
          <label>Email Address</label>
          <div class="input-box">
            <span>@</span>
            <input type="email" name="email" value="<?php echo e($_POST["email"] ?? ""); ?>" placeholder="Enter your email" required>
          </div>
        </div>

        <div class="form-group">
          <label>Phone Number</label>
          <div class="input-box">
            <span>Tel</span>
            <input type="text" name="phone" value="<?php echo e($_POST["phone"] ?? ""); ?>" placeholder="10 to 15 digits">
          </div>
        </div>

        <div class="form-group">
          <label>Password</label>
          <div class="input-box">
            <span>Lock</span>
            <input type="password" name="password" placeholder="Minimum 6 characters" required>
          </div>
        </div>

        <div class="form-group">
          <label>Confirm Password</label>
          <div class="input-box">
            <span>Lock</span>
            <input type="password" name="confirm_password" placeholder="Confirm your password" required>
          </div>
        </div>

        <button type="submit" class="auth-btn">Register</button>

        <div class="divider"></div>

        <p class="bottom-text">
          Already have an account? <a href="login.php">Login here.</a>
        </p>
      </form>
    </section>
  </main>

  <?php include __DIR__ . "/partials/site-footer.php"; ?>

</div>

<?php echo shared_js_scripts(); ?>
</body>
</html>