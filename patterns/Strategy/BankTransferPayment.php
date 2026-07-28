<?php

require_once "PaymentStrategy.php";

class BankTransferPayment implements PaymentStrategy
{
    public function pay($amount)
    {
        return [
            "method" => "Bank Transfer",
            "status" => "Pending",
            "message" => "Bank Transfer selected. The customer should transfer EGP " . number_format($amount, 2) . " to the pharmacy bank account."
        ];
    }
}

?>