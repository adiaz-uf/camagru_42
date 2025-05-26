<?php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['notifications'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing notifications value']);
    exit();
}

$notifications = $input['notifications'] ? 1 : 0;

try {
    $stmt = $pdo->prepare("UPDATE user SET notifications = :notifications WHERE id = :id");
    $stmt->execute([
        'notifications' => $notifications,
        'id' => $_SESSION['user_id']
    ]);

    echo json_encode(['success' => true, 'message' => 'Notifications updated']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}