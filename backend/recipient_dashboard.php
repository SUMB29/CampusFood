<?php
session_start();

// ✅ Ensure user is logged in and is a recipient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: ../login.html");
    exit();
}

include "db_connect.php";

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// ✅ Update expired listings dynamically
$conn->query("
    UPDATE food_listings 
    SET status = 'Expired' 
    WHERE status = 'Active' AND available_until <= NOW()
");

// ✅ Fetch ALL food items (recipients should see everything)
$stmt = $conn->prepare("SELECT * FROM food_listings WHERE status = 'Active' ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$food_items = $result->fetch_all(MYSQLI_ASSOC);

// ✅ Fetch user orders (order history) – FIXED JOIN
$order_stmt = $conn->prepare("
    SELECT c.id AS claim_id, c.quantity, c.status, c.claimed_at,c.pickup_window,
           f.food_title, f.food_description, f.pickup_location, f.available_until
    FROM claims c
    JOIN food_listings f ON c.food_id = f.id
    WHERE c.recipient_id = ?
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
  <title>Recipient Dashboard | CampusFoodShare</title>
  <link href="../src/output.css" rel="stylesheet">
  <script>
    function showTab(tab) {
      // hide all
      document.getElementById("browseSection").classList.add("hidden");
      document.getElementById("ordersSection").classList.add("hidden");

      // show selected
      document.getElementById(tab).classList.remove("hidden");

      // highlight tabs
      document.getElementById("browseTab").classList.remove("bg-green-900");
      document.getElementById("orderTab").classList.remove("bg-green-900");
      document.getElementById(tab + "Tab").classList.add("bg-green-900");
    }
  </script>
</head>
<body class="bg-white min-h-screen font-sans">

  <!-- Navbar -->
<div class="bg-green-700 text-white px-6 py-4 flex justify-between items-center">
  
  <!-- Left: Logo / Title -->
  <h1 class="text-xl font-bold">CampusFoodShare - Recipient Dashboard</h1>

  <!-- Middle: Tabs -->
  <div class="flex gap-4">
    <button id="browseSectionTab" onclick="showTab('browseSection')" 
      class="px-3 py-2 rounded-lg hover:bg-green-900 transition">
      Browse Listings
    </button>
    <button id="ordersSectionTab" onclick="showTab('ordersSection')" 
      class="px-3 py-2 rounded-lg hover:bg-green-900 transition">
      My Orders
    </button>
  </div>

  <!-- Right: User, Notifications, Logout -->
  <div class="flex items-center gap-6">
    <span>Welcome, <b><?php echo htmlspecialchars($username); ?></b></span>

    <!-- Notification Bell -->
    <div class="relative" id="notifWrapper">
      <button id="notifBell" class="relative p-2 rounded-full hover:bg-green-600 focus:outline-none">
        <span class="sr-only">Open notifications</span>
        <span>🔔</span>
        <span id="notifCount" 
          class="hidden absolute -top-1 -right-1 text-xs px-1 rounded-full bg-red-600 text-white">
          0
        </span>
      </button>

      <!-- Dropdown -->
      <div id="notifDropdown" 
        class="hidden fixed right-0 mt-2 w-80 bg-green-50 border border-green-200 rounded-2xl shadow-lg overflow-hidden">
        <div class="px-4 py-2 font-semibold bg-green-100 text-green-900">Notifications</div>
        <ul id="notifList" class="max-h-96 overflow-y-auto divide-y divide-green-200 text-black"></ul>
        <div class="px-4 py-2 bg-green-100 text-right">
          <button id="markAllBtn" class="text-sm underline text-green-800 hover:text-green-900">
            Mark all as read
          </button>
        </div>
      </div>
    </div>

    <!-- Logout -->
    <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg shadow-md transition">
      Logout
    </a>
  </div>
</div>


<!-- Browse Listings Section -->
<div id="browseSection" class="max-w-4xl mx-auto mt-8">
  <h2 class="text-2xl font-bold text-green-700 mb-6">Available Food Listings</h2>
  <div class="bg-white rounded-xl shadow-lg border border-gray-300 p-6">
    <?php if (count($food_items) > 0): ?>
      <div class="space-y-5">
        <?php foreach ($food_items as $item): ?>
          <div class="bg-green-50 rounded-lg p-5 border border-gray-200 hover:bg-gray-100 transition">
            <h3 class="text-xl font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($item['food_title']); ?></h3>
            <p class="text-sm text-gray-700 mb-2">📝 <?php echo htmlspecialchars($item['food_description'] ?? 'No description'); ?></p>
            <p class="text-sm text-gray-700 mb-2">🥗 Type: 
              <span class="<?php echo ($item['food_type'] === 'Veg') ? 'text-green-700 font-medium' : 'text-red-700 font-medium'; ?>">
                <?php echo htmlspecialchars($item['food_type']); ?>
              </span>
            </p>
            <p class="text-sm text-gray-700 mb-1">
                🍽️ Available Quantity: 
                <span class="font-medium">
                    <?php echo $item['quantity'] . ' ' . htmlspecialchars($item['quantity_unit']).'s'; ?>
                </span>
            </p>
            <p class="text-sm text-gray-700 mb-2">🌱 Freshness Status: 
              <span class="<?php echo ($item['freshness_status'] === 'Fresh') ? 'text-green-700 font-medium' : 'text-red-700 font-medium'; ?>">
                <?php echo htmlspecialchars($item['freshness_status']); ?>
              </span>
            </p>
            
            <p class="text-sm text-gray-700 mb-1">
                💸 Price per <span class="text-sm text-gray-700 mb-1"><?php echo htmlspecialchars($item['quantity_unit']); ?></span>: 
                <span class="font-medium">₹<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></span>
            </p>

            <p class="text-sm text-gray-700 mb-1">📍 Pickup Location: <?php echo htmlspecialchars($item['pickup_location']); ?></p>
            <p class="text-sm text-gray-700">⏳ Expires In: <span id="timer-<?php echo $item['id']; ?>" data-expiry="<?php echo $item['available_until']; ?>" class="font-medium text-red-600"></span></p>

            <form method="POST" action="claim_food.php" class="mt-4 flex items-center gap-4 flex-wrap">
              <input type="hidden" name="food_id" value="<?php echo $item['id']; ?>">
              <!-- Hidden input to pass total bill -->
              <input type="hidden" name="total_bill" id="total_input-<?php echo $item['id']; ?>" value="<?php echo $item['price']; ?>">

              <!-- Quantity Selector -->
              <div class="flex items-center border rounded-lg overflow-hidden">
                  <button type="button" onclick="decrementQty(<?php echo $item['id']; ?>)" class="px-3 bg-gray-200">-</button>
                  <input type="number" name="quantity" id="qty-<?php echo $item['id']; ?>" 
                        value="1" min="1" max="<?php echo $item['quantity']; ?>" 
                        class="w-12 text-center" 
                        data-price="<?php echo $item['price']; ?>">
                  <button type="button" onclick="incrementQty(<?php echo $item['id']; ?>)" class="px-3 bg-gray-200">+</button>
              </div>

              <!-- 💰 Total beside quantity -->
              <p class="text-sm text-gray-700">
                  💰 Total: ₹<span id="total-<?php echo $item['id']; ?>" class="font-medium">
                      <?php echo number_format($item['price'], 2); ?>
                  </span>
              </p>

              <!-- Claim Button -->
              <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg">
                  Claim This Food
              </button>
          </form>


          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-gray-600">No food items available.</p>
    <?php endif; ?>
  </div>
</div>

<!-- My Orders Section -->
<div id="ordersSection" class="max-w-4xl mx-auto mt-8 hidden">
  <h2 class="text-2xl font-bold text-green-700 mb-6">My Orders</h2>
  <div class="bg-white rounded-xl shadow-lg border border-gray-300 p-6">
    <?php if (count($orders) > 0): ?>
      <div class="space-y-5">
        <?php foreach ($orders as $order): ?>
          <div onclick="openReceipt(<?= $order['claim_id'] ?>)"  
            class="bg-yellow-50 rounded-lg p-5 border border-gray-200">
            <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($order['food_title']); ?></h3>
            <p>📦 Quantity: <?php echo htmlspecialchars($order['quantity']); ?></p>
            <p>📍 Pickup Location: <?php echo htmlspecialchars($order['pickup_location']); ?></p>  
            <p>🕒 Pickup Window: <?php echo htmlspecialchars($order['pickup_window']); ?></p>
            <p class="font-semibold">📌 Status: <?php echo htmlspecialchars($order['status']); ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-gray-600">You have not claimed any orders yet.</p>
    <?php endif; ?>
  </div>
</div>



<script>
// Timer
function updateTimers() {
  document.querySelectorAll("[id^='timer-']").forEach(el => {
    const expiry = new Date(el.dataset.expiry).getTime();
    const now = new Date().getTime();
    const distance = expiry - now;
    if (distance <= 0) {
      el.textContent = "Expired";
    } else {
      const h = Math.floor(distance / (1000*60*60));
      const m = Math.floor((distance % (1000*60*60)) / (1000*60));
      const s = Math.floor((distance % (1000*60)) / 1000);
      el.textContent = `${h}h ${m}m ${s}s`;
    }
  });
}
setInterval(updateTimers, 1000);

function updateTotal(id) {
  const qtyInput = document.getElementById(`qty-${id}`);
  const totalEl = document.getElementById(`total-${id}`);
  const totalInput = document.getElementById(`total_input-${id}`);
  const pricePerUnit = parseFloat(qtyInput.dataset.price);

  const total = (qtyInput.value * pricePerUnit).toFixed(2);
  totalEl.textContent = total;
  totalInput.value = total; // ← update hidden input for form submission
}


function incrementQty(id) {
  let input = document.getElementById(`qty-${id}`);
  if (parseInt(input.value) < parseInt(input.max)) {
    input.value++;
    updateTotal(id); // ← update total after increment
  }
}

function decrementQty(id) {
  let input = document.getElementById(`qty-${id}`);
  if (parseInt(input.value) > parseInt(input.min)) {
    input.value--;
    updateTotal(id); // ← update total after decrement
  }
}
</script>
<script>

async function fetchNotifications() {
  try {
    const res = await fetch('notifications.php', { credentials: 'same-origin' });
    const data = await res.json();
    if (!data.success) return;

    const countEl = document.getElementById('notifCount');
    const listEl = document.getElementById('notifList');
    listEl.innerHTML = '';

    // Filter unread only
    const unreadNotifications = data.notifications.filter(n => n.is_read == 0);

    // Badge
    if (unreadNotifications.length > 0) {
      countEl.textContent = unreadNotifications.length;
      countEl.classList.remove('hidden');
    } else {
      countEl.classList.add('hidden');
    }

    // List
    if (unreadNotifications.length === 0) {
      const li = document.createElement('li');
      li.className = 'px-4 py-3 text-sm text-gray-500';
      li.textContent = 'No unread notifications';
      listEl.appendChild(li);
    } else {
      unreadNotifications.forEach(n => {
        const li = document.createElement('li');
        li.className = 'px-4 py-3 text-sm hover:bg-gray-50';
        const time = new Date(n.created_at.replace(' ', 'T')); // naive parse
        li.innerHTML = `
          <div class="flex items-start gap-3">
            <div class="mt-0.5">🟢</div>
            <div class="flex-1">
              <div class="font-medium">${n.message}</div>
              <div class="text-xs text-gray-500">${time.toLocaleString()}</div>
            </div>
          </div>
        `;
        listEl.appendChild(li);
      });
    }
  } catch (e) {
    console.error('notif fetch failed', e);
  }
}

// Toggle dropdown & mark as read
document.getElementById('notifBell').addEventListener('click', async () => {
  const dd = document.getElementById('notifDropdown');
  dd.classList.toggle('hidden');
  if (!dd.classList.contains('hidden')) {
    //await fetch('notifications_mark_read.php', { method: 'POST', credentials: 'same-origin' });
    await fetchNotifications();
  }
});

document.getElementById('markAllBtn').addEventListener('click', async () => {
  await fetch('notifications_mark_read.php', { method: 'POST', credentials: 'same-origin' });
  await fetchNotifications();
});

// Poll every 10s
fetchNotifications();
setInterval(fetchNotifications, 10000);

 // Default: show Browse
showTab('browseSection');
</script>

</body>
</html>
