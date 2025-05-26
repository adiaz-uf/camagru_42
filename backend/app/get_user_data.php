<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}
require 'conexion.php'; 

try {
    $stmt = $pdo->prepare("SELECT notifications FROM user WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit();
    }

    echo json_encode([
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'notifications' => (int)$row['notifications']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>