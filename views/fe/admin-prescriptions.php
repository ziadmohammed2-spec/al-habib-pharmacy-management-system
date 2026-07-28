<?php

require_once __DIR__ . "/_app.php";
require_once __DIR__ . "/../../controllers/AdminPrescriptionController.php";

require_admin();

$adminPrescriptionController = new AdminPrescriptionController(app_db());
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $prescriptionId = (int) ($_POST["prescription_id"] ?? 0);
    $action = $_POST["action"] ?? "";

    if ($action === "approve") $ok = $adminPrescriptionController->approve($prescriptionId);
    elseif ($action === "reject") $ok = $adminPrescriptionController->reject($prescriptionId);
    elseif ($action === "pending") $ok = $adminPrescriptionController->pending($prescriptionId);
    elseif ($action === "delete") $ok = $adminPrescriptionController->delete($prescriptionId);
    else $ok = false;

    $message = $ok ? "Prescription updated." : "Unable to update prescription.";
}

$prescriptions = $adminPrescriptionController->index();
$total = count($prescriptions);
$approved = 0;
$rejected = 0;
$pending = 0;

foreach ($prescriptions as $p) {
    if ($p["status"] === "Approved") $approved++;
    elseif ($p["status"] === "Rejected") $rejected++;
    else $pending++;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Prescription Management</title>
<?php echo shared_css_links("admin"); ?>
  <link rel="stylesheet" href="../../assets/css/pages/admin-prescriptions.css?v=900">
</head>
<body>
<?php echo admin_header("prescriptions"); ?>

<div class="layout">
  <?php echo admin_sidebar("prescriptions"); ?>

  <main class="content">
    <div class="page-title"><h1>Prescription Management</h1><p>Dashboard &gt; Manage Prescriptions</p></div>
    <?php if ($message !== ""): ?><div class="alert-success"><?php echo e($message); ?></div><?php endif; ?>

    <section class="stats-grid">
      <div class="stat-card"><div><p>Total Prescriptions</p><h3><?php echo $total; ?></h3></div></div>
      <div class="stat-card"><div><p>Pending Review</p><h3 class="orange-text"><?php echo $pending; ?></h3></div></div>
      <div class="stat-card"><div><p>Approved</p><h3 class="green-text"><?php echo $approved; ?></h3></div></div>
      <div class="stat-card"><div><p>Rejected</p><h3 class="red-text"><?php echo $rejected; ?></h3></div></div>
    </section>

    <section class="table-card">
      <table>
        <thead><tr><th>Customer</th><th>Prescription</th><th>Upload Date</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (empty($prescriptions)): ?><tr><td colspan="5">No prescriptions found.</td></tr><?php endif; ?>
        <?php foreach ($prescriptions as $prescription): ?>
          <tr>
            <td><?php echo e($prescription["customer_name"] ?? ("Customer #" . $prescription["customer_id"])); ?></td>
            <td><div class="prescription-file"><div class="file-thumb"></div><div><p><?php echo e($prescription["file_path"]); ?></p><small>Uploaded file</small></div></div></td>
            <td><?php echo e($prescription["issue_date"]); ?></td>
            <td><span class="badge <?php echo e(strtolower($prescription["status"])); ?>"><?php echo e($prescription["status"]); ?></span></td>
            <td class="actions">
              <a class="view" href="<?php echo e($prescription["file_path"]); ?>" target="_blank" title="View">View</a>
              <form method="POST" action="admin-prescriptions.php" style="display:inline;">
                <input type="hidden" name="prescription_id" value="<?php echo (int) $prescription["prescription_id"]; ?>">
                <button class="approve" name="action" value="approve" type="submit">Approve</button>
                <button class="reject" name="action" value="reject" type="submit">Reject</button>
                <button name="action" value="pending" type="submit">Pending</button>
                <button name="action" value="delete" type="submit" onclick="return confirm('Delete this prescription?');">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <div class="table-footer"><p>Showing <?php echo $total; ?> prescriptions from database</p></div>
    </section>
  </main>
</div>

<footer>
  <div class="footer-orange"><div class="footer-logo"><div class="footer-circle">Al-Habib</div><div><h3>Al-Habib</h3><p>Pharmacy</p></div></div><div class="footer-info">02 239 077 51 - 011 04 399 658</div><div class="footer-info">Saturday - Thursday : 9 AM - 11 PM<br>Friday : 2 PM - 11 PM</div></div>
  <div class="footer-blue"><p>&copy; 2024 Al-Habib Pharmacy. All rights reserved.</p></div>
</footer>
<?php echo shared_js_scripts("admin"); ?>
</body>
</html>
