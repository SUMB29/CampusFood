<?php
require_once "db_connect.php"; // gives $conn (MySQLi)

// Check if POST data exists
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['user'] ?? '');
    $email    = trim($_POST['mail'] ?? '');
    $password = $_POST['passwd'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    $role1    = $_POST['role1'] ?? '';
    $subrole  = $_POST['subrole'] ?? '';

    // 1. Validate inputs
    if (empty($username) || empty($email) || empty($password) || empty($role1) || empty($subrole)) {
        die("❌ All fields are required!");
    }

    // 2. Check email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("❌ Invalid email format!");
    }

    // 3. Check password match
    if ($password !== $confirm) {
        die("❌ Passwords do not match!");
    }

    // 4. Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 5. Check if username/email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        die("❌ Username or Email already exists!");
    }
    $check->close();

    // 6. Insert new user
    $sql = "INSERT INTO users (username, email, password, role, subrole) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("❌ Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sssss", $username, $email, $hashedPassword, $role1, $subrole);

    if ($stmt->execute()) {
        header("Location: ../login.html");
    } else {
        echo "❌ Insert failed: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "No POST data received!";
}
?>
