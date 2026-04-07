<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'carbolabs.com.bd';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'info@carbolabs.com.bd';
    $mail->Password   = 'Chanmia@9127!';  // CHANGE
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;

    $mail->setFrom('info@carbolabs.com.bd', 'Data Sheet Request');
    $mail->addAddress('info@carbolabs.com.bd');

    // Form fields
    $name    = $_POST['name'] ?? '';
    $email   = $_POST['email'] ?? '';
    $company = $_POST['company'] ?? '';
    $msg     = $_POST['message'] ?? '';
    $pdf     = $_POST['pdf'] ?? '';

    $mail->isHTML(true);
    $mail->Subject = "New Data Sheet Request";
    $mail->Body = "
        <h2>New Data Sheet Request</h2>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Company:</strong> $company</p>
        <p><strong>Message:</strong> $msg</p>
        <p><strong>PDF:</strong> $pdf</p>
    ";

    $mail->send();

    header("Location: ".$pdf);
    exit;

} catch (Exception $e) {
    echo "Mailer Error: {$mail->ErrorInfo}";
}
?>
