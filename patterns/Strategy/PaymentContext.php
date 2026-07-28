<?php

require_once "PaymentStrategy.php";

class PaymentContext
{
    private $paymentStrategy;

    public function __construct(PaymentStrategy $paymentStrategy)
    {
        $this->paymentStrategy = $paymentStrategy;
    }

    public function executePayment($amount)
    {
        return $this->paymentStrategy->pay($amount);
    }
}

?>