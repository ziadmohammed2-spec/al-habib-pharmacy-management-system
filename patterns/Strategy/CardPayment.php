<?php

require_once "PaymentStrategy.php";

class CardPayment implements PaymentStrategy
{
    public function pay($amount)
    {
        return [
            "method" => "Credit / Debit Card",
            "status" => "Paid",
            "message" => "Card payment processed successfully. Amount paid: EGP " . number_format($amount, 2) . "."
        ];
    }
}

?>