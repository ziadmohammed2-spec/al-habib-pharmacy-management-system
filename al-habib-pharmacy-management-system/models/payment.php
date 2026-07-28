<?php

class Payment
{
    private $paymentId;
    private $method;
    private $amount;
    private $status;
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getPaymentId()
    {
        return $this->paymentId;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function processPayment()
    {
        if ($this->method == "Cash on Delivery") {
            $this->status = "Pending";
            echo "Payment will be collected on delivery.<br>";
        } elseif ($this->method == "Online Payment") {
            $this->status = "Paid";
            echo "Online payment processed successfully.<br>";
        } else {
            $this->status = "Failed";
            echo "Invalid payment method.<br>";
        }

        echo "Payment Amount: EGP " . $this->amount . "<br>";
        echo "Payment Status: " . $this->status . "<br>";
    }
}

?>