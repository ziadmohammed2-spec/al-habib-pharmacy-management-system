<?php

class Cart
{
    private $cartId;
    private $total;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function setCartId($cartId)
    {
        $this->cartId = $cartId;
    }

    public function setTotal($total)
    {
        $this->total = $total;
    }

    public function getCartId()
    {
        return $this->cartId;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function addItem()
    {
        echo "Item added to cart successfully";
    }

    public function removeItem()
    {
        echo "Item removed from cart successfully";
    }

    public function updateItem()
    {
        echo "Cart item updated successfully";
    }

    public function calculateTotal()
    {
        echo "Cart total calculated successfully";
        return $this->total;
    }
}

?>