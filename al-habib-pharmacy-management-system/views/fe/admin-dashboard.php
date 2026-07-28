<?php

require_once __DIR__ . "/_app.php";
require_once __DIR__ . "/../../controllers/DashboardController.php";

require_admin();

$db = app_db();
$dashboardController = new DashboardController($db);
$stats = $dashboardController->getDashboardStats();
$recentOrders = $dashboardController->getRecentOrders();
$lowStockProducts = $dashboardController->getLowStockProducts();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
<?php echo shared_css_links("admin"); ?>
  <link rel="stylesheet" href="../../assets/css/pages/admin-dashboard.css?v=900">
</head>
<body>
<div class="page-wrapper">
  <?php echo admin_header("dashboard"); ?>

  <div class="main-layout">
    <?php echo admin_sidebar("dashboard"); ?>

    <main class="content">
      <section class="page-title"><h2>Welcome, Admin</h2><p>Store overview and latest activity.</p></section>

      <section class="stats-grid">
        <div class="stat-card"><p>Total Products</p><h3><?php echo (int) $stats["total_products"]; ?></h3></div>
        <div class="stat-card"><p>Pending Orders</p><h3><?php echo (int) $stats["pending_orders"]; ?></h3></div>
        <div class="stat-card"><p>Pending Prescriptions</p><h3><?php echo (int) $stats["new_prescriptions"]; ?></h3></div>
        <div class="stat-card"><p>Unread Messages</p><h3><?php echo (int) $stats["messages"]; ?></h3></div>
      </section>

      <section class="dashboard-grid">
        <div class="card">
          <h3>Recent Orders</h3>
          <table>
            <thead><tr><th>Order</th><th>Date</th><th>Status</th><th>Total</th></tr></thead>
            <tbody>
            <?php if (empty($recentOrders)): ?><tr><td colspan="4">No orders yet.</td></tr><?php endif; ?>
            <?php foreach ($recentOrders as $order): ?>
              <tr>
                <td>#<?php echo (int) $order["order_id"]; ?></td>
                <td><?php echo e($order["order_date"]); ?></td>
                <td><?php echo e($order["status"]); ?></td>
                <td>EGP <?php echo number_format((float) $order["total"], 2); ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          <p style="margin-top:16px;"><a href="admin-orders.php">Manage all orders</a></p>
        </div>

        <div class="card">
          <h3>Low Stock Products</h3>
          <table>
            <thead><tr><th>Product</th><th>Category</th><th>Stock</th></tr></thead>
            <tbody>
            <?php if (empty($lowStockProducts)): ?><tr><td colspan="3">No products found.</td></tr><?php endif; ?>
            <?php foreach ($lowStockProducts as $product): ?>
              <tr>
                <td><?php echo e($product["name"]); ?></td>
                <td><?php echo e($product["category_name"] ?? ""); ?></td>
                <td><?php echo (int) $product["stock"]; ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          <p style="margin-top:16px;"><a href="admin-products.php">Update stock</a></p>
        </div>
      </section>
    </main>
  </div>
</div>
<?php echo shared_js_scripts("admin"); ?>
</body>
</html>
