<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php?error=access_denied");
    exit;
}

header("Location: admin-categories.php");
exit;
?>
