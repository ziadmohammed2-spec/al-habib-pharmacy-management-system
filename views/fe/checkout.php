<?php

require_once __DIR__ . "/_app.php";
require_once __DIR__ . "/../../controllers/CheckoutController.php";

require_customer();

$checkoutController = new CheckoutController(app_db());
$customerId = current_customer_id();
$message = "";
$messageType = "success";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["place_order"])) {
    $orderId = $checkoutController->placeOrder($customerId, $_POST);

    if ($orderId) {
        redirect_to("checkout.php?success=1&order_id=" . urlencode((string) $orderId));
    }

    $message = "Failed to place order. Please confirm the cart, address, payment method and stock availability.";
    $messageType = "error";
}

$checkoutData = $checkoutController->index($customerId);
$orderItems = $checkoutData["items"];
$summary = $checkoutData["summary"];

$paymentMethods = [
    ["value" => "cash_on_delivery", "title" => "Cash on Delivery", "description" => "Pay in cash when your order arrives"],
    ["value" => "instapay", "title" => "Instapay", "description" => "Transfer to 01012345678"],
    ["value" => "vodafone_cash", "title" => "Vodafone Cash", "description" => "Transfer to 01123456789"]
];
$selectedPaymentMethod = $_POST["payment_method"] ?? "cash_on_delivery";

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - Al-Habib Pharmacy</title>
  <link rel="stylesheet" href="../../assets/css/pages/checkout.css">
<?php echo shared_css_links(); ?>
</head>
<body>
<div class="page-wrapper">
  <?php include __DIR__ . "/partials/customer-navbar.php"; ?>
<div class="blue-strip"></div>

  <main class="checkout-page">
    <h2 class="page-title">Checkout</h2>

    <?php if (isset($_GET["success"])): ?>
      <div class="alert-success">Order placed successfully. Order ID: #<?php echo e($_GET["order_id"] ?? ""); ?></div>
    <?php endif; ?>

    <?php if ($message !== ""): ?>
      <div class="<?php echo $messageType === "success" ? "alert-success" : "alert-error"; ?>"><?php echo e($message); ?></div>
    <?php endif; ?>

    <section class="steps">
      <div class="step active"><span>1</span><p>Shipping</p></div>
      <div class="line"></div>
      <div class="step active"><span>2</span><p>Payment</p></div>
      <div class="line"></div>
      <div class="step active"><span>3</span><p>Confirm Order</p></div>
    </section>

    <form method="POST" action="checkout.php">
      <section class="checkout-grid">
        <div class="shipping-card">
          <div class="section-title">
            <div class="round">A</div>
            <div><h3>Shipping Information</h3><p>Please enter your delivery details.</p></div>
          </div>

          <label>Full Name</label>
          <div class="input-box"><span>Name</span><input type="text" name="full_name" value="<?php echo e($_POST["full_name"] ?? ($_SESSION["name"] ?? "")); ?>" placeholder="Enter your full name" required></div>

          <label>Phone Number</label>
          <div class="input-box"><span>Tel</span><input type="text" name="phone" value="<?php echo e($_POST["phone"] ?? ""); ?>" placeholder="Enter your phone number" required></div>

          <label>Address</label>
          <textarea name="address" placeholder="Enter your complete address" required><?php echo e($_POST["address"] ?? ""); ?></textarea>

          <div class="two-cols">
            <div>
              <label>City</label>
              <div class="input-box"><span>City</span><input type="text" name="city" value="<?php echo e($_POST["city"] ?? ""); ?>" placeholder="Enter your city" required></div>
            </div>
            <div>
              <label>Postal Code</label>
              <div class="input-box"><span>Zip</span><input type="text" name="postal_code" value="<?php echo e($_POST["postal_code"] ?? ""); ?>" placeholder="Enter postal code"></div>
            </div>
          </div>

          <label>Delivery Notes (Optional)</label>
          <textarea name="delivery_notes" placeholder="Add any note for delivery"><?php echo e($_POST["delivery_notes"] ?? ""); ?></textarea>
        </div>

        <aside class="order-summary">
          <div class="section-title">
            <div class="round">O</div>
            <div><h3>Order Summary</h3><p><?php echo (int) $summary["item_count"]; ?> Items</p></div>
          </div>

          <?php if (empty($orderItems)): ?>
            <p>Your cart is empty. Please add products first.</p>
          <?php else: ?>
            <?php foreach ($orderItems as $item): ?>
              <div class="summary-product">
                <?php $imageSrc = product_image_src($item["image_url"] ?? "", ($item["product_id"] ?? "") . " " . ($item["name"] ?? "")); ?>
                <div class="mini-img"><img src="<?php echo e($imageSrc); ?>" alt="<?php echo e($item["name"]); ?>"></div>
                <div><h4><?php echo e($item["name"]); ?></h4><p>Qty: <?php echo (int) $item["quantity"]; ?></p></div>
                <strong>EGP <?php echo number_format((float) $item["subtotal"], 2); ?></strong>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <div class="summary-row"><span>Subtotal</span><strong>EGP <?php echo number_format((float) $summary["subtotal"], 2); ?></strong></div>
          <div class="summary-row"><span>Shipping Fee</span><strong>EGP <?php echo number_format((float) $summary["delivery_fee"], 2); ?></strong></div>
          <div class="total-row"><span>Total</span><strong>EGP <?php echo number_format((float) $summary["total"], 2); ?></strong></div>
          <div class="secure-box"><div><b>Secure Checkout</b><p>Your personal information is handled confidentially.</p></div></div>
        </aside>
      </section>

      <section class="payment-card">
        <div class="section-title">
          <div class="round">P</div>
          <div><h3>Payment Method</h3><p>Choose your preferred payment option.</p></div>
        </div>

        <div class="payment-options">
          <?php foreach ($paymentMethods as $method): ?>
            <?php $isSelected = $selectedPaymentMethod === $method["value"]; ?>
            <label class="payment-option <?php echo $isSelected ? "active" : ""; ?>">
              <input type="radio" name="payment_method" value="<?php echo e($method["value"]); ?>" <?php echo $isSelected ? "checked" : ""; ?>>
              <span class="radio"></span>
              <div>
                <h3><?php echo e($method["title"]); ?></h3>
                <p><?php echo e($method["description"]); ?></p>
              </div>
            </label>
          <?php endforeach; ?>
        </div>

        <div id="paymentTransferBox" class="payment-transfer-box" style="display:none;"></div>
        <div class="safe-note">All transactions are secure.</div>
        <button type="submit" name="place_order" class="place-order" <?php echo empty($orderItems) ? "disabled" : ""; ?>>Place Order</button>
      </section>
    </form>
  </main>

  <?php include __DIR__ . "/partials/site-footer.php"; ?>
</div>

<?php echo shared_js_scripts("checkout"); ?>
</body>
</html>
