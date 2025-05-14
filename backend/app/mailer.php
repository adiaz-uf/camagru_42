<?php

require __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendConfirmationEmail($toEmail, $confirmUrl, $tousername) {
   $mail = new PHPMailer(true); // Instancia de PHPMailer

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();                                
        $mail->Host = 'sandbox.smtp.mailtrap.io';                  
        $mail->SMTPAuth = true;                                // Activar la autenticación SMTP
        $mail->Username = '4ecdf08ed42372';                   // Nombre de usuario
        $mail->Password = '13ef556f4619f7';                      // Contraseña del usuario
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;    // Seguridad
        $mail->Port = 587;                                    // Puerto SMTP

        // Remitente y receptor
        $mail->setFrom('no-reply@camagru.com', 'Camagru');
        $mail->addAddress($toEmail, $tousername);

        // Contenido del correo
        $mail->isHTML(true);                                 
        $mail->Subject = 'Validate email';
        $mail->Body = "
                <p>Hello {$tousername},</p>
                <p>Please click the following link to confirm your email address:</p>
                <p><a href=\"{$confirmUrl}\">Confirm your email</a></p>
                <p>If you did not register, please ignore this email.</p>
            ";

        $mail->send();                                       
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
