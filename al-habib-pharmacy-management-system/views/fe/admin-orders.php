<?php

require_once __DIR__ . "/_app.php";
require_once __DIR__ . "/../../controllers/AdminOrderController.php";

require_admin();

$adminOrderController = new AdminOrderController(app_db());
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["order_id"], $_POST["status"])) {
    $message = $adminOrderController->updateOrderStatus($_POST["order_id"], $_POST["status"])
        ? "Order status updated."
        : "Unable to update order status.";
}

$orders = $adminOrderController->getAllOrders();
$stats = $adminOrderController->getOrderStats();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Management</title>
<?php echo shared_css_links("admin"); ?>
  <link rel="stylesheet" href="../../assets/css/pages/admin-orders.css?v=900">
</head>
<body>
<div class="page-wrapper">
  <?php echo admin_header("orders"); ?>

  <div class="layout">
    <?php echo admin_sidebar("orders"); ?>

    <main class="content">
      <div class="page-title"><h1>Order Management</h1><p>Dashboard &gt; Manage Orders</p></div>
      <?php if ($message !== ""): ?><div class="alert-success"><?php echo e($message); ?></div><?php endif; ?>

      <section class="stats-grid">
        <div class="stat-card"><div><h4>Total Orders</h4><h2><?php echo (int) $stats["total_orders"]; ?></h2></div></div>
        <div class="stat-card"><div><h4>Pending Orders</h4><h2><?php echo (int) $stats["pending"]; ?></h2></div></div>
        <div class="stat-card"><div><h4>Processing Orders</h4><h2><?php echo (int) $stats["processing"]; ?></h2></div></div>
        <div class="stat-card"><div><h4>Delivered Orders</h4><h2><?php echo (int) $stats["delivered"]; ?></h2></div></div>
      </section>

      <section class="orders-box">
        <div class="table-wrapper">
          <table>
            <thead><tr><th>Order ID</th><th>Customer Name</th><th>Date</th><th>Amount (EGP)</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($orders)): ?>
              <tr><td colspan="6" style="text-align:center;">No orders found</td></tr>
            <?php else: ?>
              <?php foreach ($orders as $order): ?>
                <tr>
                  <td>#<?php echo (int) $order["order_id"]; ?></td>
                  <td><?php echo e($order["customer_name"]); ?></td>
                  <td><?php echo e(date("d M Y", strtotime($order["order_date"]))); ?></td>
                  <td><?php echo number_format((float) $order["total"], 2); ?></td>
                  <td><span class="status <?php echo e(strtolower($order["status"])); ?>"><?php echo e($order["status"]); ?></span></td>
                  <td class="actions">
                    <form method="POST" action="admin-orders.php" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                      <input type="hidden" name="order_id" value="<?php echo (int) $order["order_id"]; ?>">
                      <select name="status">
                        <?php foreach (["Pending", "Processing", "Shipped", "Delivered", "Cancelled"] as $status): ?>
                          <option value="<?php echo e($status); ?>" <?php echo $order["status"] === $status ? "selected" : ""; ?>><?php echo e($status); ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button class="icon-btn edit" type="submit">Update</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="table-footer"><p>Showing <?php echo count($orders); ?> orders</p></div>
      </section>
    </main>
  </div>
</div>
<?php echo shared_js_scripts("admin"); ?>
</body>
</html>
