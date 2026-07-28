<?php

abstract class User
{
    protected $userId;
    protected $name;
    protected $email;
    protected $password;
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function login($email, $password)
    {
        $sql = "SELECT * FROM users 
                WHERE email = :email 
                AND password = :password 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ":email" => $email,
            ":password" => $password
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function logout()
    {
        session_start();
        session_destroy();

        echo "User logged out successfully.<br>";
    }

    public function resetPassword($newPassword)
    {
        $sql = "UPDATE users 
                SET password = :password 
                WHERE user_id = :user_id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ":password" => $newPassword,
            ":user_id" => $this->userId
        ]);
    }
}

?>