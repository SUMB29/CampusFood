<?php
require "db_connect.php"; // mysqli $conn

// Your Razorpay Secret Key (from dashboard)
$razorpay_secret = "rzp_test_secretKeyHere";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['razorpay_payment_id']) && !empty($_POST['razorpay_order_id']) && !empty($_POST['razorpay_signature'])) {
        
        $payment_id = $_POST['razorpay_payment_id'];
        $order_id   = $_POST['razorpay_order_id'];
        $signature  = $_POST['razorpay_signature'];

        // Generate signature using same logic as Razorpay
        $generated_signature = hash_hmac("sha256", $order_id . "|" . $payment_id, $razorpay_secret);

        if ($generated_signature === $signature) {
            // ✅ Verified payment
            $user_id = 1; // Replace with actual logged-in user_id
            $method = "Razorpay";
            $status = "success";

            $stmt = $conn->prepare("INSERT INTO payments (user_id, method, status, payment_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $method, $status, $payment_id);

            if ($stmt->execute()) {
                echo "✅ Razorpay Payment Verified! Payment ID: $payment_id";
            } else {
                echo "❌ DB Error: " . $conn->error;
            }
        } else {
            // ❌ Invalid signature
            echo "❌ Payment verification failed!";
        }
    } else {
        echo "❌ Invalid Razorpay response!";
    }
}
