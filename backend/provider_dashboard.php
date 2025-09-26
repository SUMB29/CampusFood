<?php
session_start();
include "db_connect.php";

// ✅ Ensure provider is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    header("Location: ../login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// --- AUTO-EXPIRE CLAIMS ---
$now = time();
$stmt = $conn->prepare("
    SELECT c.id AS claim_id, c.quantity AS claimed_qty, c.claimed_at,
           f.id AS food_id, f.quantity AS available_qty
    FROM claims c
    JOIN food_listings f ON c.food_id = f.id
    WHERE c.status = 'Pickup Pending' AND f.provider_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$claims = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ✅ Fetch food items for this provider
$stmt = $conn->prepare("SELECT * FROM food_listings WHERE provider_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$food_items = $result->fetch_all(MYSQLI_ASSOC);

// ✅ Fetch all orders for this provider's food along with recipient info
$order_stmt = $conn->prepare("
    SELECT c.id AS claim_id, c.quantity, c.status, c.claimed_at, c.pickup_window,
           f.id AS food_id, f.food_title, f.quantity AS available_qty, f.available_until,
           u.username AS recipient_name, u.email AS recipient_email
    FROM claims c
    JOIN food_listings f ON c.food_id = f.id
    JOIN users u ON c.recipient_id = u.id
    WHERE f.provider_id = ?
    ORDER BY c.claimed_at DESC
");
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$orders = $order_result->fetch_all(MYSQLI_ASSOC);

// ✅ Auto-update expired claims
foreach($orders as $order) {
    if(strtolower($order['status']) === 'pickup pending') {
        $claimed_time = strtotime($order['claimed_at']);
        $one_hour_after_claim = $claimed_time + 3600;
        $listing_available_until = strtotime($order['available_until']);

        // Actual expiry is the earlier of the two
        $expiry_time = min($one_hour_after_claim, $listing_available_until);

        if(time() > $expiry_time) {
            // Rollback quantity
            $new_qty = $order['available_qty'] + $order['quantity'];
            $update_food = $conn->prepare("UPDATE food_listings SET quantity=? WHERE id=?");
            $update_food->bind_param("ii", $new_qty, $order['food_id']);
            $update_food->execute();
            $update_food->close();

            // Mark claim as Expired
            $update_claim = $conn->prepare("UPDATE claims SET status='Expired' WHERE id=?");
            $update_claim->bind_param("i", $order['claim_id']);
            $update_claim->execute();
            $update_claim->close();

            // Update local copy to reflect in UI
            $order['status'] = 'Expired';
        }
    }
}

// ✅ Fetch food items for this provider
$stmt = $conn->prepare("SELECT * FROM food_listings WHERE provider_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$food_items = $result->fetch_all(MYSQLI_ASSOC);

// ✅ Fetch all orders for this provider's food along with recipient info
$order_stmt = $conn->prepare("
    SELECT c.id AS claim_id, c.quantity, c.status, c.claimed_at, c.pickup_window,
           f.food_title, f.id AS food_id,
           u.username AS recipient_name, u.email AS recipient_email
    FROM claims c
    JOIN food_listings f ON c.food_id = f.id
    JOIN users u ON c.recipient_id = u.id
    WHERE f.provider_id = ?
    ORDER BY c.claimed_at DESC
");
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$orders = $order_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Provider Dashboard | CampusFoodShare</title>
<link href="../src/output.css" rel="stylesheet">
<script>
function showTab(tab) {
    document.getElementById("addFormSection").classList.add("hidden");
    document.getElementById("browseSection").classList.add("hidden");
    document.getElementById("ordersSection").classList.add("hidden");

    document.getElementById(tab).classList.remove("hidden");

    document.getElementById("addTab").classList.remove("bg-green-900");
    document.getElementById("browseTab").classList.remove("bg-green-900");
    document.getElementById("ordersTab").classList.remove("bg-green-900");

    document.getElementById(tab + "Tab").classList.add("bg-green-900");
}
</script>
</head>
<body class="bg-white min-h-screen font-sans">

<!-- Navbar -->
<div class="bg-green-700 text-white px-6 py-4 flex justify-between items-center">
    <h1 class="text-xl font-bold">CampusFoodShare - Provider Dashboard</h1>
    <div class="flex gap-4">
        <button id="browseTab" onclick="showTab('browseSection')" class="px-3 py-2 rounded-lg hover:bg-green-900">Browse Listings</button>
        <button id="addTab" onclick="showTab('addFormSection')" class="px-3 py-2 rounded-lg hover:bg-green-800">Add Listing</button>
        <button id="ordersTab" onclick="showTab('ordersSection')" class="px-3 py-2 rounded-lg hover:bg-green-800">Orders</button>
    </div>
    <div>
        <span class="mr-4">Welcome, <b><?= htmlspecialchars($username) ?></b></span>
        <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg">Logout</a>
    </div>
</div>

<!-- Add Listing Section -->
<div id="addFormSection" class="max-w-2xl mx-auto mt-8 hidden">
    <div class="bg-white rounded-xl shadow-lg border border-gray-300 p-6">
        <h2 class="text-2xl font-bold text-green-700 mb-6">Add Food Listing</h2>
        <form action="save_listing.php" method="POST" class="space-y-4">
            <input type="text" name="food_title" placeholder="Food Title" class="w-full border rounded-lg px-4 py-2" required>
            <textarea name="food_description" placeholder="Food Description" class="w-full border rounded-lg px-4 py-2" rows="3" required></textarea>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Food Type</label>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2">
                        <input type="radio" name="food_type" value="Veg" required class="text-green-600">
                        <span class="text-gray-800">Veg</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" name="food_type" value="Non-Veg" required class="text-red-600">
                        <span class="text-gray-800">Non-Veg</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                <div class="flex gap-3">
                    <input type="number" name="quantity" placeholder="Enter quantity" class="w-1/2 border rounded-lg px-4 py-2" required>
                    <select name="quantity_unit" class="w-1/2 border rounded-lg px-4 py-2" required>
                        <option value="servings">Serving</option>
                        <option value="plates">Plate</option>
                        <option value="packets">Packet</option>
                        <option value="boxes">Piece</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Price (₹)</label>
                <input type="number" name="price" placeholder="Enter price" class="w-full border rounded-lg px-4 py-2" step="0.01" required>
            </div>

            <label class="block text-sm font-medium text-gray-700 mb-1">Available Until</label>
            <input type="datetime-local" name="available_until" class="w-full border rounded-lg px-4 py-2" required>
            <input type="text" name="pickup_location" placeholder="Pickup Location" class="w-full border rounded-lg px-4 py-2" required>

            <div class="flex justify-end gap-3">
                <button type="reset" class="px-4 py-2 bg-gray-300 rounded-lg">Clear</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Browse Listings Section -->
<div id="browseSection" class="max-w-4xl mx-auto mt-8">
    <h2 class="text-2xl font-bold text-green-700 mb-6">Your Food Listings</h2>
    <div class="bg-white rounded-xl shadow-lg border border-gray-300 p-6">
        <?php if (count($food_items) > 0): ?>
            <div class="space-y-5">
                <?php foreach ($food_items as $item): ?>
                <div class="bg-green-50 rounded-lg p-5 border border-gray-200 hover:bg-gray-100 transition">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2"><?= htmlspecialchars($item['food_title']) ?></h3>
                    <p class="text-sm text-gray-700 mb-2">📝 <?= htmlspecialchars($item['food_description'] ?? 'No description') ?></p>
                    <p class="text-sm text-gray-700 mb-2">🥗 Type: 
                        <span class="<?= ($item['food_type'] === 'Veg') ? 'text-green-700' : 'text-red-700' ?> font-medium"><?= htmlspecialchars($item['food_type']) ?></span>
                    </p>
                    <p class="text-sm text-gray-700 mb-1">🍽️ Quantity: <span class="font-medium"><?= htmlspecialchars($item['quantity'] . ' ' . $item['quantity_unit']) ?></span></p>
                    <p class="text-sm text-gray-700 mb-1">📍 Pickup: <span class="font-medium"><?= htmlspecialchars($item['pickup_location'] ?? 'N/A') ?></span></p>
                    <p class="text-sm text-gray-700">⏳ Expires In: 
                        <span id="timer-<?= $item['id'] ?>" data-expiry="<?= htmlspecialchars($item['available_until']) ?>" class="font-medium text-red-600"></span>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-600">No food items added yet.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Orders Section -->
<div id="ordersSection" class="max-w-4xl mx-auto mt-8 hidden">
    <h2 class="text-2xl font-bold text-green-700 mb-6">Orders for Your Listings</h2>
    <div class="bg-white rounded-xl shadow-lg border border-gray-300 p-6">
        <?php if (count($orders) > 0): ?>
        <div class="space-y-5">
            <?php foreach ($orders as $order): ?>
            <div class="bg-yellow-50 rounded-lg p-5 border border-gray-200 hover:bg-yellow-100 transition">
                <p class="text-sm text-gray-600">🆔 Claim ID: <span class="font-medium"><?= htmlspecialchars($order['claim_id']) ?></span></p>
                <h3 class="text-xl font-semibold"><?= htmlspecialchars($order['food_title']) ?></h3>
                <p>📦 Quantity Ordered: <?= htmlspecialchars($order['quantity']) ?></p>
                <p>🕒 Claimed At: <?= htmlspecialchars($order['claimed_at']) ?></p>
                <p>🕒 Pickup Window: <?= htmlspecialchars($order['pickup_window']) ?></p>
                <p>👤 Ordered By: <span class="font-medium"><?= htmlspecialchars($order['recipient_name']) ?></span> (<?= htmlspecialchars($order['recipient_email']) ?>)</p>
                <p class="font-semibold">📌 Status: <?= htmlspecialchars($order['status']) ?></p>

                <?php if(strtolower(trim($order['status'])) === 'pickup pending'): ?>
                <form method="POST" action="update_claim_status.php" class="mt-2">
                    <input type="hidden" name="claim_id" value="<?= $order['claim_id'] ?>">
                    <button 
                        type="submit" 
                        style="
                            background-color:#2563eb; 
                            color:white; 
                            padding:8px 16px; 
                            border-radius:8px; 
                            font-weight:600; 
                            box-shadow:0 2px 6px rgba(0,0,0,0.2); 
                            transition:all 0.2s ease;
                            cursor:pointer;
                        " 
                        onmouseover="this.style.backgroundColor='#1e40af'; this.style.transform='scale(1.05)';" 
                        onmouseout="this.style.backgroundColor='#2563eb'; this.style.transform='scale(1)';"
                    >
                        ✅ Mark as Complete
                    </button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-gray-600">No orders yet.</p>
        <?php endif; ?>
    </div>
</div>

<script>
// Expiry Timer
function updateTimers() {
    document.querySelectorAll("[id^='timer-']").forEach(el => {
        const expiry = new Date(el.dataset.expiry).getTime();
        const now = new Date().getTime();
        const distance = expiry - now;

        if (distance <= 0) {
            el.innerHTML = "Expired";
            el.classList.remove("text-red-600");
            el.classList.add("text-gray-500");
        } else {
            const h = Math.floor((distance % (1000*60*60*24)) / (1000*60*60));
            const m = Math.floor((distance % (1000*60*60)) / (1000*60));
            const s = Math.floor((distance % (1000*60)) / 1000);
            el.innerHTML = `${h}h ${m}m ${s}s`;
        }
    });
}
setInterval(updateTimers, 1000);

// Default: Show Browse Listings
showTab('browseSection');
</script>

</body>
</html>


