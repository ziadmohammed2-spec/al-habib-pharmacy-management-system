<?php

class OrderController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getCustomerOrders($customerId)
    {
        $sql = "
            SELECT
                o.order_id,
                o.order_date,
                o.status,
                o.total,
                COALESCE(SUM(oi.quantity), 0) AS item_count,
                MIN(p.image_url) AS first_image_url,
                GROUP_CONCAT(DISTINCT p.name ORDER BY p.name SEPARATOR ', ') AS product_names
            FROM orders o
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.product_id
            WHERE o.customer_id = :customer_id
            GROUP BY o.order_id, o.order_date, o.status, o.total
            ORDER BY o.order_id DESC
        ";
        return $this->fetchAll($sql, [":customer_id" => $customerId]);
    }

    public function getOrderSummary($customerId)
    {
        $sql = "
            SELECT status, COUNT(*) AS total
            FROM orders
            WHERE customer_id = :customer_id
            GROUP BY status
        ";
        $rows = $this->fetchAll($sql, [":customer_id" => $customerId]);

        $summary = [
            "total_orders" => 0,
            "delivered"    => 0,
            "processing"   => 0,
            "shipped"      => 0,
            "cancelled"    => 0
        ];

        foreach ($rows as $row) {
            $status = strtolower($row["status"]);
            $count  = $row["total"];
            $summary["total_orders"] += $count;
            if (isset($summary[$status])) {
                $summary[$status] = $count;
            }
        }

        return $summary;
    }

    public function getOrderById($orderId, $customerId)
    {
        $sql = "
            SELECT
                o.order_id,
                o.customer_id,
                o.address_id,
                o.order_date,
                o.status,
                o.total,
                COALESCE(SUM(oi.quantity), 0) AS item_count,
                COALESCE(
                    NULLIF(CONCAT_WS(', ', a.street, a.building_no, a.city, a.details), ''),
                    'No delivery address'
                ) AS delivery_address
            FROM orders o
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            LEFT JOIN addresses a ON o.address_id = a.address_id
            WHERE o.order_id = :order_id
              AND o.customer_id = :customer_id
            GROUP BY
                o.order_id, o.customer_id, o.address_id, o.order_date, o.status, o.total,
                a.street, a.building_no, a.city, a.details
            LIMIT 1
        ";

        $rows = $this->fetchAll($sql, [":order_id" => $orderId, ":customer_id" => $customerId]);
        return $rows ? $rows[0] : null;
    }

    public function getOrderItems($orderId)
    {
        $sql = "
            SELECT
                oi.quantity,
                oi.price,
                p.name AS product_name,
                p.image_url
            FROM order_items oi
            INNER JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = :order_id
        ";
        return $this->fetchAll($sql, [":order_id" => $orderId]);
    }

    public function getRecentOrders($customerId)
    {
        $sql = "
            SELECT
                o.order_id,
                o.order_date,
                o.status,
                o.total,
                COALESCE(SUM(oi.quantity), 0) AS item_count,
                MIN(p.image_url) AS first_image_url,
                GROUP_CONCAT(DISTINCT p.name ORDER BY p.name SEPARATOR ', ') AS product_names
            FROM orders o
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.product_id
            WHERE o.customer_id = :customer_id
            GROUP BY o.order_id, o.order_date, o.status, o.total
            ORDER BY o.order_id DESC
            LIMIT 5
        ";
        return $this->fetchAll($sql, [":customer_id" => $customerId]);
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
