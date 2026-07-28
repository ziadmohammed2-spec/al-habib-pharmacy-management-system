<?php

class DashboardController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getDashboardStats()
    {
        return [
            "total_products"    => $this->countQuery("SELECT COUNT(*) FROM products"),
            "pending_orders"    => $this->countQuery("SELECT COUNT(*) FROM orders WHERE status = 'Pending'"),
            "new_prescriptions" => $this->countQuery("SELECT COUNT(*) FROM prescriptions WHERE status = 'Pending'"),
            "messages"          => $this->countQuery("SELECT COUNT(*) FROM contact_messages WHERE status = 'Unread'")
        ];
    }

    public function getRecentOrders()
    {
        $sql = "SELECT order_id, order_date, total, status FROM orders ORDER BY order_id DESC LIMIT 3";
        return $this->fetchAll($sql);
    }

    public function getLowStockProducts()
    {
        $sql = "SELECT p.name, p.stock, c.name AS category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id
                ORDER BY p.stock ASC
                LIMIT 3";
        return $this->fetchAll($sql);
    }

    private function countQuery($sql)
    {
        if ($this->db instanceof PDO) {
            return (int) $this->db->query($sql)->fetchColumn();
        }
        $rows = $this->db->select($sql);
        return $rows ? (int) array_values($rows[0])[0] : 0;
    }

    private function fetchAll($sql)
    {
        if ($this->db instanceof PDO) {
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        }
        return $this->db->select($sql);
    }
}

?>
