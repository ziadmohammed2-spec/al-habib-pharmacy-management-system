<?php
require_once __DIR__ . "/_app.php";

$db = app_db();
$message = "";
$messageType = "success";

$customerId = null;
$currentName = "";
$currentEmail = "";

if (is_logged_in()) {
    $currentName = $_SESSION["name"] ?? "";
    $currentEmail = $_SESSION["email"] ?? "";

    if (is_customer_user()) {
        $customerId = current_customer_id();
    }
}

function contact_image_path()
{
    $candidates = [
        "../../assets/images/contact-hero.jpg",
        "../../assets/images/contact.jpg",
        "../../assets/images/pharmacy-support.jpg",
        "../../assets/images/support.jpg",
        "../../assets/images/doctor.jpg"
    ];

    foreach ($candidates as $path) {
        if (file_exists(__DIR__ . "/" . $path)) {
            return $path;
        }
    }

    $patterns = [
        __DIR__ . "/../../assets/images/*contact*.*",
        __DIR__ . "/../../assets/images/*doctor*.*",
        __DIR__ . "/../../assets/images/*pharmacy*.*"
    ];

    foreach ($patterns as $pattern) {
        $files = glob($pattern);
        if (!empty($files)) {
            return "../../assets/images/" . basename($files[0]);
        }
    }

    return "";
}

$heroImage = contact_image_path();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $subject = trim($_POST["subject"] ?? "");
        $body = trim($_POST["message"] ?? "");

        if ($name === "" || $email === "" || $subject === "" || $body === "") {
            throw new Exception("Please fill in all required fields.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address.");
        }

        $storedMessage = "Name: " . $name . "\nEmail: " . $email . "\n\n" . $body;

        $stmt = $db->prepare("
            INSERT INTO contact_messages (customer_id, subject, message, status)
            VALUES (:customer_id, :subject, :message, 'Unread')
        ");

        $stmt->bindValue(":customer_id", $customerId, $customerId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(":subject", $subject);
        $stmt->bindValue(":message", $storedMessage);
        $stmt->execute();

        redirect_to("contact.php?success=1");
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = "error";
    }
}

if (($_GET["success"] ?? "") === "1") {
    $message = "Your message has been sent successfully.";
    $messageType = "success";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us - Al-Habib Pharmacy</title>
  <?php echo shared_css_links(); ?>
  <link rel="stylesheet" href="../../assets/css/pages/contact.css">
</head>

<body>
<div class="page-wrapper contact-page">

  <?php include __DIR__ . "/partials/customer-navbar.php"; ?>

  <main class="contact-main">

    <section class="contact-hero">
      <div class="contact-hero-text">
        <h1>Contact Us</h1>
        <p>We are here to help. Reach out to us for any questions, support, or feedback.</p>
      </div>

      <div class="contact-hero-image">
        <img src="../../assets/images/contact-doctor.jpg" alt="Pharmacy Support">
      </div>
    </section>

    <?php if (!empty($message)): ?>
      <div class="contact-alert <?php echo $messageType === 'success' ? 'success' : 'error'; ?>">
        <?php echo e($message); ?>
      </div>
    <?php endif; ?>

    <section class="contact-layout">

      <div class="contact-card message-card">
        <div class="card-heading">
          <span class="heading-icon">✉</span>
          <div>
            <h2>Send Us a Message</h2>
            <p>Fill out the form below and we will get back to you as soon as possible.</p>
          </div>
        </div>

        <form method="POST" action="contact.php" class="contact-form">

          <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" value="<?php echo e($_SESSION['name'] ?? ''); ?>" placeholder="Enter your full name">
          </div>

          <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" value="<?php echo e($_SESSION['email'] ?? ''); ?>" placeholder="Enter your email">
          </div>

          <div class="form-group">
            <label>Subject</label>
            <input type="text" name="subject" placeholder="Enter the subject" required>
          </div>

          <div class="form-group">
            <label>Message</label>
            <textarea name="message" placeholder="Type your message here..." required></textarea>
          </div>

          <button type="submit" class="send-btn">Send Message</button>

          <p class="safe-note">Your information is safe with us and will never be shared.</p>
        </form>
      </div>

      <aside class="contact-side">

        <div class="contact-card info-card">
          <div class="card-heading">
            <span class="heading-icon">☎</span>
            <div>
              <h2>Contact Information</h2>
              <p>Get in touch with us using the details below.</p>
            </div>
          </div>

          <div class="info-list">
            <div class="info-item">
              <span>☎</span>
              <div>
                <strong>Phone</strong>
                <p>02 239 077 51 | 011 04 399 658</p>
              </div>
            </div>

            <div class="info-item">
              <span>✉</span>
              <div>
                <strong>Email</strong>
                <p>info@alhabibpharmacy.com</p>
              </div>
            </div>

            <div class="info-item">
              <span>⌂</span>
              <div>
                <strong>Address</strong>
                <p>123-A, Katchi Pahari Road,<br>Saddar, Karachi - 74400</p>
              </div>
            </div>

            <div class="info-item">
              <span>⏱</span>
              <div>
                <strong>Working Hours</strong>
                <p>Mon - Sat: 9 AM - 11 PM<br>Sunday: 10 AM - 8 PM</p>
              </div>
            </div>
          </div>

          <div class="social-row">
            <strong>Connect With Us</strong>
            <a href="#">f</a>
            <a href="#">ig</a>
            <a href="#">wa</a>
          </div>
        </div>

        <div class="map-card">
          <div class="map-grid"></div>
          <div class="map-pin">●</div>
          <div class="map-label">
            <strong>Al-Habib Pharmacy</strong>
            <span>123-A, Katchi Pahari Road</span>
          </div>
        </div>

      </aside>
    </section>

    <section class="contact-features">
      <div class="feature-card">
        <span>🚚</span>
        <div>
          <strong>Fast & Reliable Delivery</strong>
          <p>On-time delivery at your doorstep.</p>
        </div>
      </div>

      <div class="feature-card">
        <span>🛡</span>
        <div>
          <strong>100% Genuine Medicines</strong>
          <p>Authentic products you can trust.</p>
        </div>
      </div>

      <div class="feature-card">
        <span>📄</span>
        <div>
          <strong>Upload Prescription</strong>
          <p>Easy upload and quick verification.</p>
        </div>
      </div>

      <div class="feature-card">
        <span>🎧</span>
        <div>
          <strong>24/7 Customer Support</strong>
          <p>We are here to help you anytime.</p>
        </div>
      </div>
    </section>

  </main>

  <?php include __DIR__ . "/partials/site-footer.php"; ?>

</div>

<?php echo shared_js_scripts(); ?>
</body>
</html>