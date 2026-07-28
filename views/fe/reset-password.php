<?php

require_once __DIR__ . "/_app.php";
require_once __DIR__ . "/../../controllers/AuthController.php";

$authController = new AuthController(app_db());
$email = $_GET["email"] ?? ($_POST["email"] ?? "");
$message = "";
$messageType = "error";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $result = $authController->resetPassword(
        $_POST["email"] ?? "",
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
  <title>Reset Password - Al-Habib Pharmacy</title>
  <link rel="stylesheet" href="../../assets/css/pages/reset-password.css">
<?php echo shared_css_links(); ?>
</head>
<body>
  
<?php include __DIR__ . "/partials/customer-navbar.php"; ?>
<div class="box">
    <h2>Reset Password</h2>
    <?php if ($message !== ""): ?><div class="<?php echo $messageType === "success" ? "alert-success" : "alert-error"; ?>"><?php echo e($message); ?></div><?php endif; ?>
    <form method="POST" action="reset-password.php">
      <label>Email</label>
      <input type="email" name="email" value="<?php echo e($email); ?>" required>
      <label>New Password</label>
      <input type="password" name="password" required>
      <label>Confirm Password</label>
      <input type="password" name="confirm_password" required>
      <button type="submit">Reset Password</button>
    </form>
    <p><a href="login.php">Back to login</a></p>
  </div>
<?php include __DIR__ . "/partials/site-footer.php"; ?>
<?php echo shared_js_scripts(); ?>
</body>
</html>
