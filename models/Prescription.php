<?php

class Prescription
{
    private $db;
    private $prescriptionId;
    private $customerId;
    private $orderId;
    private $filePath;
    private $issueDate;
    private $status;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function setPrescriptionId($prescriptionId) { $this->prescriptionId = $prescriptionId; }
    public function setCustomerId($customerId) { $this->customerId = $customerId; }
    public function setOrderId($orderId) { $this->orderId = $orderId; }
    public function setFilePath($filePath) { $this->filePath = $filePath; }
    public function setIssueDate($issueDate) { $this->issueDate = $issueDate; }
    public function setStatus($status) { $this->status = $status; }

    public function getAllPrescriptions()
    {
        $sql = "SELECT p.*, u.name AS customer_name
                FROM prescriptions p
                LEFT JOIN customers c ON p.customer_id = c.customer_id
                LEFT JOIN users u ON c.user_id = u.user_id
                ORDER BY p.prescription_id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPrescriptionById($prescriptionId)
    {
        $sql = "SELECT * FROM prescriptions WHERE prescription_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prescriptionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addPrescription()
    {
        $sql = "INSERT INTO prescriptions (customer_id, order_id, file_path, issue_date, status)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $this->customerId,
            $this->orderId,
            $this->filePath,
            $this->issueDate,
            $this->status
        ]);
    }

    public function updatePrescriptionStatus()
    {
        $sql = "UPDATE prescriptions SET status = ? WHERE prescription_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $this->status,
            $this->prescriptionId
        ]);
    }

    public function deletePrescription($prescriptionId)
    {
        $sql = "DELETE FROM prescriptions WHERE prescription_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$prescriptionId]);
    }
}

?>
