<?php

require_once __DIR__ . "/_app.php";
require_once __DIR__ . "/../../controllers/ProductController.php";
require_once __DIR__ . "/../../controllers/CartController.php";

$db = app_db();
$productController = new ProductController($db);
$cartController = new CartController($db);
$productId = (int) ($_GET["id"] ?? ($_POST["product_id"] ?? 0));
$product = $productController->show($productId);
$message = "";
$messageType = "success";

if (isset($_GET["added"])) {
    $message = "Product added to cart successfully. You can open the cart from the cart icon above.";
    $messageType = "success";
}

if (!$product) {
    http_response_code(404);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_to_cart"]) && $product) {
    if (!is_customer_user()) {
        redirect_to("login.php?error=login_required");
    }

    $added = $cartController->addToCart(current_customer_id(), $productId, $_POST["quantity"] ?? 1);

    if ($added) {
        redirect_to("product-details.php?id=" . urlencode((string) $productId) . "&added=1");
    }

    $message = "Unable to add this product. Please check stock.";
    $messageType = "error";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $product ? e($product["name"]) : "Product not found"; ?> - Al-Habib Pharmacy</title>
  <?php echo shared_css_links(); ?>
  <link rel="stylesheet" href="../../assets/css/pages/product-details.css?v=900">
</head>
<body>
<?php include __DIR__ . "/partials/customer-navbar.php"; ?>

<section class="hero">
  <h1>Product Details</h1>
  <p>Medicine information loaded from the Al-Habib Pharmacy database.</p>
</section>

<?php if (!$product): ?>
  <section class="product-section">
    <div class="product-info">
      <h2>Product not found</h2>
      <p>The requested product does not exist.</p>
      <a href="products.php">Back to products</a>
    </div>
  </section>
<?php else: ?>
  <section class="product-section">
    <div class="product-image">
      <?php $imageSrc = product_image_src($product["image_url"] ?? "", ($product["product_id"] ?? "") . " " . ($product["name"] ?? "")); ?>

      <?php if ($imageSrc !== ""): ?>
        <img class="product-main-img" src="<?php echo e($imageSrc); ?>" alt="<?php echo e($product["name"]); ?>">
      <?php else: ?>
        <div class="product-pack product-pack-large"><?php echo product_placeholder($product["name"]); ?></div>
      <?php endif; ?>
    </div>

    <div class="product-info">
      <?php if ($message !== ""): ?>
        <div class="<?php echo $messageType === "success" ? "alert-success" : "alert-error"; ?>">
          <?php echo e($message); ?>
        </div>
      <?php endif; ?>

      <span class="tag"><?php echo e($product["source"] ?? "local"); ?></span>

      <h2><?php echo e($product["name"]); ?></h2>

      <div class="price">
        EGP <?php echo number_format((float) $product["price"], 2); ?>
      </div>

      <p class="description">
        <?php echo e($product["generic_name"] ?: "This product is available through Al-Habib Pharmacy."); ?>
      </p>

      <div class="details-list">
        <div><strong>Category:</strong> <?php echo e($product["category_name"] ?? "Medicine"); ?></div>
        <div><strong>Company:</strong> <?php echo e($product["company_name"] ?? ($product["manufacturer_name"] ?? "N/A")); ?></div>

        <?php if (!empty($product["product_ndc"])): ?>
          <div><strong>NDC:</strong> <?php echo e($product["product_ndc"]); ?></div>
        <?php endif; ?>

        <?php if (!empty($product["dosage_form"])): ?>
          <div><strong>Dosage Form:</strong> <?php echo e($product["dosage_form"]); ?></div>
        <?php endif; ?>

        <?php if (!empty($product["route"])): ?>
          <div><strong>Route:</strong> <?php echo e($product["route"]); ?></div>
        <?php endif; ?>
      </div>

      <div class="stock">
        Available Stock: <?php echo (int) $product["stock"]; ?>
      </div>

      <form method="POST" action="product-details.php?id=<?php echo (int) $product["product_id"]; ?>">
        <input type="hidden" name="product_id" value="<?php echo (int) $product["product_id"]; ?>">

        <div class="quantity-box">
          <label for="quantity">Quantity</label>
          <input id="quantity" type="number" name="quantity" value="1" min="1" max="<?php echo max(1, (int) $product["stock"]); ?>">
        </div>

        <button class="cart-btn" name="add_to_cart" type="submit" <?php echo (int) $product["stock"] <= 0 ? "disabled" : ""; ?>>
          Add To Cart
        </button>
      </form>
    </div>
  </section>
<?php endif; ?>

<?php include __DIR__ . "/partials/site-footer.php"; ?>
<?php echo shared_js_scripts("product-details"); ?>
</body>
</html>