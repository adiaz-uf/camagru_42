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
    $newEmail = trim($_POST['email']);

    if (empty($newEmail)) {
        echo json_encode(['success' => false, 'message' => 'Email cannot be empty']);
        exit();
    }

    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit();
    }

    // Check if email already exists (excluding current user)
    $stmt = $pdo->prepare("SELECT id FROM user WHERE email = :email AND id != :id");
    $stmt->execute([':email' => $newEmail, ':id' => $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit();
    }

    // Update email
    $stmt = $pdo->prepare("UPDATE user SET email = :email WHERE id = :id");
    $stmt->execute([':email' => $newEmail, ':id' => $_SESSION['user_id']]);

    $_SESSION['email'] = $newEmail;

    echo json_encode(['success' => true, 'message' => 'Email updated successfully']);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
?>