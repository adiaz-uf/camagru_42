<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'conexion.php';

if (!isset($_GET['token'])) {
    die('Invalid confirmation link.');
}

$token = $_GET['token'];

try {
    $stmt = $pdo->prepare("SELECT id FROM user WHERE confirm_token = :token AND confirmed = 0");
    $stmt->bindParam(':token', $token);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Set user confirmed status to True
        $stmt = $pdo->prepare("UPDATE user SET confirmed = 1, confirm_token = NULL WHERE id = :id");
        $stmt->bindParam(':id', $user['id']);
        $stmt->execute();

        echo "Account confirmed successfully. You can now <a href='/login.html'>login</a>.";
    } else {
        echo "Invalid or already used confirmation link.";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
