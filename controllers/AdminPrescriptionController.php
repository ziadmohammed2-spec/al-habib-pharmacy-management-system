<?php

require_once __DIR__ . "/PrescriptionController.php";

class AdminPrescriptionController extends PrescriptionController
{
    public function approve($prescriptionId)
    {
        return $this->updateStatus($prescriptionId, "Approved");
    }

    public function reject($prescriptionId)
    {
        return $this->updateStatus($prescriptionId, "Rejected");
    }

    public function pending($prescriptionId)
    {
        return $this->updateStatus($prescriptionId, "Pending");
    }
}

?>