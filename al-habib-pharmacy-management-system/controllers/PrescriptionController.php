<?php

require_once __DIR__ . "/../models/Prescription.php";

class PrescriptionController
{
    private $prescriptionModel;

    public function __construct($db)
    {
        $this->prescriptionModel = new Prescription($db);
    }

    public function index()
    {
        return $this->prescriptionModel->getAllPrescriptions();
    }

    public function show($prescriptionId)
    {
        return $this->prescriptionModel->getPrescriptionById($prescriptionId);
    }

    public function store($filePath, $issueDate, $status, $customerId = null, $orderId = null)
    {
        $customerId = (int) $customerId;
        $filePath = trim((string) $filePath);

        if ($customerId <= 0 || $filePath === "") {
            return false;
        }

        $this->prescriptionModel->setCustomerId($customerId);
        $this->prescriptionModel->setOrderId($orderId);
        $this->prescriptionModel->setFilePath($filePath);
        $this->prescriptionModel->setIssueDate($issueDate);
        $this->prescriptionModel->setStatus($status);

        return $this->prescriptionModel->addPrescription();
    }

    public function updateStatus($prescriptionId, $status)
    {
        $this->prescriptionModel->setPrescriptionId($prescriptionId);
        $this->prescriptionModel->setStatus($status);

        return $this->prescriptionModel->updatePrescriptionStatus();
    }

    public function delete($prescriptionId)
    {
        return $this->prescriptionModel->deletePrescription($prescriptionId);
    }
}

?>
