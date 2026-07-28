<?php

require_once "PaymentStrategy.php";

class CashOnDeliveryPayment implements PaymentStrategy
{
    public function pay($amount)
    {
        return [
            "method" => "Cash on Delivery",
            "status" => "Pending",
            "message" => "Cash on Delivery selected. The customer will pay EGP " . number_format($amount, 2) . " when the order is delivered."
        ];
    }
}

?>