<?php
require_once __DIR__ . "/_app.php";
require_once __DIR__ . "/../../controllers/CompanyController.php";

$db = app_db();
$companyController = new CompanyController($db);

require_admin();

function companyCountry($name)
{
    $countries = ["Egypt", "USA", "UK", "Germany", "France", "UAE"];
    return $countries[abs(crc32($name)) % count($countries)];
}

function companyLogoClass($name)
{
    $classes = ["panadol", "pfizer", "eva", "julphar", "sanofi", "bayer", "glaxo"];
    return $classes[abs(crc32($name)) % count($classes)];
}

$message = "";
$messageType = "success";
$editCompany = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add_company") {
        $result = $companyController->store($_POST["company_name"] ?? "");
        $message = $result["message"];
        $messageType = $result["success"] ? "success" : "error";
    }

    if ($action === "update_company") {
        $result = $companyController->update($_POST["company_id"] ?? 0, $_POST["company_name"] ?? "");
        $message = $result["message"];
        $messageType = $result["success"] ? "success" : "error";
    }

    if ($action === "delete_company") {
        $result = $companyController->delete($_POST["company_id"] ?? 0);
        $message = $result["message"];
        $messageType = $result["success"] ? "success" : "error";
    }
}

if (isset($_GET["edit_company"])) {
    $editCompany = $companyController->edit((int) $_GET["edit_company"]);
}

$search = trim($_GET["search"] ?? "");
$companies = $companyController->getCompaniesWithProductCount($search);
$stats = $companyController->getStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Company Management</title>
<?php echo shared_css_links("admin"); ?>
  <link rel="stylesheet" href="../../assets/css/pages/admin-company.css?v=900">
</head>
<body>
  <?php echo admin_header("companies"); ?>
  <div class="layout"><?php echo admin_sidebar("companies"); ?>
    <main class="content"><div class="page-title"><h1>Company Management</h1><p>ⓘ Dashboard <span>›</span> Manage Products <span>›</span> Companies</p></div>
      <?php if ($message !== ""): ?><div class="<?php echo $messageType === 'success' ? 'alert-success' : 'alert-error'; ?>"><?php echo e($message); ?></div><?php endif; ?>
      <section class="stats-grid"><div class="stat-card"><div class="stat-icon green">🏢</div><div><p>Total Companies</p><h3><?php echo $stats["total_companies"]; ?></h3></div></div><div class="stat-card"><div class="stat-icon green">✅</div><div><p>Active Companies</p><h3><?php echo $stats["active_companies"]; ?></h3></div></div><div class="stat-card"><div class="stat-icon red">⛔</div><div><p>Inactive Companies</p><h3 class="red-text"><?php echo $stats["inactive_companies"]; ?></h3></div></div><div class="stat-card"><div class="stat-icon green">📦</div><div><p>Total Products</p><h3><?php echo $stats["total_products"]; ?></h3></div></div></section>
      <section class="card"><h2>Companies</h2><form class="filters" method="GET" action="admin-company.php"><div class="search-box"><input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search companies..." /><button type="submit">⌕</button></div><select><option>All Status</option><option>Active</option><option>Inactive</option></select><a class="add-btn" href="#company-form" style="text-decoration:none">+ Add Company</a></form>
        <table><thead><tr><th>Company Name</th><th>Country</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        <?php if (empty($companies)): ?><tr><td colspan="5">No companies found.</td></tr><?php endif; ?>
        <?php foreach ($companies as $company): ?>
          <tr><td><div class="company-name"><span class="company-logo <?php echo companyLogoClass($company['name']); ?>"><?php echo e(substr($company['name'], 0, 8)); ?></span><?php echo e($company["name"]); ?></div></td><td><?php echo e(companyCountry($company["name"])); ?></td><td><?php echo (int) $company["product_count"]; ?></td><td><span class="badge active">Active</span></td><td class="actions"><a class="edit" title="Edit" href="admin-company.php?edit_company=<?php echo $company["company_id"]; ?>#company-form">✏</a><form class="inline-form" method="POST" action="admin-company.php" onsubmit="return confirm('Delete this company?');"><input type="hidden" name="action" value="delete_company"><input type="hidden" name="company_id" value="<?php echo $company["company_id"]; ?>"><button class="delete" title="Delete" type="submit">🗑</button></form></td></tr>
        <?php endforeach; ?>
        </tbody></table><div class="table-footer"><p>Showing <?php echo count($companies); ?> of <?php echo $stats["total_companies"]; ?> companies</p><div class="pagination"><button type="button">‹</button><button type="button" class="active-page">1</button><button type="button">›</button></div></div></section>
      <section class="card add-company" id="company-form"><h2><?php echo $editCompany ? 'Edit Company' : 'Add New Company'; ?></h2><form method="POST" action="admin-company.php"><input type="hidden" name="action" value="<?php echo $editCompany ? 'update_company' : 'add_company'; ?>"><?php if ($editCompany): ?><input type="hidden" name="company_id" value="<?php echo e($editCompany['company_id']); ?>"><?php endif; ?><div class="form-row"><div class="form-group"><label>Company Name <span>*</span></label><input type="text" name="company_name" value="<?php echo e($editCompany['name'] ?? ''); ?>" placeholder="Enter company name" required /></div><div class="form-group"><label>Country</label><select name="country"><option>Select country</option><option>Egypt</option><option>UK</option><option>USA</option><option>Germany</option></select></div></div><div class="form-row"><div class="form-group"><label>Description</label><textarea placeholder="Description is optional in this academic version."></textarea></div><div class="form-group"><label>Company Display</label><div class="upload-box"><div>AH</div><p>Company initials are generated from the name.</p><small>Current version stores company name only</small></div></div></div><div class="form-actions"><a href="admin-company.php" class="cancel-btn" style="text-decoration:none;text-align:center">Cancel</a><button type="submit" class="save-btn"><?php echo $editCompany ? 'Update Company' : 'Save Company'; ?></button></div></form></section>
    </main></div>
  <footer><div class="footer-orange"><div class="footer-logo"><div class="footer-circle">Al-Habib</div><div><h3>Al-Habib</h3><p>Pharmacy</p></div></div><div class="footer-info">02 239 077 51 - 011 04 399 658</div><div class="footer-info">Saturday - Thursday : 9 AM - 11 PM<br>Friday : 2 PM - 11 PM</div></div><div class="footer-blue"><p>&copy; 2024 Al-Habib Pharmacy. All rights reserved.</p><div><a href="home.php">Terms & Conditions</a><span>|</span><a href="contact.php">Privacy Policy</a></div></div></footer>
<?php echo shared_js_scripts("admin"); ?>
</body>
</html>
