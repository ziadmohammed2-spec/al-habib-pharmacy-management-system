<?php

class OrderItem
{
    private $quantity;
    private $price;
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getSubtotal()
    {
        return $this->quantity * $this->price;
    }
}

?>