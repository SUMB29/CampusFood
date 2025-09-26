<?php
session_start();
include "db_connect.php";

// Ensure provider is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    header("Location: ../login.html");
    exit();
}

$provider_id = $_SESSION['user_id'];
$claim_id = intval($_POST['claim_id'] ?? 0);

if($claim_id <= 0) exit;

// Fetch the claim info
$stmt = $conn->prepare("
    SELECT c.id, c.quantity AS claimed_qty, c.claimed_at, c.status,
           f.id AS food_id, f.quantity AS available_qty
    FROM claims c
    JOIN food_listings f ON c.food_id = f.id
    WHERE c.id = ? AND f.provider_id = ?
");
$stmt->bind_param("ii", $claim_id, $provider_id);
$stmt->execute();
$claim = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$claim) exit;

$claimed_at = strtotime($claim['claimed_at']);
$expiry_time = $claimed_at + 3600; // 1 hour after claimed_at
$now = time();

// Auto-expiry
if(strtolower(trim($claim['status'])) === 'pickup pending' && $now < $expiry_time){
 // If within 1 hour and provider clicks "Mark as Complete"
    $update_claim = $conn->prepare("UPDATE claims SET status='Completed' WHERE id=?");
    $update_claim->bind_param("i", $claim_id);
    $update_claim->execute();
    $update_claim->close();
}
// Redirect back to dashboard
header("Location: provider_dashboard.php");
exit();
?>
