<?php

require_once __DIR__ . "/_app.php";
require_once __DIR__ . "/../../controllers/OrderController.php";

require_customer();

$orderController = new OrderController(app_db());
$customerId = current_customer_id();
$orders = $orderController->getCustomerOrders($customerId);
$summary = $orderController->getOrderSummary($customerId);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Al-Habib Pharmacy - My Orders</title>
  <link rel="stylesheet" href="../../assets/css/pages/orders.css">
<?php echo shared_css_links(); ?>
</head>
<body>
<div class="page">
  <?php include __DIR__ . "/partials/customer-navbar.php"; ?>
<section class="hero">
    <div class="hero-text"><h1>My Orders</h1><p>View your order history and track deliveries in one place.</p></div>
    <div class="doctor-box"><div class="doctor-circle"><div class="doctor-face">+</div></div></div>
  </section>

  <main class="orders-layout">
    <section class="orders-card">
      <h2>All Orders</h2>
      <div class="orders-tools">
        <input type="text" placeholder="Search by Order ID or Product">
        <select><option>All Status</option><option>Delivered</option><option>Processing</option><option>Shipped</option><option>Cancelled</option></select>
      </div>

      <table>
        <thead>
          <tr><th>Order ID</th><th>Date</th><th>Items</th><th>Total Amount</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody>
        <?php if (empty($orders)): ?>
          <tr><td colspan="6" style="text-align:center;">No orders found</td></tr>
        <?php else: ?>
          <?php foreach ($orders as $order): ?>
            <?php $statusClass = strtolower($order["status"]); ?>
            <tr>
              <td class="order-id">#<?php echo (int) $order["order_id"]; ?></td>
              <td><?php echo e(date("d M Y", strtotime($order["order_date"]))); ?></td>
              <td>
                <?php $imageSrc = product_image_src($order["first_image_url"] ?? "", ($order["order_id"] ?? "") . " " . ($order["first_product_name"] ?? "")); ?>
                <div class="order-items-cell">
                  <img src="<?php echo e($imageSrc); ?>" alt="<?php echo e($order["product_names"] ?? "Order items"); ?>">
                  <div>
                    <strong><?php echo (int) $order["item_count"]; ?> <?php echo (int) $order["item_count"] === 1 ? "Item" : "Items"; ?></strong>
                    <small><?php echo e($order["product_names"] ?? "Pharmacy products"); ?></small>
                  </div>
                </div>
              </td>
              <td>EGP <?php echo number_format((float) $order["total"], 2); ?></td>
              <td><span class="status <?php echo e($statusClass); ?>"><?php echo e($order["status"]); ?></span></td>
              <td><a href="order-tracking.php?order_id=<?php echo (int) $order["order_id"]; ?>"><button>View Details</button></a></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </section>

    <aside class="right-side">
      <div class="side-card">
        <h3>Order Summary</h3>
        <div class="summary-box"><span class="circle blue">#</span><div><p>Total Orders</p><h4><?php echo (int) $summary["total_orders"]; ?></h4></div></div>
        <div class="summary-box"><span class="circle green">OK</span><div><p>Delivered Orders</p><h4><?php echo (int) $summary["delivered"]; ?></h4></div></div>
        <div class="summary-box"><span class="circle orange">...</span><div><p>Processing</p><h4><?php echo (int) $summary["processing"]; ?></h4></div></div>
        <div class="summary-box"><span class="circle blue">S</span><div><p>Shipped</p><h4><?php echo (int) $summary["shipped"]; ?></h4></div></div>
        <div class="summary-box"><span class="circle gray">X</span><div><p>Cancelled</p><h4><?php echo (int) $summary["cancelled"]; ?></h4></div></div>
      </div>

      <div class="side-card">
        <h3>Recent Activity</h3>
        <?php foreach (array_slice($orders, 0, 3) as $order): ?>
          <div class="activity"><span class="dot green"></span><div><p>Order #<?php echo (int) $order["order_id"]; ?> <?php echo e($order["status"]); ?></p><small><?php echo e(date("d M Y", strtotime($order["order_date"]))); ?></small></div></div>
        <?php endforeach; ?>
      </div>
    </aside>
  </main>

  <section class="help-card">
    <div class="help-left"><div><h3>Need help with your order?</h3><p>Our support team is here to assist you.</p></div></div>
    <a href="contact.php"><button>Contact Support</button></a>
  </section>

  <?php include __DIR__ . "/partials/site-footer.php"; ?>
</div>
<?php echo shared_js_scripts(); ?>
</body>
</html>
