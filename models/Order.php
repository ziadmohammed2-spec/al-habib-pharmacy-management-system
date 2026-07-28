<?php

class Order
{
    private $orderId;
    private $orderDate;
    private $status;
    private $total;
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    public function setOrderDate($orderDate)
    {
        $this->orderDate = $orderDate;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function setTotal($total)
    {
        $this->total = $total;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function getOrderDate()
    {
        return $this->orderDate;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function placeOrder()
    {
        echo "Order placed successfully";
    }

    public function updateStatus($newStatus)
    {
        $this->status = $newStatus;
        echo "Order status updated successfully";
    }

    public function calculateTotal($items)
    {
        $this->total = 0;

        foreach ($items as $item) {
            $this->total += $item;
        }

        return $this->total;
    }

    public function getOrderDetails()
    {
        echo "Order ID: " . $this->orderId . "<br>";
        echo "Order Date: " . $this->orderDate . "<br>";
        echo "Status: " . $this->status . "<br>";
        echo "Total: EGP " . $this->total . "<br>";
    }
}

?>