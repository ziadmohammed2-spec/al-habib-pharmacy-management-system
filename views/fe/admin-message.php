<?php

require_once __DIR__ . "/_app.php";
require_once __DIR__ . "/../../controllers/AdminMessageController.php";

require_admin();

$controller = new AdminMessageController(app_db());
$message = "";
$messageType = "success";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int) ($_POST["message_id"] ?? 0);
    $action = $_POST["action"] ?? "";

    if ($action === "read") {
        $ok = $controller->markAsRead($id);
        $message = $ok ? "Message marked as read." : "Unable to update message.";
    } elseif ($action === "replied") {
        $ok = $controller->markAsReplied($id);
        $message = $ok ? "Message marked as replied." : "Unable to update message.";
    } elseif ($action === "delete") {
        $ok = $controller->delete($id);
        $message = $ok ? "Message deleted." : "Unable to delete message.";
    } else {
        $ok = false;
        $message = "Unknown message action.";
    }

    $messageType = $ok ? "success" : "error";
}

$search = trim($_GET["search"] ?? "");
$status = trim($_GET["status"] ?? "");
$messages = $controller->index($search, $status);
$selected = isset($_GET["view"]) ? $controller->show((int) $_GET["view"]) : null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Messages</title>

  <?php echo shared_css_links("admin"); ?>
  <link rel="stylesheet" href="../../assets/css/pages/admin-message.css?v=1000">
</head>

<body class="admin-page admin-message-page">
<div class="page-wrapper admin-page-wrapper">

  <?php echo admin_header("messages"); ?>

  <div class="layout admin-layout">
    <?php echo admin_sidebar("messages"); ?>

    <main class="content admin-content">
      <div class="page-title">
        <h1>Customer Messages</h1>
        <p>Dashboard &gt; Customer Messages</p>
      </div>

      <?php if ($message !== ""): ?>
        <div class="<?php echo $messageType === "success" ? "alert-success" : "alert-error"; ?>">
          <?php echo e($message); ?>
        </div>
      <?php endif; ?>

      <section class="panel card message-panel">
        <form class="filters message-filters" method="GET" action="admin-message.php">
          <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search messages">

          <select name="status">
            <option value="">All Status</option>
            <?php foreach (["Unread", "Read", "Replied"] as $statusOption): ?>
              <option value="<?php echo e($statusOption); ?>" <?php echo $status === $statusOption ? "selected" : ""; ?>>
                <?php echo e($statusOption); ?>
              </option>
            <?php endforeach; ?>
          </select>

          <button class="btn add-btn" type="submit">Search</button>
          <a class="view-link reset-link" href="admin-message.php">Reset</a>
        </form>

        <div class="table-wrapper">
          <table class="table message-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>

            <tbody>
              <?php if (empty($messages)): ?>
                <tr>
                  <td colspan="5">No messages found.</td>
                </tr>
              <?php endif; ?>

              <?php foreach ($messages as $m): ?>
                <tr>
                  <td><?php echo (int) $m["message_id"]; ?></td>
                  <td><?php echo e($m["customer_name"] ?? "Guest"); ?></td>
                  <td><?php echo e($m["subject"]); ?></td>
                  <td>
                    <span class="status badge <?php echo e(strtolower($m["status"])); ?>">
                      <?php echo e($m["status"]); ?>
                    </span>
                  </td>
                  <td class="actions">
                    <a class="btn view-btn" href="admin-message.php?view=<?php echo (int) $m["message_id"]; ?>">View</a>

                    <form method="POST" action="admin-message.php" class="inline-form">
                      <input type="hidden" name="message_id" value="<?php echo (int) $m["message_id"]; ?>">
                      <button class="btn read-btn" name="action" value="read" type="submit">Read</button>
                      <button class="btn replied-btn" name="action" value="replied" type="submit">Replied</button>
                      <button class="btn delete-btn" name="action" value="delete" type="submit" data-confirm="Delete this message?">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <?php if ($selected): ?>
        <section class="panel card message-detail">
          <h2><?php echo e($selected["subject"]); ?></h2>

          <p>
            <strong>Customer:</strong>
            <?php echo e($selected["customer_name"] ?? "Guest"); ?>
            <?php echo e($selected["customer_email"] ?? ""); ?>
          </p>

          <p>
            <strong>Status:</strong>
            <?php echo e($selected["status"]); ?>
          </p>

          <pre><?php echo e($selected["message"]); ?></pre>
        </section>
      <?php endif; ?>
    </main>
  </div>
</div>

<?php echo shared_js_scripts("admin"); ?>
</body>
</html>