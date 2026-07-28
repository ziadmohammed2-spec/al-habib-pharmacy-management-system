<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../database/MySQL.php";

function app_db()
{
    static $db = null;

    if ($db === null) {
        $dbObject = new MySQL();
        $db = $dbObject->connectToDb();
        ensure_product_schema($db);
    }

    return $db;
}

function ensure_product_schema($db)
{
    static $checked = false;

    if ($checked || !($db instanceof PDO)) {
        return;
    }

    $checked = true;

    try {
        $columns = [
            "generic_name" => "ALTER TABLE products ADD COLUMN generic_name VARCHAR(150) NULL",
            "brand_name" => "ALTER TABLE products ADD COLUMN brand_name VARCHAR(150) NULL",
            "manufacturer_name" => "ALTER TABLE products ADD COLUMN manufacturer_name VARCHAR(150) NULL",
            "product_ndc" => "ALTER TABLE products ADD COLUMN product_ndc VARCHAR(50) NULL",
            "dosage_form" => "ALTER TABLE products ADD COLUMN dosage_form VARCHAR(100) NULL",
            "route" => "ALTER TABLE products ADD COLUMN route VARCHAR(100) NULL",
            "image_url" => "ALTER TABLE products ADD COLUMN image_url VARCHAR(255) NULL",
            "source" => "ALTER TABLE products ADD COLUMN source VARCHAR(50) DEFAULT 'local'",
            "is_active" => "ALTER TABLE products ADD COLUMN is_active TINYINT(1) DEFAULT 1",
        ];

        $stmt = $db->prepare("SHOW COLUMNS FROM products LIKE :column_name");

        foreach ($columns as $column => $alterSql) {
            $stmt->execute([":column_name" => $column]);
            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                $db->exec($alterSql);
            }
        }
    } catch (Exception $e) {
    }
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function redirect_to($path)
{
    header("Location: " . $path);
    exit;
}

function is_logged_in()
{
    return isset($_SESSION["user_id"]);
}

function is_admin_user()
{
    return ($_SESSION["role"] ?? "") === "admin";
}

function is_customer_user()
{
    return isset($_SESSION["customer_id"]) && ($_SESSION["role"] ?? "") === "customer";
}

function require_admin()
{
    if (!is_admin_user()) {
        redirect_to("login.php?error=access_denied");
    }
}

function require_customer()
{
    if (!is_customer_user()) {
        redirect_to("login.php?error=login_required");
    }
}

function current_customer_id()
{
    return (int) ($_SESSION["customer_id"] ?? 0);
}

function current_user_id()
{
    return (int) ($_SESSION["user_id"] ?? 0);
}

function current_cart_count()
{
    if (!is_customer_user()) {
        return 0;
    }

    try {
        $db = app_db();
        $sql = "SELECT COALESCE(SUM(ci.quantity), 0) AS item_count
                FROM carts c
                LEFT JOIN cart_items ci ON c.cart_id = ci.cart_id
                WHERE c.customer_id = :customer_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([":customer_id" => current_customer_id()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row["item_count"] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

function cart_nav_link($active = false)
{
    $count = current_cart_count();
    $classes = "cart-nav-link" . ($active ? " active" : "");
    $badge = $count > 0 ? '<span class="cart-badge">' . (int) $count . '</span>' : '';
    return '<a href="cart.php" class="' . $classes . '"><span class="cart-icon" aria-hidden="true">Cart</span> Cart' . $badge . '</a>';
}

function auth_link()
{
    if (is_logged_in()) {
        return '<a href="logout.php" class="logout-link">Logout</a>';
    }

    return '<a href="login.php" class="login-link">Login</a>';
}


function product_image_src($imageUrl, $seed = "")
{
    $imageUrl = trim((string) $imageUrl);

    $badDefaults = [
        "default-product.png",
        "default-medicine.svg",
        "generic-capsules.jpg",
        "capsule.png",
        "medicine.png"
    ];

    if ($imageUrl !== "" && strpos($imageUrl, "assets/") === 0) {
        $basename = basename($imageUrl);
        $absolute = __DIR__ . "/../../" . $imageUrl;

        if (file_exists($absolute) && !in_array($basename, $badDefaults, true)) {
            return "../../" . $imageUrl;
        }
    }

    $productDir = __DIR__ . "/../../assets/images/products";
    $fallbackImages = [];

    if (is_dir($productDir)) {
        foreach (glob($productDir . "/*.{jpg,jpeg,png,webp,avif}", GLOB_BRACE) as $file) {
            $base = basename($file);
            if (!in_array($base, $badDefaults, true)) {
                $fallbackImages[] = $base;
            }
        }
    }

    sort($fallbackImages);

    if (!empty($fallbackImages)) {
        $seedText = trim((string) $seed);
        if ($seedText === "") {
            $seedText = $imageUrl !== "" ? $imageUrl : "alhabib";
        }

        $index = abs(crc32($seedText)) % count($fallbackImages);
        return "../../assets/images/products/" . $fallbackImages[$index];
    }

    $default = __DIR__ . "/../../assets/images/placeholders/default-product.png";
    if (file_exists($default)) {
        return "../../assets/images/placeholders/default-product.png";
    }

    $legacyDefault = __DIR__ . "/../../assets/images/products/default-medicine.svg";
    if (file_exists($legacyDefault)) {
        return "../../assets/images/products/default-medicine.svg";
    }

    return "";
}

function product_placeholder($name)
{
    $parts = preg_split('/\s+/', trim((string) $name));
    return e($parts[0] ?? "Medicine");
}

function shared_css_links($type = "site")
{
    $links = [
        "../../assets/css/global.css",
        "../../assets/css/forms.css",
        "../../assets/css/responsive.css",
        "../../assets/css/navbar.css",
        "../../assets/css/site-unified.css",
    ];

    if ($type === "admin") {
        $links[] = "../../assets/css/admin.css";
    }

    $html = "";
    foreach ($links as $href) {
        $html .= '  <link rel="stylesheet" href="' . e($href) . '?v=900">' . "\n";
    }

    return $html;
}

function shared_js_scripts($page = "")
{
    $scripts = ['../../assets/js/main.js'];

    if (in_array($page, ["cart", "products", "product-details"], true)) {
        $scripts[] = '../../assets/js/cart.js';
    }

    if ($page === "checkout") {
        $scripts[] = '../../assets/js/checkout.js';
    }

    if ($page === "admin") {
        $scripts[] = '../../assets/js/admin.js';
    }

    $html = "";
    foreach ($scripts as $src) {
        $html .= '<script src="' . e($src) . '"></script>' . "\n";
    }

    return $html;
}

function admin_header($active = "")
{
    $links = [
        "dashboard" => ["href" => "admin-dashboard.php", "label" => "Dashboard"],
        "products" => ["href" => "admin-products.php", "label" => "Products"],
        "orders" => ["href" => "admin-orders.php", "label" => "Orders"],
        "messages" => ["href" => "admin-message.php", "label" => "Messages"],
    ];

    $html = '<header class="top-header admin-top-header">';
    $html .= '<a href="admin-dashboard.php" class="logo logo-section">';
    $html .= '<div class="logo-circle">Al-Habib<br>Pharmacy</div>';
    $html .= '<div class="logo-text"><h1>Al-Habib</h1><p>Pharmacy</p></div>';
    $html .= '</a><nav class="main-nav nav">';

    foreach ($links as $key => $link) {
        $class = $active === $key ? ' class="active"' : '';
        $html .= '<a href="' . e($link["href"]) . '"' . $class . '>' . e($link["label"]) . '</a>';
    }

    $html .= '<a href="home.php">Storefront</a><a href="logout.php" class="logout-link">Logout</a>';
    $html .= '</nav></header>';

    return $html;
}

function admin_sidebar($active = "")
{
    $items = [
        "dashboard" => ["href" => "admin-dashboard.php", "icon" => "DB", "label" => "Dashboard"],
        "products" => ["href" => "admin-products.php", "icon" => "PR", "label" => "Manage Products"],
        "orders" => ["href" => "admin-orders.php", "icon" => "OR", "label" => "Manage Orders"],
        "prescriptions" => ["href" => "admin-prescriptions.php", "icon" => "RX", "label" => "Manage Prescriptions"],
        "categories" => ["href" => "admin-categories.php", "icon" => "CT", "label" => "Manage Categories"],
        "companies" => ["href" => "admin-company.php", "icon" => "CO", "label" => "Manage Companies"],
        "messages" => ["href" => "admin-message.php", "icon" => "MS", "label" => "Customer Messages"],
        "logout" => ["href" => "logout.php", "icon" => "LO", "label" => "Logout"],
    ];

    $html = '<aside class="sidebar admin-sidebar">';

    foreach ($items as $key => $item) {
        $classes = "sidebar-link menu-item";
        if ($active === $key) {
            $classes .= " active";
        }

        $html .= '<a href="' . e($item["href"]) . '" class="' . $classes . '">';
        $html .= '<span class="menu-icon" aria-hidden="true">' . e($item["icon"]) . '</span>';
        $html .= '<span class="menu-text">' . e($item["label"]) . '</span>';
        $html .= '</a>';
    }

    $html .= '</aside>';

    return $html;
}

?>
