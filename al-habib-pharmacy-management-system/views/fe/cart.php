<?php

require_once __DIR__ . "/_app.php";
require_once __DIR__ . "/../../controllers/CartController.php";

require_customer();

$cartController = new CartController(app_db());
$customerId = current_customer_id();
$message = isset($_GET["added"]) ? "Product added to cart successfully." : "";
$messageType = "success";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["add_to_cart"])) {
        $ok = $cartController->addToCart($customerId, $_POST["product_id"] ?? 0, $_POST["quantity"] ?? 1);
        $message = $ok ? "Product added to cart." : "Unable to add product. Please check stock.";
        $messageType = $ok ? "success" : "error";
    } elseif (isset($_POST["increase_quantity"])) {
        $cartController->increaseQuantity($_POST["cart_item_id"] ?? 0);
        redirect_to("cart.php");
    } elseif (isset($_POST["decrease_quantity"])) {
        $cartController->decreaseQuantity($_POST["cart_item_id"] ?? 0);
        redirect_to("cart.php");
    } elseif (isset($_POST["remove_item"])) {
        $cartController->removeItem($_POST["cart_item_id"] ?? 0);
        redirect_to("cart.php");
    } elseif (isset($_POST["clear_cart"])) {
        $cartController->clearCart($customerId);
        redirect_to("cart.php");
    }
}

$cartItems = $cartController->index($customerId);
$summary = $cartController->getCartSummary($customerId);
$relatedProducts = $cartController->getRelatedProducts(5);

function productImageClass($index)
{
    $classes = ["panadol", "calc", "blue", "yellow", "green", "light", "gold"];
    return $classes[$index % count($classes)];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cart - Al-Habib Pharmacy</title>
  <link rel="stylesheet" href="../../assets/css/pages/cart.css">
<?php echo shared_css_links(); ?>
</head>
<body>
<div class="page-wrapper">
  <?php include __DIR__ . "/partials/customer-navbar.php"; ?>
<section class="hero">
    <h2>Cart</h2>
    <p>Home <span>&gt;</span> <b>Cart</b></p>
  </section>

  <main class="cart-page">
    <?php if ($message !== ""): ?><div class="<?php echo $messageType === "success" ? "alert-success" : "alert-error"; ?>"><?php echo e($message); ?></div><?php endif; ?>

    <section class="cart-layout">
      <div class="cart-table-card">
        <div class="cart-header">
          <span>Product</span><span>Price</span><span>Quantity</span><span>Subtotal</span><span>Action</span>
        </div>

        <?php if (empty($cartItems)): ?>
          <div class="cart-item">
            <div class="product-info">
              <img class="empty-cart-img" src="../../assets/images/placeholders/empty-cart.png" alt="Empty cart">
              <div><h3>Your cart is empty</h3><p>Please add products to your cart first.</p></div>
            </div>
          </div>
        <?php else: ?>
          <?php foreach ($cartItems as $index => $item): ?>
            <div class="cart-item">
              <div class="product-info">
                <?php $imageSrc = product_image_src($item["image_url"] ?? "", ($item["product_id"] ?? "") . " " . ($item["name"] ?? "")); ?>
                <div class="product-img">
                  <img src="<?php echo e($imageSrc); ?>" alt="<?php echo e($item["name"]); ?>">
                </div>
                <div>
                  <h3><?php echo e($item["name"]); ?></h3>
                  <p>Pharmacy Product</p>
                  <strong>EGP <?php echo number_format((float) $item["unit_price"], 2); ?></strong>
                  <small><?php echo ((int) $item["stock"] > 0) ? "In Stock" : "Out of Stock"; ?></small>
                </div>
              </div>
              <div class="price">EGP <?php echo number_format((float) $item["unit_price"], 2); ?></div>
              <form class="quantity" method="POST" action="cart.php">
                <input type="hidden" name="cart_item_id" value="<?php echo (int) $item["cart_item_id"]; ?>">
                <button type="submit" name="decrease_quantity">-</button>
                <span><?php echo (int) $item["quantity"]; ?></span>
                <button type="submit" name="increase_quantity">+</button>
              </form>
              <div class="subtotal">EGP <?php echo number_format((float) $item["subtotal"], 2); ?></div>
              <div class="action">
                <form method="POST" action="cart.php">
                  <input type="hidden" name="cart_item_id" value="<?php echo (int) $item["cart_item_id"]; ?>">
                  <button type="submit" name="remove_item" class="delete">Delete</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <div class="cart-buttons">
          <a href="products.php" class="outline-btn">Continue Shopping</a>
          <form method="POST" action="cart.php"><button type="submit" name="clear_cart" class="outline-btn">Clear Cart</button></form>
        </div>
      </div>

      <aside class="summary-card">
        <h3>Cart Summary</h3>
        <div class="summary-row"><span>Subtotal (<?php echo (int) $summary["item_count"]; ?> items)</span><strong>EGP <?php echo number_format((float) $summary["subtotal"], 2); ?></strong></div>
        <div class="summary-row"><span>Delivery Fee</span><strong>EGP <?php echo number_format((float) $summary["delivery_fee"], 2); ?></strong></div>
        <hr>
        <p>Total Amount</p>
        <h2>EGP <?php echo number_format((float) $summary["total"], 2); ?></h2>
        <?php if ((int) $summary["item_count"] > 0): ?>
          <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
        <?php else: ?>
          <button type="button" class="checkout-btn disabled" disabled>Cart is Empty</button>
        <?php endif; ?>
        <div class="feature"><b>Secure & Confidential</b><span>Your information is safe with us</span></div>
        <div class="feature"><b>Fast & Reliable Delivery</b><span>Quick delivery at your doorstep</span></div>
        <div class="feature"><b>100% Genuine Medicines</b><span>Authentic products you can trust</span></div>
      </aside>
    </section>

    <section class="related">
      <h2>Related Products</h2>
      <div class="related-grid">
        <?php foreach ($relatedProducts as $index => $product): ?>
          <div class="product-card">
            <?php $imageSrc = product_image_src($product["image_url"] ?? "", ($product["product_id"] ?? "") . " " . ($product["name"] ?? "")); ?>
            <div class="related-img">
              <img src="<?php echo e($imageSrc); ?>" alt="<?php echo e($product["name"]); ?>">
            </div>
            <h3><?php echo e($product["name"]); ?></h3>
            <p>Available Stock: <?php echo (int) $product["stock"]; ?></p>
            <strong>EGP <?php echo number_format((float) $product["price"], 2); ?></strong>
            <form method="POST" action="cart.php">
              <input type="hidden" name="product_id" value="<?php echo (int) $product["product_id"]; ?>">
              <input type="hidden" name="quantity" value="1">
              <button type="submit" name="add_to_cart" <?php echo (int) $product["stock"] <= 0 ? "disabled" : ""; ?>>Add to Cart</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </main>

  <?php include __DIR__ . "/partials/site-footer.php"; ?>
</div>
<?php echo shared_js_scripts("cart"); ?>
</body>
</html>
