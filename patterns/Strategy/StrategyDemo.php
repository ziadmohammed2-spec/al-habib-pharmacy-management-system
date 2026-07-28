<?php

require_once "PaymentContext.php";
require_once "PaymentStrategyFactory.php";

$amount = 450.00;
$paymentMethod = "instapay";
$strategy = PaymentStrategyFactory::createPaymentStrategy($paymentMethod);
$paymentContext = new PaymentContext($strategy);
$result = $paymentContext->executePayment($amount);

echo "<h2>Strategy Pattern Demo</h2>";
echo "Payment Method: " . $result["method"] . "<br>";
echo "Payment Status: " . $result["status"] . "<br>";
echo "Message: " . $result["message"] . "<br>";

?>
