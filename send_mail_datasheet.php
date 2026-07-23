<?php
// file_put_contents('debug_post.txt', print_r($_POST, true));
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require __DIR__ . '/mail-config.php';

header("Content-Type: text/plain");

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $mail_host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $mail_username;
    $mail->Password   = $mail_password;
    $mail->SMTPSecure = $mail_smtp_secure;
    $mail->Port       = $mail_port;

    $mail->setFrom('info@al-amin.com.bd', 'Data Sheet Request');
    $mail->addAddress('info@al-amin.com.bd');

    // Get form data safely
    $name    = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email   = htmlspecialchars(trim($_POST['email'] ?? ''));
    $company = htmlspecialchars(trim($_POST['company'] ?? ''));
    $msg     = htmlspecialchars(trim($_POST['message'] ?? ''));
    $pdf     = htmlspecialchars(trim($_POST['pdf'] ?? ''));
    $phone   = htmlspecialchars(trim($_POST['mobile'] ?? 'Not provided'));

    error_log("Captured phone: " . $phone); // Debug log

    $mail->isHTML(true);
    $mail->Subject = "New Data Sheet Request";

    $mail->Body = "
        <h3>New Technical Data Sheet Request</h3>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Phone:</strong> $phone</p>
        <p><strong>Company:</strong> $company</p>
        <p><strong>Message:</strong> $msg</p>
        <p><strong>Requested PDF:</strong> $pdf</p>
    ";

    $mail->send();
    echo "OK";
    exit;

} catch (Exception $e) {
    echo "ERROR: " . $mail->ErrorInfo;
    exit;
}
?>

