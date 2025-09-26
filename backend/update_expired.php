<?php
include "C:\xampp\htdocs\FreshShare\backend\db_connect.php";

// Update expired listings
$sql = "UPDATE food_listings 
        SET status = 'Expired' 
        WHERE status = 'Active' AND available_until <= NOW()";

$conn->query($sql);
$conn->close();
?>
