<?php
include "db_connect.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $provider_id      = $_SESSION['user_id'] ?? 0;
    $food_title       = $_POST['food_title'];
    $food_description = $_POST['food_description'];
    $food_type        = $_POST['food_type']; // 'Veg' or 'Non-Veg'
    $quantity         = $_POST['quantity'];
    $quantity_unit    = $_POST['quantity_unit'] ?? 'pcs';
    $price            = $_POST['price'];
    $pickup_location  = $_POST['pickup_location'];
    $available_until  = $_POST['available_until']; // 'YYYY-MM-DD HH:MM:SS'

    // Compute dynamic freshness
    $current_time = new DateTime();
    $available_until_dt = new DateTime($available_until);

    // Assuming listing was just created now
    $total_duration = $available_until_dt->getTimestamp() - $current_time->getTimestamp();
    $elapsed = 0; // just created, so elapsed = 0

    $freshness_status = 'Fresh'; // default
    if ($total_duration > 0) {
        $percentage_elapsed = ($elapsed / $total_duration) * 100;

        if ($percentage_elapsed <= 50) {
            $freshness_status = 'Fresh';
        } elseif ($percentage_elapsed <= 80) {
            $freshness_status = 'Good';
        } else {
            $freshness_status = 'Near Expiry';
        }
    } else {
        $freshness_status = 'Expired';
    }

    // Prepare SQL
    $stmt = $conn->prepare("
        INSERT INTO food_listings 
        (provider_id, food_title, food_description, food_type, quantity, quantity_unit, price, freshness_status, available_until, pickup_location) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "isssisisss",
        $provider_id,
        $food_title,
        $food_description,
        $food_type,
        $quantity,
        $quantity_unit,
        $price,
        $freshness_status,
        $available_until,
        $pickup_location
    );

  if ($stmt->execute()) {
        echo "<script>
            alert('Listing added successfully!');
            window.location.href = 'provider_dashboard.php#browseSection';
        </script>";

    } else {
        echo "<script>
                alert('Error: " . addslashes($stmt->error) . "');
                window.location.href = 'provider_dashboard.php#addFormSection';
              </script>";
    }
    $listing_id = $conn->insert_id; // new listing id

    $notifSql = "
    INSERT INTO notifications (recipient_id, listing_id, message)
    SELECT id AS recipient_id, ? AS listing_id, CONCAT('New listing added: ', ?) AS message
    FROM users
    WHERE role = 'recipient'
    ";
    $notifStmt = $conn->prepare($notifSql);
    $notifStmt->bind_param('is', $listing_id, $food_title);
    $notifStmt->execute();
    $notifStmt->close();


    $stmt->close();
    $conn->close();
}
?>
