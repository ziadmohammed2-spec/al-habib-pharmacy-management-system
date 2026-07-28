<?php

require_once __DIR__ . "/../database/MySQL.php";

class PaymentController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createPayment($orderId, $method, $amount)
    {
        if (!$this->isAllowedMethod($method)) {
            return false;
        }

        $status = $this->processPaymentStatus($method);

        $sql = "INSERT INTO payments (order_id, method, amount, status)
                VALUES (:order_id, :method, :amount, :status)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ":order_id" => $orderId,
            ":method" => $method,
            ":amount" => $amount,
            ":status" => $status
        ]);
    }

    public function processPaymentStatus($method)
    {
        return "Pending";
    }

    public function isAllowedMethod($method)
    {
        return in_array($method, ["cash_on_delivery", "instapay", "vodafone_cash"], true);
    }

    public function getPaymentMethodLabel($method)
    {
        $labels = [
            "cash_on_delivery" => "Cash on Delivery",
            "instapay" => "Instapay",
            "vodafone_cash" => "Vodafone Cash",
        ];

        return $labels[$method] ?? $method;
    }

    public function getPaymentByOrderId($orderId)
    {
        $sql = "SELECT * FROM payments WHERE order_id = :order_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ":order_id" => $orderId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePaymentStatus($paymentId, $status)
    {
        $sql = "UPDATE payments
                SET status = :status
                WHERE payment_id = :payment_id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ":status" => $status,
            ":payment_id" => $paymentId
        ]);
    }
}

?>
