<?php
session_start();
include "db_connect.php";

// ✅ Ensure user is logged in and is a recipient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: ../login.html");
    exit();
}

$recipient_id = $_SESSION['user_id'];

// ✅ Get POST data
$food_id = intval($_POST['food_id'] ?? 0);
$quantity_claimed = intval($_POST['quantity'] ?? 0);
$total_price = floatval($_POST['total_bill'] ?? 0);


if ($food_id <= 0 || $quantity_claimed <= 0) {
    echo "<script>alert('Invalid request.'); window.history.back();</script>";
    exit();
}

// ✅ Fetch the current quantity of the food
$stmt = $conn->prepare("SELECT quantity, status FROM food_listings WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $food_id);
$stmt->execute();
$result = $stmt->get_result();
$food = $result->fetch_assoc();

if (!$food) {
    echo "<script>alert('Food item not found.'); window.history.back();</script>";
    exit();
}

if ($food['status'] !== 'Active') {
    echo "<script>alert('This food item is no longer available.'); window.history.back();</script>";
    exit();
}

$current_qty = intval($food['quantity']);

if ($quantity_claimed > $current_qty) {
    echo "<script>alert('Requested quantity exceeds available quantity.'); window.history.back();</script>";
    exit();
}

// ✅ Deduct quantity and possibly mark as expired
$new_qty = $current_qty - $quantity_claimed;
$status = ($new_qty <= 0) ? 'Expired' : 'Active';

$stmt = $conn->prepare("UPDATE food_listings SET quantity = ?, status = ? WHERE id = ?");
$stmt->bind_param("isi", $new_qty, $status, $food_id);
$stmt->execute();
$stmt->close();

// ✅ Calculate total bill
//$total_bill = $quantity_claimed * $unit_price;

// ✅ Insert claim into claims table with status "Pickup Pending" and total_bill
$stmt = $conn->prepare("INSERT INTO claims (food_id, recipient_id, quantity, total_bill, status) VALUES (?, ?, ?, ?, 'Pickup Pending')");
$stmt->bind_param("iiid", $food_id, $recipient_id, $quantity_claimed, $total_price);
$stmt->execute();
$claim_id = $stmt->insert_id; // Get new claim ID
$stmt->close();

$conn->close();

// ✅ Redirect directly to order receipt
header("Location: order_receipt.php?claim_id=" . $claim_id);
exit();

?>
