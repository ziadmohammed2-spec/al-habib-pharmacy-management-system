<?php

class AdminMessageController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function index($search = "", $status = "")
    {
        $sql = "SELECT cm.*, u.name AS customer_name, u.email AS customer_email
                FROM contact_messages cm
                LEFT JOIN customers c ON cm.customer_id = c.customer_id
                LEFT JOIN users u ON c.user_id = u.user_id
                WHERE 1=1";
        $params = [];

        if (trim($search) !== "") {
            $sql .= " AND (cm.subject LIKE :search OR cm.message LIKE :search OR u.name LIKE :search OR u.email LIKE :search)";
            $params[":search"] = "%" . trim($search) . "%";
        }

        if (trim($status) !== "") {
            $sql .= " AND cm.status = :status";
            $params[":status"] = trim($status);
        }

        $sql .= " ORDER BY cm.message_id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function show($messageId)
    {
        $sql = "SELECT cm.*, u.name AS customer_name, u.email AS customer_email
                FROM contact_messages cm
                LEFT JOIN customers c ON cm.customer_id = c.customer_id
                LEFT JOIN users u ON c.user_id = u.user_id
                WHERE cm.message_id = :message_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([":message_id" => $messageId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function markAsRead($messageId)
    {
        return $this->updateStatus($messageId, "Read");
    }

    public function markAsReplied($messageId)
    {
        return $this->updateStatus($messageId, "Replied");
    }

    public function delete($messageId)
    {
        $sql = "DELETE FROM contact_messages WHERE message_id = :message_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([":message_id" => $messageId]);
    }

    private function updateStatus($messageId, $status)
    {
        $sql = "UPDATE contact_messages SET status = :status WHERE message_id = :message_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ":status" => $status,
            ":message_id" => $messageId
        ]);
    }
}

?>
