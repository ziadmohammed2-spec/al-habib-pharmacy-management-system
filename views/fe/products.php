<?php

require_once __DIR__ . "/_app.php";
require_once __DIR__ . "/../../controllers/ProductController.php";
require_once __DIR__ . "/../../controllers/CartController.php";

$db = app_db();
$productController = new ProductController($db);
$cartController = new CartController($db);

$message = "";
$messageType = "success";
$currentQueryString = $_SERVER["QUERY_STRING"] ?? "";
$currentProductsUrl = "products.php" . ($currentQueryString !== "" ? "?" . $currentQueryString : "");

if (isset($_GET["added"])) {
    $message = "Product added to cart successfully. You can open the cart from the cart icon above.";
    $messageType = "success";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_to_cart"])) {
    if (!is_customer_user()) {
        redirect_to("login.php?error=login_required");
    }

    $added = $cartController->addToCart(current_customer_id(), $_POST["product_id"] ?? 0, $_POST["quantity"] ?? 1);

    if ($added) {
        $returnParams = $_GET;
        $returnParams["added"] = 1;
        redirect_to("products.php?" . http_build_query($returnParams));
    }

    $message = "Unable to add this product. Please check stock.";
    $messageType = "error";
}

$search = trim($_GET["search"] ?? "");
$categoryId = (int) ($_GET["category_id"] ?? 0);
$companyId = (int) ($_GET["company_id"] ?? 0);
$products = $productController->index($search, $categoryId ?: null, $companyId ?: null);

$categories = $db->query("
    SELECT c.category_id, c.name, COUNT(p.product_id) AS product_count
    FROM categories c
    INNER JOIN products p ON p.category_id = c.category_id
    GROUP BY c.category_id, c.name
    ORDER BY c.name
")->fetchAll(PDO::FETCH_ASSOC);
$companies = $db->query("
    SELECT co.company_id, co.name, COUNT(p.product_id) AS product_count
    FROM companies co
    INNER JOIN products p ON p.company_id = co.company_id
    GROUP BY co.company_id, co.name
    ORDER BY co.name
")->fetchAll(PDO::FETCH_ASSOC);

function products_filter_url($updates = [])
{
    $params = [
        "search" => trim($_GET["search"] ?? ""),
        "category_id" => (int) ($_GET["category_id"] ?? 0),
        "company_id" => (int) ($_GET["company_id"] ?? 0),
    ];

    foreach ($updates as $key => $value) {
        $params[$key] = $value;
    }

    $params = array_filter($params, function ($value, $key) {
        if ($key === "search") {
            return trim((string) $value) !== "";
        }

        return (int) $value > 0;
    }, ARRAY_FILTER_USE_BOTH);

    $query = http_build_query($params);
    return "products.php" . ($query !== "" ? "?" . $query : "");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Al-Habib Pharmacy - Products</title>
  <link rel="stylesheet" href="../../assets/css/pages/products.css">
<?php echo shared_css_links(); ?>
</head>
<body>
<div class="page">
  <?php include __DIR__ . "/partials/customer-navbar.php"; ?>
<section class="hero">
    <div class="hero-text">
      <h1>Our Products</h1>
      <p>Browse medicines, health products and personal care items.</p>
    </div>
    <div class="doctor-box"><div class="doctor-circle"><div class="doctor-face">+</div></div></div>
  </section>

  <main class="content">
    <aside class="sidebar">
      <div class="side-card">
        <h3>Categories</h3>
        <?php foreach ($categories as $category): ?>
          <a class="side-row <?php echo $categoryId === (int) $category["category_id"] ? "active" : ""; ?>" href="<?php echo e(products_filter_url(["category_id" => (int) $category["category_id"]])); ?>">
            <span><?php echo e($category["name"]); ?></span><small><?php echo (int) $category["product_count"]; ?></small>
          </a>
        <?php endforeach; ?>
        <a class="view-link" href="products.php">View All</a>
      </div>

      <div class="side-card">
        <h3>Top Companies</h3>
        <?php foreach ($companies as $company): ?>
          <a class="side-row <?php echo $companyId === (int) $company["company_id"] ? "active" : ""; ?>" href="<?php echo e(products_filter_url(["company_id" => (int) $company["company_id"]])); ?>">
            <span><?php echo e($company["name"]); ?></span><small><?php echo (int) $company["product_count"]; ?></small>
          </a>
        <?php endforeach; ?>
      </div>
    </aside>

    <section class="products-section">
      <?php if ($message !== ""): ?><div class="<?php echo $messageType === "success" ? "alert-success" : "alert-error"; ?>"><?php echo e($message); ?></div><?php endif; ?>

      <div class="toolbar">
        <form method="GET" action="products.php">
          <div class="search-box">
            <span>Search</span>
            <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search medicines, brands or NDC">
          </div>
          <select name="category_id">
            <option value="">All Categories</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?php echo (int) $category["category_id"]; ?>" <?php echo $categoryId === (int) $category["category_id"] ? "selected" : ""; ?>><?php echo e($category["name"]); ?></option>
            <?php endforeach; ?>
          </select>
          <select name="company_id">
            <option value="">All Companies</option>
            <?php foreach ($companies as $company): ?>
              <option value="<?php echo (int) $company["company_id"]; ?>" <?php echo $companyId === (int) $company["company_id"] ? "selected" : ""; ?>><?php echo e($company["name"]); ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit">Filter</button>
          <a class="clear-filters" href="products.php">Reset Filters</a>
        </form>
      </div>

      <p class="showing">Showing <?php echo count($products); ?> products from database</p>

      <div class="products-grid">
        <?php if (empty($products)): ?>
          <p class="empty-state">No products found for the selected search, category and company filters.</p>
        <?php endif; ?>

        <?php foreach ($products as $product): ?>
          <div class="product-card">
            <div class="img-box">
              <?php $imageSrc = product_image_src($product["image_url"] ?? "", ($product["product_id"] ?? "") . " " . ($product["name"] ?? "")); ?>
              <?php if ($imageSrc !== ""): ?>
                <img src="<?php echo e($imageSrc); ?>" alt="<?php echo e($product["name"]); ?>">
              <?php else: ?>
                <div class="product-pack"><?php echo product_placeholder($product["name"]); ?></div>
              <?php endif; ?>
            </div>
            <h4><?php echo e($product["name"]); ?></h4>
            <p><?php echo e($product["category_name"] ?? "Medicine"); ?><?php echo !empty($product["company_name"]) ? " - " . e($product["company_name"]) : ""; ?></p>
            <p>Stock: <?php echo (int) $product["stock"]; ?></p>
            <strong>EGP <?php echo number_format((float) $product["price"], 2); ?></strong>
            <span class="tag pain"><?php echo e($product["source"] ?? "local"); ?></span>
            <a class="view-details" href="product-details.php?id=<?php echo (int) $product["product_id"]; ?>">View Details</a>
            <form method="POST" action="<?php echo e($currentProductsUrl); ?>">
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
<?php echo shared_js_scripts("products"); ?>
</body>
</html>
