<?php

require_once __DIR__ . "/../database/MySQL.php";

class AddressController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function index($userId)
    {
        $sql = "SELECT * FROM addresses
                WHERE user_id = :user_id
                ORDER BY address_id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ":user_id" => $userId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function store($userId, $city, $street, $buildingNo = null, $details = null)
    {
        $userId = (int) $userId;
        $city = trim((string) $city);
        $street = trim((string) $street);

        if ($userId <= 0 || $city === "" || $street === "") {
            return false;
        }

        $sql = "INSERT INTO addresses (user_id, city, street, building_no, details)
                VALUES (:user_id, :city, :street, :building_no, :details)";

        $stmt = $this->db->prepare($sql);

        $result = $stmt->execute([
            ":user_id" => $userId,
            ":city" => $city,
            ":street" => $street,
            ":building_no" => $buildingNo,
            ":details" => $details
        ]);

        if ($result) {
            return (int) $this->db->lastInsertId();
        }

        return false;
    }

    public function show($addressId)
    {
        $sql = "SELECT * FROM addresses WHERE address_id = :address_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ":address_id" => $addressId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($addressId, $city, $street, $buildingNo = null, $details = null)
    {
        $addressId = (int) $addressId;
        $city = trim((string) $city);
        $street = trim((string) $street);

        if ($addressId <= 0 || $city === "" || $street === "") {
            return false;
        }

        $sql = "UPDATE addresses
                SET city = :city,
                    street = :street,
                    building_no = :building_no,
                    details = :details
                WHERE address_id = :address_id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ":city" => $city,
            ":street" => $street,
            ":building_no" => $buildingNo,
            ":details" => $details,
            ":address_id" => $addressId
        ]);
    }

    public function delete($addressId)
    {
        $addressId = (int) $addressId;
        if ($addressId <= 0) {
            return false;
        }

        $sql = "DELETE FROM addresses WHERE address_id = :address_id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ":address_id" => $addressId
        ]);
    }
}

?>
