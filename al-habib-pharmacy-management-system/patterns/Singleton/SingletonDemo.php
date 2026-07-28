<?php

require_once "DatabaseSingleton.php";

$database1 = DatabaseSingleton::getInstance();
$connection1 = $database1->getConnection();

$database2 = DatabaseSingleton::getInstance();
$connection2 = $database2->getConnection();

if ($connection1 === $connection2) {
    echo "Singleton Pattern Works: Both variables use the same database connection.";
} else {
    echo "Singleton Pattern Failed: Different database connections were created.";
}

?>