<?php

require_once __DIR__ . "/database/MySQL.php";
require_once __DIR__ . "/controllers/OpenFdaImportController.php";

$dbObject = new MySQL();
$db = $dbObject->connectToDb();

$importController = new OpenFdaImportController($db);

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["import"])) {

    $limit = isset($_POST["limit"]) ? (int) $_POST["limit"] : 100;
    $skip = isset($_POST["skip"]) ? (int) $_POST["skip"] : 0;

    if ($limit < 1) {
        $limit = 20;
    }

    if ($limit > 100) {
        $limit = 100;
    }

    if ($skip < 0) {
        $skip = 0;
    }

    $inserted = $importController->importMedicines($limit, $skip);

    if ($inserted > 0) {
        $message = $inserted . " medicines imported successfully from openFDA.";
    } else {
        $message = "No new medicines imported. Try increasing Skip value.";
    }
}

$productsStmt = $db->prepare("
    SELECT 
        product_id,
        name,
        generic_name,
        manufacturer_name,
        product_ndc,
        dosage_form,
        route,
        price,
        stock,
        source
    FROM products
    ORDER BY product_id DESC
    LIMIT 50
");

$productsStmt->execute();
$products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);

$countStmt = $db->prepare("SELECT COUNT(*) AS total FROM products");
$countStmt->execute();
$totalProducts = $countStmt->fetch(PDO::FETCH_ASSOC)["total"];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import Medicines from openFDA</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            padding: 30px;
            color: #102744;
        }

        h1 {
            color: #064181;
        }

        .box {
            background: white;
            border: 1px solid #dce5ef;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 12px;
            margin-bottom: 6px;
        }

        input {
            padding: 10px;
            width: 200px;
            border: 1px solid #ccd8e4;
            border-radius: 6px;
            margin-right: 10px;
        }

        button {
            background: #ff6418;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 15px;
        }

        .message {
            margin-top: 15px;
            padding: 12px;
            background: #e8f6ed;
            color: #0b7a3b;
            border: 1px solid #bde5c8;
            border-radius: 6px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            border: 1px solid #dce5ef;
            padding: 9px;
            font-size: 13px;
            text-align: left;
        }

        th {
            background: #064181;
            color: white;
        }

        .back {
            display: inline-block;
            margin-bottom: 15px;
            color: #064181;
            text-decoration: none;
            font-weight: bold;
        }

        .note {
            color: #697b91;
            font-size: 14px;
        }
    </style>
</head>
<body>

<a class="back" href="views/fe/admin-products.php">Back to Admin Products</a>

<h1>Import Medicines from openFDA</h1>

<div class="box">
    <p>
        Total products in database:
        <strong><?php echo $totalProducts; ?></strong>
    </p>

    <p class="note">
        Limit أقصى قيمة له 100 في الطلب الواحد.  
        Skip معناها يبدأ من رقم كام في نتائج openFDA.  
        مثال: أول مرة skip = 0، تاني مرة skip = 100، تالت مرة skip = 200.
    </p>

    <form method="POST">
        <label>Limit</label>
        <input type="number" name="limit" value="100" min="1" max="100">

        <label>Skip</label>
        <input type="number" name="skip" value="0" min="0">

        <br>

        <button type="submit" name="import">
            Import Medicines
        </button>
    </form>

    <?php if (!empty($message)): ?>
        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
</div>

<div class="box">
    <h2>Latest Products</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Generic Name</th>
                <th>Manufacturer</th>
                <th>NDC</th>
                <th>Dosage Form</th>
                <th>Route</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Source</th>
            </tr>
        </thead>

        <tbody>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="10">No products found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product["product_id"]; ?></td>
                        <td><?php echo htmlspecialchars($product["name"]); ?></td>
                        <td><?php echo htmlspecialchars($product["generic_name"] ?? ""); ?></td>
                        <td><?php echo htmlspecialchars($product["manufacturer_name"] ?? ""); ?></td>
                        <td><?php echo htmlspecialchars($product["product_ndc"] ?? ""); ?></td>
                        <td><?php echo htmlspecialchars($product["dosage_form"] ?? ""); ?></td>
                        <td><?php echo htmlspecialchars($product["route"] ?? ""); ?></td>
                        <td>EGP <?php echo number_format($product["price"], 2); ?></td>
                        <td><?php echo $product["stock"]; ?></td>
                        <td><?php echo htmlspecialchars($product["source"] ?? "local"); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
