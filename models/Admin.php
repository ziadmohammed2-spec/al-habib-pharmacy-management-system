<?php

require_once __DIR__ . "/User.php";

class Admin extends User
{
    private $role;

    public function __construct($db)
    {
        parent::__construct($db);
        $this->role = "admin";
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function reviewPrescription($prescriptionId, $status)
    {
        $sql = "UPDATE prescriptions 
                SET status = :status 
                WHERE prescription_id = :prescription_id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ":status" => $status,
            ":prescription_id" => $prescriptionId
        ]);
    }

    public function addProduct($name, $price, $stock)
    {
        $sql = "INSERT INTO products (name, price, stock) 
                VALUES (:name, :price, :stock)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ":name" => $name,
            ":price" => $price,
            ":stock" => $stock
        ]);
    }

    public function updateProduct($productId, $name, $price, $stock)
    {
        $sql = "UPDATE products 
                SET name = :name, price = :price, stock = :stock 
                WHERE product_id = :product_id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ":name" => $name,
            ":price" => $price,
            ":stock" => $stock,
            ":product_id" => $productId
        ]);
    }

    public function deleteProduct($productId)
    {
        $sql = "DELETE FROM products 
                WHERE product_id = :product_id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ":product_id" => $productId
        ]);
    }
}

?>
