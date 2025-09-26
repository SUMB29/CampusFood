<?php
$host = "localhost";   // DB host
$user = "root";        // DB username
$pass = "";            // DB password
$db   = "smart_surplus"; // DB name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed"]));
}
?>
