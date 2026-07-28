<?php

class AdminOrderController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAllOrders()
    {
        $sql = "
            SELECT
                o.order_id,
                o.order_date,
                o.status,
                o.total,
                u.name AS customer_name
            FROM orders o
            INNER JOIN customers c ON o.customer_id = c.customer_id
            INNER JOIN users u ON c.user_id = u.user_id
            ORDER BY o.order_id DESC
        ";
        return $this->fetchAll($sql);
    }

    public function getOrderStats()
    {
        $sql = "SELECT status, COUNT(*) AS total FROM orders GROUP BY status";
        $rows = $this->fetchAll($sql);

        $stats = [
            "total_orders" => 0,
            "pending"      => 0,
            "processing"   => 0,
            "delivered"    => 0
        ];

        foreach ($rows as $row) {
            $status = strtolower($row["status"]);
            $count  = $row["total"];
            $stats["total_orders"] += $count;
            if ($status === "pending")    $stats["pending"] = $count;
            elseif ($status === "processing") $stats["processing"] = $count;
            elseif ($status === "delivered")  $stats["delivered"] = $count;
        }

        return $stats;
    }

    public function updateOrderStatus($orderId, $status)
    {
        $allowed = ["Pending", "Processing", "Shipped", "Delivered", "Cancelled"];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $sql = "UPDATE orders SET status = :status WHERE order_id = :order_id";

        if ($this->db instanceof PDO) {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([":status" => $status, ":order_id" => $orderId]);
        }

        return $this->db->update($sql, [":status" => $status, ":order_id" => $orderId]);
    }

    private function fetchAll($sql, $params = [])
    {
        if ($this->db instanceof PDO) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $this->db->select($sql, $params);
    }
}

?>
