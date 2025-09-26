<?php
/**
 * POST /backend/notifications_mark_read.php
 * Marks all notifications for the current recipient as read.
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'recipient') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/db_connect.php';

$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("
    UPDATE notifications 
    SET is_read = 1, read_at = NOW()
    WHERE recipient_id = ? AND is_read = 0
");
$stmt->bind_param("i", $user_id);
$ok = $stmt->execute();
$stmt->close();

echo json_encode(['success' => $ok ? true : false]);
