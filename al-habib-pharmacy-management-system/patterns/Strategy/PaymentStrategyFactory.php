<?php

require_once "CashOnDeliveryPayment.php";
require_once "InstapayPayment.php";
require_once "VodafoneCashPayment.php";

class PaymentStrategyFactory
{
    public static function createPaymentStrategy($paymentMethod)
    {
        if ($paymentMethod === "instapay") {
            return new InstapayPayment();
        }

        if ($paymentMethod === "vodafone_cash") {
            return new VodafoneCashPayment();
        }

        return new CashOnDeliveryPayment();
    }
}

?>
