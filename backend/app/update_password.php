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
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];

    if (empty($oldPassword) || empty($newPassword)) {
        echo json_encode(['success' => false, 'message' => 'All password fields are required']);
        exit();
    }

    $stmt = $pdo->prepare("SELECT password FROM user WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($oldPassword, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Incorrect old password']);
        exit();
    }

    if (password_verify($newPassword, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'New password must be different from the old password']);
        exit();
    }

    if (!preg_match('/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/', $newPassword)) {
    echo json_encode(['success' => false, 'message' => 'Password does not meet security requirements. Must contain at least 1 number, 1 uppercase, 1 lowercase and be 8+ characters long.']);
    exit();
    }

    $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE user SET password = :password WHERE id = :id");
    $stmt->execute([':password' => $newHashedPassword, ':id' => $_SESSION['user_id']]);

    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
?>