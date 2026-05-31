<?php
session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'redirect' => '/student-accommodation/login.php']);
    exit;
}

$userId     = $_SESSION['user_id'];
$propertyId = isset($_POST['property_id']) ? (int)$_POST['property_id'] : 0;

if (!$propertyId) {
    echo json_encode(['success' => false, 'message' => 'Invalid property.']);
    exit;
}

// Check if already interested
$check = $pdo->prepare("SELECT 1 FROM interested_users WHERE user_id = ? AND property_id = ?");
$check->execute([$userId, $propertyId]);

if ($check->fetch()) {
    // Remove interest
    $del = $pdo->prepare("DELETE FROM interested_users WHERE user_id = ? AND property_id = ?");
    $del->execute([$userId, $propertyId]);
    echo json_encode(['success' => true, 'action' => 'removed']);
} else {
    // Add interest
    $ins = $pdo->prepare("INSERT INTO interested_users (user_id, property_id) VALUES (?, ?)");
    $ins->execute([$userId, $propertyId]);
    echo json_encode(['success' => true, 'action' => 'added']);
}
?>
