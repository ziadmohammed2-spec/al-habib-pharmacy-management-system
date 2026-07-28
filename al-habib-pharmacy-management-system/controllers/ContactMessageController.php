<?php

class ContactMessageController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function store($subject, $message, $customerId = null, $guestName = "", $guestEmail = "")
    {
        $subject = trim($subject);
        $message = trim($message);
        $guestName = trim($guestName);
        $guestEmail = trim($guestEmail);

        if ($subject === "" || $message === "") {
            return ["success" => false, "message" => "Subject and message are required."];
        }

        if ($customerId === null) {
            if ($guestName === "") {
                return ["success" => false, "message" => "Name is required for guest messages."];
            }
            if (!filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
                return ["success" => false, "message" => "Please enter a valid email address."];
            }

            $message = "Guest Name: " . $guestName . "\nGuest Email: " . $guestEmail . "\n\n" . $message;
        }

        $sql = "INSERT INTO contact_messages (customer_id, subject, message, status)
                VALUES (:customer_id, :subject, :message, 'Unread')";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ":customer_id" => $customerId,
            ":subject" => $subject,
            ":message" => $message
        ]);

        return [
            "success" => $result,
            "message" => $result ? "Message sent successfully." : "Failed to send message."
        ];
    }
}

?>
