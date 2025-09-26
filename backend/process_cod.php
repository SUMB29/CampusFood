<?php
// process_cod.php
require "db_connect.php"; // mysqli $conn

$user_id = 1; // Demo user_id
$payment_method = "COD";
$status = "pending";

$stmt = $conn->prepare("INSERT INTO payments (user_id, method, status) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $payment_method, $status);

if ($stmt->execute()) {
   echo "<script>
        alert('✅ Order placed successfully!');
        window.location.href = 'recipient_dashboard.php';
      </script>";
exit();
} else {
    echo "❌ Error: " . $conn->error;
}
