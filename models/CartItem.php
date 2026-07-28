<?php

class CartItem
{
    private $quantity;
    private $unitPrice;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    public function getSubtotal()
    {
        return $this->quantity * $this->unitPrice;
    }
}

?>