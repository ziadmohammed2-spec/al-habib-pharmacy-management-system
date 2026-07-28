<?php

class AuthController
{
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    public function register($name, $email, $phone, $password, $confirmPassword)
    {
        $name = trim($name);
        $email = trim($email);
        $phone = trim($phone);

        if ($name === "" || $email === "" || $password === "" || $confirmPassword === "") {
            return ["success" => false, "message" => "All required fields must be filled."];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ["success" => false, "message" => "Please enter a valid email address."];
        }

        if (strlen($password) < 6) {
            return ["success" => false, "message" => "Password must be at least 6 characters."];
        }

        if ($password !== $confirmPassword) {
            return ["success" => false, "message" => "Password and confirm password do not match."];
        }

        if ($phone !== "" && !preg_match('/^[0-9]{10,15}$/', $phone)) {
            return ["success" => false, "message" => "Phone number must contain 10 to 15 digits."];
        }

        if ($this->emailExists($email)) {
            return ["success" => false, "message" => "This email is already registered."];
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO users (name, email, password, role)
                    VALUES (:name, :email, :password, 'customer')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ":name" => $name,
                ":email" => $email,
                ":password" => $hashedPassword
            ]);

            $userId = $this->db->lastInsertId();

            $sql = "INSERT INTO customers (user_id, phone, loyalty_points)
                    VALUES (:user_id, :phone, 0)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ":user_id" => $userId,
                ":phone" => $phone
            ]);

            $this->db->commit();
            return ["success" => true, "message" => "Account created successfully. Please login."];
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ["success" => false, "message" => "Registration failed: " . $e->getMessage()];
        }
    }

    public function login($email, $password)
    {
        $email = trim($email);

        if ($email === "" || $password === "") {
            return ["success" => false, "message" => "Email and password are required."];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ["success" => false, "message" => "Please enter a valid email address."];
        }

        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([":email" => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ["success" => false, "message" => "Invalid email or password."];
        }

        $validPassword = password_verify($password, $user["password"]);

        if (!$validPassword && $password === $user["password"]) {
            $validPassword = true;
            $this->rehashLegacyPassword($user["user_id"], $password);
        }

        if (!$validPassword) {
            return ["success" => false, "message" => "Invalid email or password."];
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["name"] = $user["name"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["role"] = $user["role"];

        if ($user["role"] === "customer") {
            $customer = $this->getCustomerByUserId($user["user_id"]);
            if ($customer) {
                $_SESSION["customer_id"] = $customer["customer_id"];
            }
        } elseif ($user["role"] === "admin") {
            $admin = $this->getAdminByUserId($user["user_id"]);
            if ($admin) {
                $_SESSION["admin_id"] = $admin["admin_id"];
            }
        }

        return ["success" => true, "message" => "Login successful.", "role" => $user["role"]];
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        session_unset();
        session_destroy();

        return true;
    }

    public function forgotPassword($email)
    {
        $email = trim($email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ["success" => false, "message" => "Please enter a valid email address."];
        }

        if (!$this->emailExists($email)) {
            return ["success" => false, "message" => "Email was not found."];
        }

        return ["success" => true, "message" => "Email found. You can reset your password now."];
    }

    public function resetPassword($email, $password, $confirmPassword)
    {
        $email = trim($email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ["success" => false, "message" => "Please enter a valid email address."];
        }

        if (strlen($password) < 6) {
            return ["success" => false, "message" => "Password must be at least 6 characters."];
        }

        if ($password !== $confirmPassword) {
            return ["success" => false, "message" => "Password and confirm password do not match."];
        }

        if (!$this->emailExists($email)) {
            return ["success" => false, "message" => "Email was not found."];
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "UPDATE users SET password = :password WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ":password" => $hashedPassword,
            ":email" => $email
        ]);

        return [
            "success" => $result,
            "message" => $result ? "Password reset successfully. Please login." : "Failed to reset password."
        ];
    }

    public function isAdmin()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION["role"]) && $_SESSION["role"] === "admin";
    }

    public function isLoggedIn()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION["user_id"]);
    }

    private function emailExists($email)
    {
        $sql = "SELECT user_id FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([":email" => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

    private function getCustomerByUserId($userId)
    {
        $sql = "SELECT * FROM customers WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([":user_id" => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getAdminByUserId($userId)
    {
        $sql = "SELECT * FROM admins WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([":user_id" => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function rehashLegacyPassword($userId, $password)
    {
        $sql = "UPDATE users SET password = :password WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ":password" => password_hash($password, PASSWORD_DEFAULT),
            ":user_id" => $userId
        ]);
    }
}

?>
