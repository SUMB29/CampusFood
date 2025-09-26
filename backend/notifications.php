<?php
/**
 * GET /backend/notifications.php
 * Returns the current recipient user's notifications and unread count.
 * Requires an active PHP session with $_SESSION['user_id'] and role 'recipient'.
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'recipient') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once 'db_connect.php';

$user_id = (int)$_SESSION['user_id'];
$limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 20;

// Fetch latest notifications for this recipient
$stmt = $conn->prepare("
    SELECT id, listing_id, message, is_read, created_at
    FROM notifications
    WHERE recipient_id = ?
    ORDER BY created_at DESC, id DESC
    LIMIT ?
");
$stmt->bind_param("ii", $user_id, $limit);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Count unread
$stmt2 = $conn->prepare("SELECT COUNT(*) AS unread FROM notifications WHERE recipient_id = ? AND is_read = 0");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$unread = ($stmt2->get_result()->fetch_assoc() ?? ['unread' => 0])['unread'];
$stmt2->close();

echo json_encode(['success' => true, 'unread' => (int)$unread, 'notifications' => $rows]);
