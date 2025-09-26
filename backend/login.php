<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost/FreshShare/index.html"); // adjust if needed
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");

include "db_connect.php";

// Read JSON input from frontend
$data = json_decode(file_get_contents("php://input"), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';
$role     = $data['role'] ?? '';
$subrole  = $data['subrole'] ?? '';

if (empty($username) || empty($password) || empty($role) || empty($subrole)) {
    echo json_encode([
        "success" => false,
        "message" => "All fields are required"
    ]);
    exit;
}

// Fetch user by email OR username and role+subrole
$sql = "SELECT id, username, email, password, role, subrole
        FROM users 
        WHERE (email = ? OR username = ?) AND role = ? AND subrole = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $username, $username, $role, $subrole);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    // ✅ Save to session
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['subrole']   = $user['subrole'];

    echo json_encode([
        "success"  => true,
        "message"  => "Login successful",
        "user_id"  => $user['id'],
        "username" => $user['username'],
        "role"     => $user['role'],
        "subrole"  => $user['subrole']
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid username, password, or role"
    ]);
}

$stmt->close();
$conn->close();
?>