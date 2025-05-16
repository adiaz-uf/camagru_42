<?php
session_start();
require 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username']);

    if (empty($newUsername)) {
        echo json_encode(['success' => false, 'message' => 'Username cannot be empty']);
        exit();
    }

    // Check if username already exists (excluding current user)
    $stmt = $pdo->prepare("SELECT id FROM user WHERE username = :username AND id != :id");
    $stmt->execute([':username' => $newUsername, ':id' => $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        exit();
    }

    // Update username
    $stmt = $pdo->prepare("UPDATE user SET username = :username WHERE id = :id");
    $stmt->execute([':username' => $newUsername, ':id' => $_SESSION['user_id']]);

    $_SESSION['username'] = $newUsername;

    echo json_encode(['success' => true, 'message' => 'Username updated successfully']);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
?>