<?php
require 'conexion.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

$token = $data['token'];
$password = $data['password'];

if (!preg_match('/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/', $password)) {
    echo json_encode(['success' => false, 'message' => 'Password does not meet security requirements.']);
    exit();
}

$stmt = $pdo->prepare("SELECT id FROM user WHERE reset_token = :token");
$stmt->bindParam(':token', $token);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired token.']);
    exit();
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE user SET password = :password, reset_token = NULL WHERE id = :id");
$stmt->bindParam(':password', $hashedPassword);
$stmt->bindParam(':id', $user['id']);
$stmt->execute();

echo json_encode(['success' => true, 'message' => 'Password has been reset successfully.']);
?>