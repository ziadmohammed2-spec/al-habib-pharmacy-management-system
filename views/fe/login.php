<?php

require_once __DIR__ . "/_app.php";
require_once __DIR__ . "/../../controllers/AuthController.php";

if (is_logged_in()) {
    redirect_to(is_admin_user() ? "admin-dashboard.php" : "home.php");
}

$authController = new AuthController(app_db());
$message = "";
$messageType = "error";

if (($_GET["logout"] ?? "") === "success") {
    $message = "You logged out successfully.";
    $messageType = "success";
}

if (($_GET["error"] ?? "") === "access_denied") {
    $message = "Access denied. Please login with an admin account.";
}

if (($_GET["error"] ?? "") === "login_required") {
    $message = "Please login first to continue.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $result = $authController->login($_POST["email"] ?? "", $_POST["password"] ?? "");

    if ($result["success"]) {
        redirect_to($result["role"] === "admin" ? "admin-dashboard.php" : "home.php");
    }

    $message = $result["message"];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Al-Habib Pharmacy</title>
  <?php echo shared_css_links(); ?>
  <link rel="stylesheet" href="../../assets/css/auth.css?v=900">
</head>

<body class="auth-page">
<div class="page-wrapper">

  <?php include __DIR__ . "/partials/customer-navbar.php"; ?>

  <div class="blue-strip"></div>

  <main class="auth-main">
    <section class="auth-card">
      <div class="auth-title">
        <h2>Login</h2>
        <span></span>
      </div>

      <?php if ($message !== ""): ?>
        <div class="<?php echo $messageType === "success" ? "alert-success" : "alert-error"; ?>">
          <?php echo e($message); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="login.php" novalidate>
        <div class="form-group">
          <label>Email Address</label>
          <div class="input-box">
            <span>@</span>
            <input type="email" name="email" placeholder="Enter your email" required>
          </div>
        </div>

        <div class="form-group">
          <label>Password</label>
          <div class="input-box">
            <span>Lock</span>
            <input type="password" name="password" placeholder="Enter your password" required>
          </div>
        </div>

        <button type="submit" class="auth-btn">Login</button>

        <div class="divider"></div>

        <p class="bottom-text">
          Don't have an account? <a href="register.php">Register here.</a>
        </p>

        <p class="bottom-text">
          <a href="forgot-password.php">Forgot password?</a>
        </p>
      </form>
    </section>
  </main>

  <?php include __DIR__ . "/partials/site-footer.php"; ?>

</div>

<?php echo shared_js_scripts(); ?>
</body>
</html>