<?php

require_once "PaymentStrategy.php";

class VodafoneCashPayment implements PaymentStrategy
{
    public function pay($amount)
    {
        return [
            "method" => "Vodafone Cash",
            "status" => "Pending",
            "message" => "Vodafone Cash selected. Transfer EGP " . number_format($amount, 2) . " to 01123456789."
        ];
    }
}

?>
