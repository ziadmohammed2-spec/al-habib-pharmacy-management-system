<?php

class Address
{
    private $addressId;
    private $city;
    private $street;
    private $details;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function setAddressId($addressId)
    {
        $this->addressId = $addressId;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function setStreet($street)
    {
        $this->street = $street;
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }

    public function getAddressId()
    {
        return $this->addressId;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function validateAddress()
    {
        if (!empty($this->city) && !empty($this->street)) {
            echo "Address is valid";
            return true;
        } else {
            echo "City and street are required";
            return false;
        }
    }

    public function viewAddressDetails()
    {
        echo "Address ID: " . $this->addressId . "<br>";
        echo "City: " . $this->city . "<br>";
        echo "Street: " . $this->street . "<br>";
        echo "Details: " . $this->details . "<br>";
    }
}

?>