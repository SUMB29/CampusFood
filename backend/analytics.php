<?php
// Database connection
$host = "localhost";   // change if needed
$user = "root";        // your DB username
$pass = "";            // your DB password
$db   = "smart_surplus"; // your DB name

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Database connection failed: " . $conn->connect_error);
}

// --- Handle Provider Filter ---
$providerFilter = "";
$selectedProvider = "";
if (!empty($_GET['provider_id'])) {
  $providerId = intval($_GET['provider_id']);
  $providerFilter = " AND f.provider_id = $providerId ";
  $selectedProvider = $providerId;
}

// Fetch data from claims table (joined with food_listings to know provider)
$sql = "SELECT c.quantity, c.claimed_at 
        FROM claims c
        JOIN food_listings f ON c.food_id = f.id
        WHERE 1=1 $providerFilter";

$result = $conn->query($sql);

$totalKg = 0;
$foodOverTime = [];

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $qty = (float)$row['quantity'];  // Food Saved (kg)
    $totalKg += $qty;

    // Group by date for line chart
    $date = date("Y-m-d", strtotime($row['claimed_at']));
    if (!isset($foodOverTime[$date])) {
      $foodOverTime[$date] = 0;
    }
    $foodOverTime[$date] += $qty;
  }
}

// Calculations
$totalMeals = $totalKg * 0.5; // assuming 0.5kg = 1 meal
$co2Saved = $totalKg * 2.5;
$waterSaved = $totalKg * 5;

// Prepare data for charts
$labels = json_encode(array_keys($foodOverTime));
$foodData = json_encode(array_values($foodOverTime));

// Fetch providers for dropdown
$providers = $conn->query("SELECT id, username, subrole FROM users WHERE role='provider'");
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>CampusFoodShare Analytics</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gray-100">
  <!-- Header -->
  <header class="bg-emerald-600 text-white p-4 flex items-center">
    <a href="../index.html" class="bg-emerald-900 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
      <- Back
    </a>
    <span class="text-2xl font-bold ml-2">CampusFoodShare Analytics</span>
    <span class="ml-4 text-sm opacity-80">Impact of Food Sharing</span>
  </header>

  <!-- Metrics Cards -->
  <div class="grid grid-cols-4 gap-4 p-6">
    <div class="bg-white shadow-lg rounded-2xl p-6">
      <h3 class="text-sm font-medium text-gray-500">Food Saved</h3>
      <p class="text-3xl font-bold text-green-600"><?php echo $totalKg; ?> kg</p>
    </div>
    <div class="bg-white shadow-lg rounded-2xl p-6">
      <h3 class="text-sm font-medium text-gray-500">Meals Served</h3>
      <p class="text-3xl font-bold text-blue-600"><?php echo $totalMeals; ?> meals</p>
    </div>
    <div class="bg-white shadow-lg rounded-2xl p-6">
      <h3 class="text-sm font-medium text-gray-500">CO₂ Avoided</h3>
      <p class="text-3xl font-bold text-green-700"><?php echo $co2Saved; ?> kg</p>
    </div>
    <div class="bg-white shadow-lg rounded-2xl p-6">
      <h3 class="text-sm font-medium text-gray-500">Water Saved</h3>
      <p class="text-3xl font-bold text-blue-700"><?php echo number_format($waterSaved); ?> kL</p>
    </div>
  </div>

  <!-- Chart Section -->
  <div class="p-6 grid grid-cols-2 gap-6">
    <!-- Pie Chart (Top Left) -->
    <div class="bg-white shadow-lg rounded-2xl p-6 flex justify-center">
      <div class="w-64 h-64">
        <h3 class="text-lg font-semibold text-gray-700 mb-4 text-center">Impact Distribution</h3>
        <canvas id="pieChart"></canvas>
      </div>
    </div>

    <!-- Line Chart (Top Right) -->
    <div class="bg-white shadow-lg rounded-2xl p-6">
      <h3 class="text-lg font-semibold text-gray-700 mb-4">Food Saved Over Time</h3>
      <canvas id="lineChart" class="w-full h-64"></canvas>
    </div>

    <!-- Bottom Section: Bar Chart + Filter -->
    <div class="col-span-2 grid grid-cols-3 gap-6">
      <!-- Bar Chart (Left, wider) -->
      <div class="col-span-2 bg-white shadow-lg rounded-2xl p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Impact Overview</h3>
        <canvas id="impactChart" class="w-full h-64"></canvas>
      </div>

      <!-- Filter (Right of Bar Graph, Full Height Card) -->
      <div class="bg-white shadow-lg rounded-2xl p-6 flex flex-col justify-start w-full h-full">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Filter Analytics</h3>
        <form method="GET" class="space-y-4 max-w-sm">

          <!-- Provider Dropdown -->
          <div>
            <label for="provider_id" class="block text-sm font-medium text-gray-600 mb-1">Provider</label>
            <select id="provider_id" name="provider_id"
              class="w-full p-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm transition">
              <option value="">All Providers</option>
              <?php while ($row = $providers->fetch_assoc()) { ?>
                <option value="<?php echo $row['id']; ?>" <?php echo ($selectedProvider == $row['id']) ? "selected" : ""; ?>>
                  <?php echo $row['username'] . " (" . $row['subrole'] . ")"; ?>
                </option>
              <?php } ?>
            </select>
          </div>

          <!-- Start Date -->
          <div>
            <label for="start_date" class="block text-sm font-medium text-gray-600 mb-1">Start Date</label>
            <input type="date" id="start_date" name="start_date"
              class="w-full p-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm transition">
          </div>

          <!-- End Date -->
          <div>
            <label for="end_date" class="block text-sm font-medium text-gray-600 mb-1">End Date</label>
            <input type="date" id="end_date" name="end_date"
              class="w-full p-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm transition">
          </div>

          <!-- Submit Button -->
          <button type="submit"
            class="w-full bg-emerald-600 text-white font-medium py-2 px-4 rounded-xl shadow-md hover:bg-emerald-700 transition">
            Apply Filter
          </button>
        </form>
      </div>

    </div>
  </div>

  <script>
    // PHP data passed to JS
    const totalKg = <?php echo $totalKg; ?>;
    const totalMeals = <?php echo $totalMeals; ?>;
    const co2Saved = <?php echo $co2Saved; ?>;
    const waterSaved = <?php echo $waterSaved; ?>;
    const labels = <?php echo $labels; ?>;
    const foodData = <?php echo $foodData; ?>;

    // Impact Overview (Bar Chart)
    new Chart(document.getElementById('impactChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: ['Food Saved (kg)', 'Meals Served', 'CO₂ Avoided (kg)', 'Water Saved (kL)'],
        datasets: [{
          label: 'Impact',
          data: [totalKg, totalMeals, co2Saved, waterSaved],
          backgroundColor: [
            'rgba(34,197,94,0.7)',
            'rgba(59,130,246,0.7)',
            'rgba(234,179,8,0.7)',
            'rgba(139,92,246,0.7)'
          ],
          borderRadius: 12
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    // Line Chart
    new Chart(document.getElementById('lineChart').getContext('2d'), {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Food Saved (kg)',
          data: foodData,
          borderColor: 'rgba(34,197,94,1)',
          backgroundColor: 'rgba(34,197,94,0.2)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top'
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    // Pie Chart
    new Chart(document.getElementById('pieChart').getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: ['Food Saved (kg)', 'Meals Served', 'CO₂ Avoided (kg)', 'Water Saved (kL)'],
        datasets: [{
          data: [totalKg, totalMeals, co2Saved, waterSaved],
          backgroundColor: [
            'rgba(34,197,94,0.7)',
            'rgba(59,130,246,0.7)',
            'rgba(234,179,8,0.7)',
            'rgba(139,92,246,0.7)'
          ],
          hoverOffset: 12
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom'
          }
        },
        cutout: '55%'
      }
    });
  </script>
</body>

</html>