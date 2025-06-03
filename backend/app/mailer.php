<?php
require __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendConfirmationEmail($toEmail, $confirmUrl, $tousername) {
   $mail = new PHPMailer(true);

    try {
        // SMTP server conf
        $mail->isSMTP();                                
        $mail->Host = getenv('MAIL_HOST');
        $mail->Port = getenv('MAIL_PORT');
        $mail->Username = getenv('MAIL_USERNAME');
        $mail->Password = getenv('MAIL_PASSWORD');         
        $mail->SMTPAuth = true;                               
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 

        
        $mail->setFrom(getenv('MAIL_FROM_ADDRESS'), getenv('MAIL_FROM_NAME'));
        $mail->addAddress($toEmail, $tousername);

        // mail content
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
