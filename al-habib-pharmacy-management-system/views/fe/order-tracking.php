<?php

require_once __DIR__ . "/_app.php";
require_once __DIR__ . "/../../controllers/OrderController.php";

require_customer();

$orderController = new OrderController(app_db());
$customerId = current_customer_id();
$recentOrders = $orderController->getRecentOrders($customerId);
$orderId = (int) str_replace("#", "", $_GET["order_id"] ?? "");

if ($orderId <= 0 && !empty($recentOrders)) {
    $orderId = (int) $recentOrders[0]["order_id"];
}

$order = $orderId > 0 ? $orderController->getOrderById($orderId, $customerId) : null;
$orderItems = $order ? $orderController->getOrderItems($orderId) : [];

function getStepClass($status, $stepIndex)
{
    $status = strtolower($status);
    $currentStep = 0;

    if ($status === "processing") $currentStep = 1;
    elseif ($status === "shipped") $currentStep = 2;
    elseif ($status === "delivered") $currentStep = 3;
    elseif ($status === "cancelled") return "";

    if ($stepIndex < $currentStep) return "done";
    if ($stepIndex === $currentStep) return "active";
    return "";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Tracking - Al-Habib Pharmacy</title>
  <link rel="stylesheet" href="../../assets/css/pages/order-tracking.css">
<?php echo shared_css_links(); ?>
</head>
<body>
<div class="page-wrapper">
  <?php include __DIR__ . "/partials/customer-navbar.php"; ?>
<section class="tracking-hero">
    <div><h2>Order Tracking</h2><p>Track your orders and stay updated on every delivery step.</p></div>
    <div class="doctor-img">+</div>
  </section>

  <main class="tracking-page">
    <section class="track-card">
      <div class="big-icon">#</div>
      <div class="track-content">
        <h3>Track Your Order</h3>
        <p>Enter your Order ID to view the latest status.</p>
        <form class="track-form" method="GET" action="order-tracking.php">
          <input type="text" name="order_id" placeholder="Enter Order ID (e.g., #1)" value="<?php echo $orderId > 0 ? "#" . (int) $orderId : ""; ?>">
          <button type="submit">Track Order</button>
        </form>
      </div>
    </section>

    <section class="status-card">
      <h3>Current Order Status</h3>
      <?php if (!$order): ?>
        <div class="order-box"><p style="text-align:center;padding:30px;">Order not found</p></div>
      <?php else: ?>
        <div class="order-box">
          <div class="order-head"><h4>Order #<?php echo (int) $order["order_id"]; ?></h4><div class="current-status"><?php echo e($order["status"]); ?></div></div>

          <div class="order-info-grid">
            <div><b>Date Placed</b><span><?php echo e(date("d M Y", strtotime($order["order_date"]))); ?></span></div>
            <div><b>Total Amount</b><span class="orange">EGP <?php echo number_format((float) $order["total"], 2); ?></span></div>
            <div><b>Items</b><span><?php echo (int) $order["item_count"]; ?> <?php echo (int) $order["item_count"] === 1 ? "Item" : "Items"; ?></span></div>
          </div>

          <div class="progress">
            <div class="progress-line"></div>
            <div class="progress-step <?php echo getStepClass($order["status"], 0); ?>"><span>1</span><b>Order Placed</b><p><?php echo e(date("d M Y", strtotime($order["order_date"]))); ?></p></div>
            <div class="progress-step <?php echo getStepClass($order["status"], 1); ?>"><span>2</span><b>Processing</b><p>Preparing your order</p></div>
            <div class="progress-step <?php echo getStepClass($order["status"], 2); ?>"><span>3</span><b>Shipped</b><p>Order is on the way</p></div>
            <div class="progress-step <?php echo getStepClass($order["status"], 3); ?>"><span>4</span><b>Delivered</b><p>Order delivered successfully</p></div>
          </div>

          <div class="details-grid">
            <div class="detail-card">
              <h4>Delivery Details</h4>
              <p><b>Delivery Address</b><br><?php echo e($order["delivery_address"]); ?></p>
              <p><b>Delivery Partner</b><br>Al-Habib Pharmacy Delivery <span>Tracking ID: AHP<?php echo (int) $order["order_id"]; ?></span></p>
            </div>

            <div class="detail-card summary-mini">
              <h4>Order Summary</h4>
              <?php if (empty($orderItems)): ?>
                <p>No items found for this order.</p>
              <?php else: ?>
                <?php foreach ($orderItems as $item): ?>
                  <div class="mini-order">
                    <?php $imageSrc = product_image_src($item["image_url"] ?? "", ($item["product_id"] ?? "") . " " . ($item["name"] ?? "")); ?>
                    <div class="mini-img"><img src="<?php echo e($imageSrc); ?>" alt="<?php echo e($item["product_name"]); ?>"></div>
                    <p><b><?php echo e($item["product_name"]); ?></b><br><?php echo (int) $item["quantity"]; ?> x EGP <?php echo number_format((float) $item["price"], 2); ?></p>
                    <strong>EGP <?php echo number_format((float) $item["quantity"] * (float) $item["price"], 2); ?></strong>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
              <div class="summary-line"><span>Total Amount</span><b>EGP <?php echo number_format((float) $order["total"], 2); ?></b></div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </section>

    <section class="recent-card">
      <div class="recent-title"><h3>Recent Orders</h3><a href="orders.php">View All Orders &gt;</a></div>
      <table>
        <thead><tr><th>Order ID</th><th>Date</th><th>Items</th><th>Total Amount</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php if (empty($recentOrders)): ?>
          <tr><td colspan="6" style="text-align:center;">No recent orders found</td></tr>
        <?php else: ?>
          <?php foreach ($recentOrders as $recent): ?>
            <tr>
              <td>#<?php echo (int) $recent["order_id"]; ?></td>
              <td><?php echo e(date("d M Y", strtotime($recent["order_date"]))); ?></td>
              <td><?php echo (int) $recent["item_count"]; ?></td>
              <td>EGP <?php echo number_format((float) $recent["total"], 2); ?></td>
              <td><span class="<?php echo e(strtolower($recent["status"])); ?>"><?php echo e($recent["status"]); ?></span></td>
              <td><a href="order-tracking.php?order_id=<?php echo (int) $recent["order_id"]; ?>"><button>View Details</button></a></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </section>
  </main>

  <?php include __DIR__ . "/partials/site-footer.php"; ?>
</div>
<?php echo shared_js_scripts(); ?>
</body>
</html>
