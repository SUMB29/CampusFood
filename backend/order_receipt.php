<?php
session_start();
include "db_connect.php";

// ✅ Ensure recipient is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: ../login.html");
    exit();
}

$recipient_id = $_SESSION['user_id'];
$claim_id = intval($_GET['claim_id'] ?? 0);

if ($claim_id <= 0) {
    die("Invalid claim ID.");
}

// ✅ Fetch claim details with food + provider
$stmt = $conn->prepare("
    SELECT c.id AS claim_id, c.quantity AS claimed_qty, c.status AS claim_status, c.claimed_at,c.total_bill,
           f.food_title, f.food_description, f.available_until, f.pickup_location,
           u.username AS provider_name
    FROM claims c
    JOIN food_listings f ON c.food_id = f.id
    JOIN users u ON f.provider_id = u.id
    WHERE c.id = ? AND c.recipient_id = ?
");
$stmt->bind_param("ii", $claim_id, $recipient_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found.");
}

// ✅ Pickup time logic
$claim_time = strtotime($order['claimed_at']);
$pickup_start = date("h:i A", $claim_time);

// Default pickup end is 1 hour after claim
$pickup_end = strtotime("+1 hour", $claim_time);

// Convert available_until to timestamp
$available_until = strtotime($order['available_until']);

// If 1-hour window exceeds available_until, cap it
if ($pickup_end > $available_until) {
    $pickup_end = $available_until;
}

$pickup_end_formatted = date("h:i A", $pickup_end);

// ✅ Concatenate pickup window
$pickup_window = $pickup_start . " - " . $pickup_end_formatted;

$updateStmt = $conn->prepare("UPDATE claims SET pickup_window = ? WHERE id = ? and recipient_id = ?");
$updateStmt->bind_param("sii", $pickup_window, $order['claim_id'],$recipient_id);
$updateStmt->execute();
$updateStmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Receipt</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-4 text-center text-green-700">🍽️ Order Receipt</h1>

        <!-- ✅ Confirmation Message -->
        <?php if ($order['claim_status'] === 'Pickup Pending'): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4 text-center">
             ✅ Your booking is under process.Select payment method to pickup between <br> <strong><?= $pickup_start ?> - <?= $pickup_end_formatted ?></strong>.
         </div>
        <?php endif; ?>

        <div class="space-y-3">
            <p><strong>Claim ID:</strong> <?= htmlspecialchars($order['claim_id']) ?></p>
            <p><strong>Food:</strong> <?= htmlspecialchars($order['food_title']) ?> - <?= htmlspecialchars($order['food_description']) ?></p>
            <p><strong>Quantity Claimed:</strong> <?= htmlspecialchars($order['claimed_qty']) ?></p>
            <p><strong>Total Bill:</strong> Rs.  <?= htmlspecialchars(number_format($order['total_bill'], 2)) ?></p>
            <p><strong>Claim Date:</strong> <?= date("d M Y, h:i A", strtotime($order['claimed_at'])) ?></p>
            <p><strong>Pickup Location:</strong> <?= htmlspecialchars($order['pickup_location']) ?></p>
            <p><strong>Pickup Window:</strong> <?= $pickup_start ?> - <?= $pickup_end_formatted ?></p>
            <p><strong>Provider:</strong> <?= htmlspecialchars($order['provider_name']) ?></p>
            <p><strong>Status:</strong> <span class="font-semibold"><?= htmlspecialchars($order['claim_status']) ?></span></p>
            <button class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition duration-300"><a href="../checkout.html">Select Payment Mode</a></button>
        </div>

        <!-- <div class="mt-6 text-center">
            <a href="recipient_dashboard.php" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Back to Dashboard</a> -->
        <!-- </div> -->
    </div>
</body>
</html>
