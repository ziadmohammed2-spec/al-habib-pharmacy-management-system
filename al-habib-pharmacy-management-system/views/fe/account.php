<?php
require_once __DIR__ . "/_app.php";
require_customer();

$db = app_db();
$userId = current_user_id();
$customerId = current_customer_id();
$message = "";
$messageType = "success";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    try {
        if ($action === "update_profile") {
            $name = trim($_POST["name"] ?? "");
            $email = trim($_POST["email"] ?? "");
            $phone = trim($_POST["phone"] ?? "");

            if ($name === "" || $email === "") {
                throw new Exception("Name and email are required.");
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Please enter a valid email address.");
            }

            $check = $db->prepare("SELECT user_id FROM users WHERE email = :email AND user_id <> :user_id LIMIT 1");
            $check->execute([":email" => $email, ":user_id" => $userId]);

            if ($check->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception("This email is already used by another account.");
            }

            $db->beginTransaction();

            $stmt = $db->prepare("UPDATE users SET name = :name, email = :email WHERE user_id = :user_id");
            $stmt->execute([":name" => $name, ":email" => $email, ":user_id" => $userId]);

            $stmt = $db->prepare("UPDATE customers SET phone = :phone WHERE customer_id = :customer_id");
            $stmt->execute([":phone" => $phone, ":customer_id" => $customerId]);

            $db->commit();

            $_SESSION["name"] = $name;
            $_SESSION["email"] = $email;

            redirect_to("account.php?success=profile");
        }

        if ($action === "add_address") {
            $city = trim($_POST["city"] ?? "");
            $street = trim($_POST["street"] ?? "");
            $buildingNo = trim($_POST["building_no"] ?? "");
            $details = trim($_POST["details"] ?? "");

            if ($city === "" || $street === "") {
                throw new Exception("City and street are required.");
            }

            $stmt = $db->prepare("INSERT INTO addresses (user_id, city, street, building_no, details) VALUES (:user_id, :city, :street, :building_no, :details)");
            $stmt->execute([
                ":user_id" => $userId,
                ":city" => $city,
                ":street" => $street,
                ":building_no" => $buildingNo,
                ":details" => $details
            ]);

            redirect_to("account.php?success=address");
        }
    } catch (Exception $e) {
        if ($db instanceof PDO && $db->inTransaction()) {
            $db->rollBack();
        }

        $message = $e->getMessage();
        $messageType = "error";
    }
}

if (($_GET["success"] ?? "") === "profile") {
    $message = "Profile updated successfully.";
    $messageType = "success";
}

if (($_GET["success"] ?? "") === "address") {
    $message = "Address added successfully.";
    $messageType = "success";
}

$stmt = $db->prepare("SELECT u.name, u.email, c.phone, c.loyalty_points FROM users u JOIN customers c ON u.user_id = c.user_id WHERE c.customer_id = :customer_id LIMIT 1");
$stmt->execute([":customer_id" => $customerId]);
$account = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$stmt = $db->prepare("SELECT address_id, city, street, building_no, details FROM addresses WHERE user_id = :user_id ORDER BY address_id DESC LIMIT 3");
$stmt->execute([":user_id" => $userId]);
$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT COUNT(*) AS total_orders FROM orders WHERE customer_id = :customer_id");
$stmt->execute([":customer_id" => $customerId]);
$totalOrders = (int)($stmt->fetch(PDO::FETCH_ASSOC)["total_orders"] ?? 0);

$stmt = $db->prepare("SELECT COUNT(*) AS pending_prescriptions FROM prescriptions WHERE customer_id = :customer_id AND status = 'Pending'");
$stmt->execute([":customer_id" => $customerId]);
$pendingPrescriptions = (int)($stmt->fetch(PDO::FETCH_ASSOC)["pending_prescriptions"] ?? 0);

$stmt = $db->prepare("SELECT o.order_id, o.order_date, o.status, o.total, p.name AS product_name, p.image_url FROM orders o LEFT JOIN order_items oi ON oi.order_item_id = (SELECT MIN(order_item_id) FROM order_items WHERE order_id = o.order_id) LEFT JOIN products p ON p.product_id = oi.product_id WHERE o.customer_id = :customer_id ORDER BY o.order_id DESC LIMIT 3");
$stmt->execute([":customer_id" => $customerId]);
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$name = $account["name"] ?? "Customer";
$email = $account["email"] ?? "";
$phone = $account["phone"] ?? "";
$loyaltyPoints = (int)($account["loyalty_points"] ?? 0);

$initials = strtoupper(substr(trim($name), 0, 1));
if ($initials === "") {
    $initials = "U";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Account - Al-Habib Pharmacy</title>
  <?php echo shared_css_links(); ?>
  <link rel="stylesheet" href="../../assets/css/pages/account.css">
</head>
<body>
<div class="page-wrapper account-page">
  <?php include __DIR__ . "/partials/customer-navbar.php"; ?>

  <main class="account-main">
    <section class="account-hero">
      <div>
        <span class="account-kicker">Customer Profile</span>
        <h1>My Account</h1>
        <p>Manage your profile, addresses, orders, prescriptions, and pharmacy services in one place.</p>
      </div>

      <div class="hero-doctor-card">
        <div class="hero-doctor-circle">+</div>
      </div>
    </section>

    <?php if ($message !== ""): ?>
      <div class="account-alert <?php echo $messageType === "success" ? "success" : "error"; ?>">
        <?php echo e($message); ?>
      </div>
    <?php endif; ?>

    <section class="account-layout">
      <aside class="account-sidebar-card">
        <div class="avatar-circle"><?php echo e($initials); ?></div>
        <h2><?php echo e($name); ?></h2>
        <p><?php echo e($email); ?></p>

        <div class="member-box">
          <span>Member since</span>
          <strong>2024</strong>
        </div>

        <nav class="account-menu">
          <a href="#profile" class="active">Account Overview</a>
          <a href="#profile">Personal Information</a>
          <a href="#addresses">Addresses</a>
          <a href="orders.php">My Orders</a>
          <a href="upload.php">Payment Methods</a>
          <a href="logout.php" class="danger">Logout</a>
        </nav>
      </aside>

      <section class="account-center">
        <div class="account-card" id="profile">
          <div class="card-title-row">
            <div>
              <h2>Personal Information</h2>
              <p>Keep your contact information up to date.</p>
            </div>
            <span class="small-badge">Edit</span>
          </div>

          <form method="POST" action="account.php" class="profile-form">
            <input type="hidden" name="action" value="update_profile">

            <div class="form-grid two-cols">
              <label>
                Full Name
                <input type="text" name="name" value="<?php echo e($name); ?>" required>
              </label>

              <label>
                Email Address
                <input type="email" name="email" value="<?php echo e($email); ?>" required>
              </label>

              <label>
                Phone Number
                <input type="text" name="phone" value="<?php echo e($phone); ?>" placeholder="Add your phone number">
              </label>

              <label>
                Gender
                <select name="gender" disabled>
                  <option>Not specified</option>
                </select>
              </label>
            </div>

            <button type="submit" class="account-btn">Save Changes</button>
          </form>
        </div>

        <div class="account-card" id="addresses">
          <div class="card-title-row">
            <div>
              <h2>My Addresses</h2>
              <p>Saved delivery addresses for checkout.</p>
            </div>
          </div>

          <div class="address-list">
            <?php if (empty($addresses)): ?>
              <div class="empty-box">No saved addresses yet. Add your first delivery address below.</div>
            <?php else: ?>
              <?php foreach ($addresses as $address): ?>
                <div class="address-item">
                  <div class="address-icon">⌂</div>
                  <div>
                    <strong><?php echo e($address["city"]); ?></strong>
                    <p>
                      <?php echo e($address["street"]); ?>
                      <?php echo $address["building_no"] ? ", Building " . e($address["building_no"]) : ""; ?>
                    </p>
                    <?php if (!empty($address["details"])): ?>
                      <span><?php echo e($address["details"]); ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <form method="POST" action="account.php" class="address-form">
            <input type="hidden" name="action" value="add_address">

            <div class="form-grid two-cols">
              <label>
                City
                <input type="text" name="city" placeholder="City" required>
              </label>

              <label>
                Street
                <input type="text" name="street" placeholder="Street" required>
              </label>

              <label>
                Building No.
                <input type="text" name="building_no" placeholder="Building number">
              </label>

              <label>
                Details
                <input type="text" name="details" placeholder="Apartment, floor, landmark">
              </label>
            </div>

            <button type="submit" class="account-btn light">Add New Address</button>
          </form>
        </div>

        <div class="account-card security-card">
          <div class="security-icon">🛡</div>

          <div>
            <h2>Account Security</h2>
            <p>Keep your account protected by changing your password regularly.</p>
          </div>

          <a href="forgot-password.php" class="outline-btn">Change Password</a>
        </div>
      </section>

      <aside class="account-right">
        <div class="account-card quick-card">
          <h2>Quick Actions</h2>

          <a href="upload.php" class="quick-action">
            <span>▣</span>
            <div>
              <strong>Upload Prescription</strong>
              <small>Submit your medical file</small>
            </div>
          </a>

          <a href="orders.php" class="quick-action">
            <span>▤</span>
            <div>
              <strong>My Orders</strong>
              <small>View order history</small>
            </div>
          </a>

          <a href="cart.php" class="quick-action">
            <span>♡</span>
            <div>
              <strong>View Cart</strong>
              <small>Check selected items</small>
            </div>
          </a>

          <a href="contact.php" class="quick-action">
            <span>♥</span>
            <div>
              <strong>Need Help?</strong>
              <small>Contact pharmacy support</small>
            </div>
          </a>
        </div>

        <div class="account-card stats-card">
          <h2>Account Summary</h2>
          <div class="stat-row"><span>Total Orders</span><strong><?php echo $totalOrders; ?></strong></div>
          <div class="stat-row"><span>Pending Prescriptions</span><strong><?php echo $pendingPrescriptions; ?></strong></div>
          <div class="stat-row"><span>Loyalty Points</span><strong><?php echo $loyaltyPoints; ?></strong></div>
        </div>

        <div class="account-card recent-card">
          <div class="card-title-row compact">
            <h2>Recent Orders</h2>
            <a href="orders.php">View all</a>
          </div>

          <?php if (empty($recentOrders)): ?>
            <div class="empty-box small">No recent orders yet.</div>
          <?php else: ?>
            <?php foreach ($recentOrders as $order): ?>
              <a href="order-tracking.php?order_id=<?php echo (int)$order["order_id"]; ?>" class="recent-order">
                <?php $image = product_image_src($order["image_url"] ?? "", $order["product_name"] ?? "order"); ?>

                <?php if ($image !== ""): ?>
                  <img src="<?php echo e($image); ?>" alt="Order product">
                <?php endif; ?>

                <div>
                  <strong>Order #<?php echo (int)$order["order_id"]; ?></strong>
                  <span><?php echo e($order["status"]); ?></span>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </aside>
    </section>
  </main>

  <?php include __DIR__ . "/partials/site-footer.php"; ?>
</div>

<?php echo shared_js_scripts(); ?>
</body>
</html>