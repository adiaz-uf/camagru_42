<?php
require 'conexion.php';
require __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email']);

$stmt = $pdo->prepare("SELECT id, username FROM user WHERE email = :email");
$stmt->bindParam(':email', $email);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Email not found.']);
    exit();
}

$token = bin2hex(random_bytes(32));
#$resetUrl = "{$protocol}{$host}/frontend/html/new_password.html?token=$token";
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST']; // IP o dominio dinámico

$resetUrl = "https://{$host}/html/new-password.html?token=$token";

$stmt = $pdo->prepare("UPDATE user SET reset_token = :token WHERE email = :email");
$stmt->bindParam(':token', $token);
$stmt->bindParam(':email', $email);
$stmt->execute();

// Send email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'sandbox.smtp.mailtrap.io';
    $mail->SMTPAuth = true;
    $mail->Username = '4ecdf08ed42372';
    $mail->Password = '13ef556f4619f7';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('no-reply@camagru.com', 'Camagru');
    $mail->addAddress($email, $user['username']);

    $mail->isHTML(true);
    $mail->Subject = 'Reset Your Password';
    $mail->Body = "
        <p>Hello {$user['username']},</p>
        <p>Click the link below to reset your password:</p>
        <p><a href='{$resetUrl}'>Reset Password</a></p>
        <p>If you didn't request this, just ignore this email.</p>
    ";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Reset link sent to your email.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to send email.']);
}
?>