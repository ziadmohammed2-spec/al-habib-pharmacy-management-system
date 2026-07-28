<?php

require_once __DIR__ . "/_app.php";
require_once __DIR__ . "/../../controllers/ProductController.php";
require_once __DIR__ . "/../../controllers/CategoryController.php";
require_once __DIR__ . "/../../controllers/CompanyController.php";

require_admin();

$db = app_db();
$productController = new ProductController($db);
$categoryController = new CategoryController($db);
$companyController = new CompanyController($db);
$message = "";
$messageType = "success";

function upload_product_image(&$errorMessage)
{
    if (!isset($_FILES["product_image"]) || ($_FILES["product_image"]["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES["product_image"]["error"] !== UPLOAD_ERR_OK) {
        $errorMessage = "Product image upload failed.";
        return false;
    }

    $file = $_FILES["product_image"];
    $extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowed = ["jpg", "jpeg", "png", "webp", "avif"];

    if (!in_array($extension, $allowed, true)) {
        $errorMessage = "Allowed product image types are JPG, JPEG, PNG, WEBP and AVIF.";
        return false;
    }

    if ((int) $file["size"] > 3 * 1024 * 1024) {
        $errorMessage = "Product image must be 3 MB or smaller.";
        return false;
    }

    $uploadDir = __DIR__ . "/../../assets/images/products";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $baseName = strtolower(pathinfo($file["name"], PATHINFO_FILENAME));
    $baseName = preg_replace("/[^a-z0-9]+/", "-", $baseName);
    $baseName = trim($baseName, "-") ?: "product";
    $fileName = $baseName . "-" . date("YmdHis") . "." . $extension;
    $targetPath = $uploadDir . "/" . $fileName;

    if (!move_uploaded_file($file["tmp_name"], $targetPath)) {
        $errorMessage = "Failed to save uploaded product image.";
        return false;
    }

    return "assets/images/products/" . $fileName;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add_product") {
        $uploadError = "";
        $uploadedImageUrl = upload_product_image($uploadError);
        if ($uploadedImageUrl === false) {
            $message = $uploadError;
            $messageType = "error";
        } else {
            $extra = [
                "generic_name" => $_POST["generic_name"] ?? "",
                "brand_name" => $_POST["brand_name"] ?? "",
                "manufacturer_name" => $_POST["manufacturer_name"] ?? "",
                "product_ndc" => $_POST["product_ndc"] ?? "",
                "dosage_form" => $_POST["dosage_form"] ?? "",
                "route" => $_POST["route"] ?? "",
                "image_url" => $uploadedImageUrl ?: ($_POST["image_url"] ?? ""),
                "source" => $_POST["source"] ?? "local"
            ];
            $result = $productController->store($_POST["name"] ?? "", $_POST["price"] ?? 0, $_POST["stock"] ?? 0, $_POST["category_id"] ?? null, $_POST["company_id"] ?? null, $extra);
            $message = $result["message"];
            $messageType = $result["success"] ? "success" : "error";
        }
    } elseif ($action === "update_product") {
        $uploadError = "";
        $uploadedImageUrl = upload_product_image($uploadError);
        if ($uploadedImageUrl === false) {
            $message = $uploadError;
            $messageType = "error";
        } else {
            $extra = [
                "generic_name" => $_POST["generic_name"] ?? "",
                "brand_name" => $_POST["brand_name"] ?? "",
                "manufacturer_name" => $_POST["manufacturer_name"] ?? "",
                "product_ndc" => $_POST["product_ndc"] ?? "",
                "dosage_form" => $_POST["dosage_form"] ?? "",
                "route" => $_POST["route"] ?? "",
                "image_url" => $uploadedImageUrl ?: ($_POST["image_url"] ?? ""),
                "source" => $_POST["source"] ?? "local"
            ];
            $result = $productController->update($_POST["product_id"] ?? 0, $_POST["name"] ?? "", $_POST["price"] ?? 0, $_POST["stock"] ?? 0, $_POST["category_id"] ?? null, $_POST["company_id"] ?? null, $extra);
            $message = $result["message"];
            $messageType = $result["success"] ? "success" : "error";
        }
    } elseif ($action === "delete_product") {
        $ok = $productController->delete($_POST["product_id"] ?? 0);
        $message = $ok ? "Product deleted successfully." : "Failed to delete product.";
        $messageType = $ok ? "success" : "error";
    }
}

$search = trim($_GET["search"] ?? "");
$products = $productController->index($search);
$categories = $categoryController->index();
$companies = $companyController->index();
$editProduct = isset($_GET["edit"]) ? $productController->show((int) $_GET["edit"]) : null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Products</title>
<?php echo shared_css_links("admin"); ?>
  <link rel="stylesheet" href="../../assets/css/pages/admin-products.css?v=900">
</head>
<body>
<div class="page-wrapper">
  <?php echo admin_header("products"); ?>

  <div class="main-layout">
    <?php echo admin_sidebar("products"); ?>

    <main class="content">
      <div class="page-title"><h2>Product Management</h2><p>Dashboard / Manage Products</p></div>
      <?php if ($message !== ""): ?><div class="<?php echo $messageType === "success" ? "alert-success" : "alert-error"; ?>"><?php echo e($message); ?></div><?php endif; ?>

      <section class="products-card">
        <form class="filters-row" method="GET" action="admin-products.php">
          <div class="search-box"><input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search products..."></div>
          <button class="add-btn" type="submit">Search</button>
        </form>

        <div class="table-wrapper">
          <table>
            <thead><tr><th>ID</th><th>Product</th><th>Category</th><th>Company</th><th>Price</th><th>Stock</th><th>Source</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($products)): ?><tr><td colspan="8">No products found.</td></tr><?php endif; ?>
            <?php foreach ($products as $product): ?>
              <tr>
                <td><?php echo (int) $product["product_id"]; ?></td>
                <td>
                  <?php $imageSrc = product_image_src($product["image_url"] ?? "", ($product["product_id"] ?? "") . " " . ($product["name"] ?? "")); ?>
                  <div class="product-cell">
                    <div class="product-img"><img src="<?php echo e($imageSrc); ?>" alt="<?php echo e($product["name"]); ?>"></div>
                    <div><strong><?php echo e($product["name"]); ?></strong><br><small><?php echo e($product["product_ndc"] ?? ""); ?></small></div>
                  </div>
                </td>
                <td><?php echo e($product["category_name"] ?? ""); ?></td>
                <td><?php echo e($product["company_name"] ?? ""); ?></td>
                <td>EGP <?php echo number_format((float) $product["price"], 2); ?></td>
                <td><span class="<?php echo (int) $product["stock"] <= 5 ? "stock-red" : "stock-green"; ?>"><?php echo (int) $product["stock"]; ?></span></td>
                <td><?php echo e($product["source"] ?? "local"); ?></td>
                <td class="actions">
                  <a href="product-details.php?id=<?php echo (int) $product["product_id"]; ?>" title="View">View</a>
                  <a href="admin-products.php?edit=<?php echo (int) $product["product_id"]; ?>#product-form" title="Edit">Edit</a>
                  <form method="POST" action="admin-products.php" onsubmit="return confirm('Delete this product?');">
                    <input type="hidden" name="action" value="delete_product">
                    <input type="hidden" name="product_id" value="<?php echo (int) $product["product_id"]; ?>">
                    <button class="delete-btn" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="pagination-row"><p>Showing <?php echo count($products); ?> products</p></div>
      </section>

      <section class="products-card product-form" id="product-form">
        <h3><?php echo $editProduct ? "Edit Product" : "Add Product"; ?></h3>
        <form method="POST" action="admin-products.php#product-form" class="form-grid" enctype="multipart/form-data">
          <input type="hidden" name="action" value="<?php echo $editProduct ? "update_product" : "add_product"; ?>">
          <?php if ($editProduct): ?><input type="hidden" name="product_id" value="<?php echo (int) $editProduct["product_id"]; ?>"><?php endif; ?>

          <div><label>Product Name</label><input type="text" name="name" value="<?php echo e($editProduct["name"] ?? ""); ?>" required></div>
          <div><label>Brand Name</label><input type="text" name="brand_name" value="<?php echo e($editProduct["brand_name"] ?? ""); ?>"></div>
          <div><label>Price</label><input type="number" step="0.01" min="0" name="price" value="<?php echo e($editProduct["price"] ?? "0"); ?>" required></div>
          <div><label>Stock</label><input type="number" min="0" name="stock" value="<?php echo e($editProduct["stock"] ?? "0"); ?>" required></div>
          <div><label>Category</label><select name="category_id"><option value="">Select Category</option><?php foreach ($categories as $category): ?><option value="<?php echo (int) $category["category_id"]; ?>" <?php echo isset($editProduct["category_id"]) && (int) $editProduct["category_id"] === (int) $category["category_id"] ? "selected" : ""; ?>><?php echo e($category["name"]); ?></option><?php endforeach; ?></select></div>
          <div><label>Company</label><select name="company_id"><option value="">Select Company</option><?php foreach ($companies as $company): ?><option value="<?php echo (int) $company["company_id"]; ?>" <?php echo isset($editProduct["company_id"]) && (int) $editProduct["company_id"] === (int) $company["company_id"] ? "selected" : ""; ?>><?php echo e($company["name"]); ?></option><?php endforeach; ?></select></div>
          <div><label>Generic Name</label><input type="text" name="generic_name" value="<?php echo e($editProduct["generic_name"] ?? ""); ?>"></div>
          <div><label>Manufacturer</label><input type="text" name="manufacturer_name" value="<?php echo e($editProduct["manufacturer_name"] ?? ""); ?>"></div>
          <div><label>Product NDC</label><input type="text" name="product_ndc" value="<?php echo e($editProduct["product_ndc"] ?? ""); ?>"></div>
          <div><label>Dosage Form</label><input type="text" name="dosage_form" value="<?php echo e($editProduct["dosage_form"] ?? ""); ?>"></div>
          <div><label>Route</label><input type="text" name="route" value="<?php echo e($editProduct["route"] ?? ""); ?>"></div>
          <div><label>Source</label><select name="source"><option value="local" <?php echo ($editProduct["source"] ?? "") !== "openFDA" ? "selected" : ""; ?>>local</option><option value="openFDA" <?php echo ($editProduct["source"] ?? "") === "openFDA" ? "selected" : ""; ?>>openFDA</option></select></div>
          <div class="wide image-upload-field">
            <label>Product Image</label>
            <?php $previewSrc = product_image_src($editProduct["image_url"] ?? ""); ?>
            <div class="image-upload-row">
              <img id="productImagePreview" src="<?php echo e($previewSrc); ?>" alt="Product image preview">
              <div>
                <input type="file" name="product_image" id="product_image" accept=".jpg,.jpeg,.png,.webp,.avif">
                <input type="text" name="image_url" value="<?php echo e($editProduct["image_url"] ?? ""); ?>" placeholder="assets/images/products/panadol.jpg">
              </div>
            </div>
          </div>
          <div class="wide"><button class="add-btn" type="submit"><?php echo $editProduct ? "Update Product" : "Save Product"; ?></button> <a href="admin-products.php" class="view-link">Cancel</a></div>
        </form>
      </section>
    </main>
  </div>
</div>
<?php echo shared_js_scripts("admin"); ?>
</body>
</html>
