<?php

require_once __DIR__ . "/../database/MySQL.php";
require_once __DIR__ . "/CartController.php";
require_once __DIR__ . "/AddressController.php";
require_once __DIR__ . "/PaymentController.php";

class CheckoutController
{
    private $db;
    private $cartController;
    private $addressController;
    private $paymentController;

    public function __construct($db)
    {
        $this->db = $db;
        $this->cartController = new CartController($this->db);
        $this->addressController = new AddressController($this->db);
        $this->paymentController = new PaymentController($this->db);
    }

    public function index($customerId)
    {
        return [
            "items" => $this->cartController->index($customerId),
            "summary" => $this->cartController->getCartSummary($customerId)
        ];
    }

    public function placeOrder($customerId, $data)
    {
        $customerId = (int) $customerId;
        if ($customerId <= 0) {
            return false;
        }

        $requiredFields = ["full_name", "phone", "address", "city", "payment_method"];
        foreach ($requiredFields as $field) {
            if (trim($data[$field] ?? "") === "") {
                return false;
            }
        }

        $paymentMethod = trim((string) ($data["payment_method"] ?? ""));
        if (!$this->paymentController->isAllowedMethod($paymentMethod)) {
            return false;
        }

        $cartItems = $this->cartController->index($customerId);
        $summary = $this->cartController->getCartSummary($customerId);

        if (empty($cartItems) || (float) $summary["total"] <= 0) {
            return false;
        }

        $userId = $this->getUserIdByCustomerId($customerId);

        if (!$userId) {
            return false;
        }

        foreach ($cartItems as $item) {
            if ((int) $item["quantity"] <= 0 || (int) $item["quantity"] > (int) $item["stock"]) {
                return false;
            }
        }

        try {
            $this->db->beginTransaction();

            if (!empty($data["phone"])) {
                $this->updateCustomerPhone($customerId, $data["phone"]);
            }

            $addressId = $this->addressController->store(
                $userId,
                $data["city"],
                $data["address"],
                $data["postal_code"] ?? null,
                $data["delivery_notes"] ?? null
            );

            if (!$addressId) {
                throw new Exception("Failed to save address.");
            }

            $orderId = $this->createOrder($customerId, $addressId, $summary["total"]);

            if (!$orderId) {
                throw new Exception("Failed to create order.");
            }

            foreach ($cartItems as $item) {
                if (!$this->createOrderItem(
                    $orderId,
                    $item["product_id"],
                    $item["quantity"],
                    $item["unit_price"]
                )) {
                    throw new Exception("Failed to create order item.");
                }

                if (!$this->decreaseProductStock($item["product_id"], $item["quantity"])) {
                    throw new Exception("Insufficient product stock.");
                }
            }

            if (!$this->paymentController->createPayment($orderId, $paymentMethod, $summary["total"])) {
                throw new Exception("Failed to create payment.");
            }

            $this->cartController->clearCart($customerId);

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    private function createOrder($customerId, $addressId, $total)
    {
        $sql = "INSERT INTO orders (customer_id, address_id, order_date, status, total)
                VALUES (:customer_id, :address_id, CURDATE(), 'Pending', :total)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ":customer_id" => $customerId,
            ":address_id" => $addressId,
            ":total" => $total
        ]);

        if ($result) {
            return (int) $this->db->lastInsertId();
        }

        return false;
    }

    private function createOrderItem($orderId, $productId, $quantity, $price)
    {
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (:order_id, :product_id, :quantity, :price)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ":order_id" => $orderId,
            ":product_id" => $productId,
            ":quantity" => $quantity,
            ":price" => $price
        ]);
    }

    private function decreaseProductStock($productId, $quantity)
    {
        $sql = "UPDATE products
                SET stock = stock - :quantity
                WHERE product_id = :product_id
                AND stock >= :quantity";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ":quantity" => $quantity,
            ":product_id" => $productId
        ]);

        return $stmt->rowCount() > 0;
    }

    private function getUserIdByCustomerId($customerId)
    {
        $sql = "SELECT user_id FROM customers WHERE customer_id = :customer_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ":customer_id" => $customerId
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row["user_id"] : null;
    }

    private function updateCustomerPhone($customerId, $phone)
    {
        $sql = "UPDATE customers
                SET phone = :phone
                WHERE customer_id = :customer_id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ":phone" => $phone,
            ":customer_id" => $customerId
        ]);
    }

}

?>
