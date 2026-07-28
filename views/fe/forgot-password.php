<?php
require_once __DIR__ . "/_app.php";
require_once __DIR__ . "/../../controllers/AuthController.php";

$authController = new AuthController(app_db());
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"] ?? "";
    $result = $authController->forgotPassword($email);

    if ($result["success"]) {
        redirect_to("reset-password.php?email=" . urlencode($email));
    }

    $message = $result["message"];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password - Al-Habib Pharmacy</title>
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
        <h2>Forgot Password</h2>
        <span></span>
      </div>

      <?php if ($message !== ""): ?>
        <div class="alert-error"><?php echo e($message); ?></div>
      <?php endif; ?>

      <form method="POST" action="forgot-password.php" novalidate>
        <div class="form-group">
          <label>Email Address</label>
          <div class="input-box">
            <span>@</span>
            <input type="email" name="email" required placeholder="Enter your email">
          </div>
        </div>

        <button type="submit" class="auth-btn">Continue</button>

        <div class="divider"></div>

        <p class="bottom-text">
          <a href="login.php">Back to login</a>
        </p>
      </form>
    </section>
  </main>

  <?php include __DIR__ . "/partials/site-footer.php"; ?>
</div>
<?php echo shared_js_scripts(); ?>
</body>
</html>