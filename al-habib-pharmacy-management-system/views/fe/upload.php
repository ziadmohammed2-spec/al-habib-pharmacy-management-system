<?php

require_once __DIR__ . "/_app.php";
require_customer();

$db = app_db();
$customerId = current_customer_id();
$message = "";
$messageType = "success";

$maxSize = 5 * 1024 * 1024;
$allowedExtensions = ["jpg", "jpeg", "png", "pdf"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        if (!isset($_FILES["prescription_file"])) {
            throw new Exception("Please select a prescription file.");
        }

        $file = $_FILES["prescription_file"];

        if ($file["error"] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed. Please try again.");
        }

        if ($file["size"] > $maxSize) {
            throw new Exception("File size must not exceed 5MB.");
        }

        $originalName = $file["name"];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new Exception("Only JPG, PNG, and PDF files are allowed.");
        }

        $uploadDir = __DIR__ . "/../../uploads/prescriptions";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $newFileName = "prescription_" . $customerId . "_" . time() . "." . $extension;
        $targetPath = $uploadDir . "/" . $newFileName;
        $dbPath = "uploads/prescriptions/" . $newFileName;

        if (!move_uploaded_file($file["tmp_name"], $targetPath)) {
            throw new Exception("Could not save the uploaded file.");
        }

        $columnsStmt = $db->query("SHOW COLUMNS FROM prescriptions");
        $columnsRows = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);
        $columns = array_column($columnsRows, "Field");

        $insertColumns = [];
        $insertValues = [];
        $params = [];

        if (in_array("customer_id", $columns, true)) {
            $insertColumns[] = "customer_id";
            $insertValues[] = ":customer_id";
            $params[":customer_id"] = $customerId;
        }

        if (in_array("order_id", $columns, true)) {
            $insertColumns[] = "order_id";
            $insertValues[] = "NULL";
        }

        if (in_array("file_path", $columns, true)) {
            $insertColumns[] = "file_path";
            $insertValues[] = ":file_path";
            $params[":file_path"] = $dbPath;
        }

        if (in_array("file_name", $columns, true)) {
            $insertColumns[] = "file_name";
            $insertValues[] = ":file_name";
            $params[":file_name"] = $originalName;
        }

        if (in_array("original_name", $columns, true)) {
            $insertColumns[] = "original_name";
            $insertValues[] = ":original_name";
            $params[":original_name"] = $originalName;
        }

        if (in_array("issue_date", $columns, true)) {
            $insertColumns[] = "issue_date";
            $insertValues[] = "NOW()";
        }

        if (in_array("upload_date", $columns, true)) {
            $insertColumns[] = "upload_date";
            $insertValues[] = "NOW()";
        }

        if (in_array("created_at", $columns, true)) {
            $insertColumns[] = "created_at";
            $insertValues[] = "NOW()";
        }

        if (in_array("status", $columns, true)) {
            $insertColumns[] = "status";
            $insertValues[] = ":status";
            $params[":status"] = "Pending";
        }

        if (empty($insertColumns)) {
            throw new Exception("Prescriptions table is not configured correctly.");
        }

        $sql = "INSERT INTO prescriptions (" . implode(", ", $insertColumns) . ") VALUES (" . implode(", ", $insertValues) . ")";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        redirect_to("upload.php?success=1");
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = "error";
    }
}

if (($_GET["success"] ?? "") === "1") {
    $message = "Prescription uploaded successfully. Our pharmacy team will review it soon.";
    $messageType = "success";
}

$bannerCandidates = [
    "prescription-banner.jpg",
    "upload-prescription.jpg",
    "contact-doctor.jpg",
    "doctor.jpg",
    "pharmacy-doctor.jpg"
];

$bannerImage = "";

foreach ($bannerCandidates as $candidate) {
    $serverPath = __DIR__ . "/../../assets/images/" . $candidate;
    if (file_exists($serverPath)) {
        $bannerImage = "../../assets/images/" . $candidate;
        break;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Upload Prescription - Al-Habib Pharmacy</title>
  <?php echo shared_css_links(); ?>
  <link rel="stylesheet" href="../../assets/css/pages/upload.css">
</head>

<body>
<div class="page-wrapper upload-page">

  <?php include __DIR__ . "/partials/customer-navbar.php"; ?>

  <main class="upload-main">

    <section class="upload-hero">
      <div class="upload-hero-text">
        <h1>Upload Prescription</h1>
        <p>Upload your prescription and our pharmacy team will review it with care.</p>
      </div>

      <div class="upload-hero-image">
        <?php if ($bannerImage !== ""): ?>
          <img src="<?php echo e($bannerImage); ?>" alt="Upload Prescription">
        <?php else: ?>
          <div class="upload-hero-placeholder">+</div>
        <?php endif; ?>
      </div>
    </section>

    <section class="upload-features top-features">
      <div class="upload-feature">
        <span>🚚</span>
        <div>
          <strong>Fast Delivery</strong>
          <p>Quick medicine delivery.</p>
        </div>
      </div>

      <div class="upload-feature">
        <span>🛡</span>
        <div>
          <strong>Secure Review</strong>
          <p>Your file is reviewed safely.</p>
        </div>
      </div>

      <div class="upload-feature">
        <span>📄</span>
        <div>
          <strong>Easy Prescription</strong>
          <p>Upload in simple steps.</p>
        </div>
      </div>

      <div class="upload-feature">
        <span>🎧</span>
        <div>
          <strong>24/7 Support</strong>
          <p>We are here to help.</p>
        </div>
      </div>
    </section>

    <?php if ($message !== ""): ?>
      <div class="upload-alert <?php echo $messageType === "success" ? "success" : "error"; ?>">
        <?php echo e($message); ?>
      </div>
    <?php endif; ?>

    <section class="upload-card">
      <div class="upload-card-heading">
        <h2>Upload Your Prescription</h2>
        <p>Please upload a clear image of your prescription.</p>
      </div>

      <form method="POST" action="upload.php" enctype="multipart/form-data" class="upload-form">
        <label class="drop-zone">
          <span class="cloud-icon">☁</span>
          <strong>Drag & Drop your prescription here</strong>
          <small>or</small>
          <span class="browse-btn">Browse File</span>
          <input type="file" name="prescription_file" accept=".jpg,.jpeg,.png,.pdf" required>
          <em>Accepted: JPG, PNG, PDF — Maximum size: 5MB</em>
        </label>

        <button type="submit" class="upload-btn">Upload Prescription</button>

        <p class="upload-note">
          🔒 Your prescription will be reviewed by our pharmacists and will only be used for verification.
        </p>
      </form>
    </section>

    <section class="upload-features bottom-features">
      <div class="upload-feature">
        <span>🚚</span>
        <div>
          <strong>Fast & Reliable Delivery</strong>
          <p>On-time delivery.</p>
        </div>
      </div>

      <div class="upload-feature">
        <span>🧡</span>
        <div>
          <strong>100% Genuine Medicines</strong>
          <p>Authentic pharmacy products.</p>
        </div>
      </div>

      <div class="upload-feature">
        <span>🛡</span>
        <div>
          <strong>Secure & Confidential</strong>
          <p>Your data is protected.</p>
        </div>
      </div>

      <div class="upload-feature">
        <span>🔄</span>
        <div>
          <strong>Easy Refund</strong>
          <p>Simple return process.</p>
        </div>
      </div>
    </section>

  </main>

  <?php include __DIR__ . "/partials/site-footer.php"; ?>

</div>

<?php echo shared_js_scripts(); ?>
</body>
</html>