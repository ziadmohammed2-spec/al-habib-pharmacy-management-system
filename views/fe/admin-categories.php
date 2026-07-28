<?php
require_once __DIR__ . "/_app.php";
require_once __DIR__ . "/../../controllers/CategoryController.php";

$db = app_db();
$categoryController = new CategoryController($db);

require_admin();

function categoryDescription($name)
{
    $lower = strtolower($name);

    if (strpos($lower, 'vitamin') !== false) return 'Vitamins and dietary supplements.';
    if (strpos($lower, 'pain') !== false) return 'Medications for pain relief and fever.';
    if (strpos($lower, 'antibiotic') !== false) return 'Antibiotic medicines and treatments.';
    if (strpos($lower, 'allergy') !== false) return 'Allergy and antihistamine products.';
    if (strpos($lower, 'gastric') !== false) return 'Gastric and stomach care products.';

    return 'Medicines and pharmacy products.';
}

function getProductCountByCategory($db, $categoryId)
{
    $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = :category_id");
    $stmt->execute([":category_id" => $categoryId]);
    return (int) $stmt->fetchColumn();
}

$message = "";
$messageType = "success";
$editCategory = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add_category") {
        $name = trim($_POST["category_name"] ?? "");

        if ($name === "") {
            $message = "Category name is required.";
            $messageType = "error";
        } else {
            $result = $categoryController->store($name);
            $message = $result ? "Category added successfully." : "Failed to add category.";
            $messageType = $result ? "success" : "error";
        }
    }

    if ($action === "update_category") {
        $categoryId = (int) ($_POST["category_id"] ?? 0);
        $name = trim($_POST["category_name"] ?? "");

        if ($categoryId <= 0 || $name === "") {
            $message = "Valid category ID and name are required.";
            $messageType = "error";
        } else {
            $result = $categoryController->update($categoryId, $name);
            $message = $result ? "Category updated successfully." : "Failed to update category.";
            $messageType = $result ? "success" : "error";
        }
    }

    if ($action === "delete_category") {
        $categoryId = (int) ($_POST["category_id"] ?? 0);
        $result = $categoryId > 0 ? $categoryController->delete($categoryId) : false;
        $message = $result ? "Category deleted successfully." : "Failed to delete category.";
        $messageType = $result ? "success" : "error";
    }
}

if (isset($_GET["edit_category"])) {
    $editCategory = $categoryController->edit((int) $_GET["edit_category"]);
}

$search = trim($_GET["search"] ?? "");
$categories = $categoryController->index();

if ($search !== "") {
    $categories = array_filter($categories, function ($category) use ($search) {
        return stripos($category["name"], $search) !== false;
    });
}

$totalCategories = count($categoryController->index());
$totalProducts = (int) $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$activeCategories = $totalCategories;
$inactiveCategories = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Category Management</title>
<?php echo shared_css_links("admin"); ?>
  <link rel="stylesheet" href="../../assets/css/pages/admin-categories.css?v=900">
</head>
<body>
  <?php echo admin_header("categories"); ?>
  <div class="layout">
    <?php echo admin_sidebar("categories"); ?>
    <main class="content">
      <div class="page-title"><h1>Category Management</h1><p>ⓘ Dashboard <span>›</span> Manage Products <span>›</span> Categories</p></div>
      <?php if ($message !== ""): ?><div class="<?php echo $messageType === 'success' ? 'alert-success' : 'alert-error'; ?>"><?php echo e($message); ?></div><?php endif; ?>
      <section class="stats-grid">
        <div class="stat-card"><div class="stat-icon green">🗂️</div><div><p>Total Categories</p><h3><?php echo $totalCategories; ?></h3></div></div>
        <div class="stat-card"><div class="stat-icon orange">✅</div><div><p>Active Categories</p><h3 class="orange-text"><?php echo $activeCategories; ?></h3></div></div>
        <div class="stat-card"><div class="stat-icon red">⛔</div><div><p>Inactive Categories</p><h3 class="red-text"><?php echo $inactiveCategories; ?></h3></div></div>
        <div class="stat-card"><div class="stat-icon green">📦</div><div><p>Total Products</p><h3><?php echo $totalProducts; ?></h3></div></div>
      </section>
      <section class="card">
        <h2>Categories</h2>
        <form class="filters" method="GET" action="admin-categories.php">
          <div class="search-box"><input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search categories..." /><button type="submit">⌕</button></div>
          <select><option>All Status</option><option>Active</option><option>Inactive</option></select>
          <a class="add-btn" href="#category-form" style="text-decoration:none">+ Add Category</a>
        </form>
        <table><thead><tr><th>Category Name</th><th>Description</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        <?php if (empty($categories)): ?><tr><td colspan="5">No categories found.</td></tr><?php endif; ?>
        <?php foreach ($categories as $category): $count = getProductCountByCategory($db, $category["category_id"]); ?>
          <tr>
            <td><div class="category-name"><span class="cat-icon blue-icon">💊</span><?php echo e($category["name"]); ?></div></td>
            <td><?php echo e(categoryDescription($category["name"])); ?></td>
            <td><?php echo $count; ?></td>
            <td><span class="badge active">Active</span></td>
            <td class="actions">
              <a class="edit" title="Edit" href="admin-categories.php?edit_category=<?php echo $category["category_id"]; ?>#category-form">✏</a>
              <form class="inline-form" method="POST" action="admin-categories.php" onsubmit="return confirm('Delete this category?');">
                <input type="hidden" name="action" value="delete_category"><input type="hidden" name="category_id" value="<?php echo $category["category_id"]; ?>"><button class="delete" title="Delete" type="submit">🗑</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody></table>
        <div class="table-footer"><p>Showing <?php echo count($categories); ?> of <?php echo $totalCategories; ?> categories</p><div class="pagination"><button type="button">‹</button><button type="button" class="active-page">1</button><button type="button">›</button></div></div>
      </section>
      <section class="card add-category" id="category-form">
        <h2><?php echo $editCategory ? 'Edit Category' : 'Add New Category'; ?></h2>
        <form method="POST" action="admin-categories.php">
          <input type="hidden" name="action" value="<?php echo $editCategory ? 'update_category' : 'add_category'; ?>">
          <?php if ($editCategory): ?><input type="hidden" name="category_id" value="<?php echo e($editCategory['category_id']); ?>"><?php endif; ?>
          <div class="form-row"><div class="form-group"><label>Category Name <span>*</span></label><input type="text" name="category_name" value="<?php echo e($editCategory['name'] ?? ''); ?>" placeholder="Enter category name" required /></div><div class="form-group"><label>Status <span>*</span></label><select><option>Active</option><option>Inactive</option></select></div></div>
          <div class="form-group"><label>Description</label><textarea placeholder="Description is displayed automatically in this academic version."></textarea></div>
          <div class="form-actions"><a href="admin-categories.php" class="cancel-btn" style="text-decoration:none;text-align:center">Cancel</a><button type="submit" class="save-btn"><?php echo $editCategory ? 'Update Category' : 'Save Category'; ?></button></div>
        </form>
      </section>
    </main>
  </div>
  <footer><div class="footer-orange"><div class="footer-logo"><div class="footer-circle">Al-Habib</div><div><h3>Al-Habib</h3><p>Pharmacy</p></div></div><div class="footer-info">02 239 077 51 - 011 04 399 658</div><div class="footer-info">Saturday - Thursday : 9 AM - 11 PM<br>Friday : 2 PM - 11 PM</div></div><div class="footer-blue"><p>&copy; 2024 Al-Habib Pharmacy. All rights reserved.</p><div><a href="home.php">Terms & Conditions</a><span>|</span><a href="contact.php">Privacy Policy</a></div></div></footer>
<?php echo shared_js_scripts("admin"); ?>
</body>
</html>
