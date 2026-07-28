<?php

require_once __DIR__ . "/User.php";

class Customer extends User
{
    private $phone;
    private $loyaltyPoints;

    public function __construct($db)
    {
        parent::__construct($db);
        $this->loyaltyPoints = 0;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function setLoyaltyPoints($loyaltyPoints)
    {
        $this->loyaltyPoints = $loyaltyPoints;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getLoyaltyPoints()
    {
        return $this->loyaltyPoints;
    }

    public function register()
    {
        $sql = "INSERT INTO users (name, email, password, role) 
                VALUES (:name, :email, :password, 'customer')";

        $stmt = $this->db->prepare($sql);

        $userCreated = $stmt->execute([
            ":name" => $this->name,
            ":email" => $this->email,
            ":password" => $this->password
        ]);

        if ($userCreated) {
            $this->userId = $this->db->lastInsertId();

            $sql = "INSERT INTO customers (user_id, phone, loyalty_points) 
                    VALUES (:user_id, :phone, :loyalty_points)";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ":user_id" => $this->userId,
                ":phone" => $this->phone,
                ":loyalty_points" => $this->loyaltyPoints
            ]);
        }

        return false;
    }

    public function updateProfile()
    {
        $sql = "UPDATE users 
                SET name = :name, email = :email 
                WHERE user_id = :user_id";

        $stmt = $this->db->prepare($sql);

        $userUpdated = $stmt->execute([
            ":name" => $this->name,
            ":email" => $this->email,
            ":user_id" => $this->userId
        ]);

        if ($userUpdated) {
            $sql = "UPDATE customers 
                    SET phone = :phone, loyalty_points = :loyalty_points 
                    WHERE user_id = :user_id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ":phone" => $this->phone,
                ":loyalty_points" => $this->loyaltyPoints,
                ":user_id" => $this->userId
            ]);
        }

        return false;
    }

    public function addAddress($city, $street, $buildingNo)
    {
        $sql = "INSERT INTO addresses (user_id, city, street, building_no) 
                VALUES (:user_id, :city, :street, :building_no)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ":user_id" => $this->userId,
            ":city" => $city,
            ":street" => $street,
            ":building_no" => $buildingNo
        ]);
    }
}

?>
