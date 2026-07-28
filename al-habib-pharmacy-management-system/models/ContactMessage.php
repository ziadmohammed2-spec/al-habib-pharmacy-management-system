<?php

class ContactMessage
{
    private $messageId;
    private $subject;
    private $message;
    private $status;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function setMessageId($messageId)
    {
        $this->messageId = $messageId;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getMessageId()
    {
        return $this->messageId;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function sendMessage()
    {
        echo "Message sent successfully";
    }

    public function markAsRead()
    {
        $this->status = "Read";
        echo "Message marked as read";
    }

    public function updateStatus()
    {
        echo "Message status updated successfully";
    }

    public function viewMessageDetails()
    {
        echo "Message ID: " . $this->messageId . "<br>";
        echo "Subject: " . $this->subject . "<br>";
        echo "Message: " . $this->message . "<br>";
        echo "Status: " . $this->status . "<br>";
    }
}

?>