<?php

require_once "PaymentStrategy.php";

class InstapayPayment implements PaymentStrategy
{
    public function pay($amount)
    {
        return [
            "method" => "Instapay",
            "status" => "Pending",
            "message" => "Instapay selected. Transfer EGP " . number_format($amount, 2) . " to 01012345678."
        ];
    }
}

?>
