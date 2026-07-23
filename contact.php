<?php
header("Content-Type: application/json");

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status"=>"error","message"=>"Invalid request"]);
    exit;
}

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/phpmailer/src/Exception.php";
require __DIR__ . "/phpmailer/src/PHPMailer.php";
require __DIR__ . "/phpmailer/src/SMTP.php";
require __DIR__ . "/mail-config.php";

// Read form data
$name    = trim($_POST['name'] ?? "");
$email   = trim($_POST['email'] ?? "");
$phone   = trim($_POST['phone'] ?? "");
$subject = trim($_POST['subject'] ?? "");
$message = trim($_POST['message'] ?? "");

if ($name === "" || $email === "" || $phone === "" || $subject === "" || $message === "") {
    echo json_encode(["status"=>"error","message"=>"Missing required fields"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status"=>"error","message"=>"Invalid email address"]);
    exit;
}

// Basic international phone sanity check (real per-country validation is done client-side)
if (!preg_match('/^\+?[0-9\s\-]{7,20}$/', $phone)) {
    echo json_encode(["status"=>"error","message"=>"Invalid phone number"]);
    exit;
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $mail_host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $mail_username;
    $mail->Password   = $mail_password;
    $mail->SMTPSecure = $mail_smtp_secure;
    $mail->Port       = $mail_port;

    $mail->setFrom("info@al-amin.com.bd", "Website Contact");
    $mail->addAddress("info@al-amin.com.bd");

    $mail->isHTML(true);
    $mail->Subject = "New Contact Form Submission";

    $mail->Body = "
        <h3>Contact Form Details</h3>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Phone:</strong> $phone</p>
        <p><strong>Subject:</strong> $subject</p>
        <p><strong>Message:</strong><br>$message</p>
    ";

    $mail->send();

    // Confirmation email back to the person who submitted the form
    try {
        $confirmation = new PHPMailer(true);
        $confirmation->isSMTP();
        $confirmation->Host       = $mail_host;
        $confirmation->SMTPAuth   = true;
        $confirmation->Username   = $mail_username;
        $confirmation->Password   = $mail_password;
        $confirmation->SMTPSecure = $mail_smtp_secure;
        $confirmation->Port       = $mail_port;

        $confirmation->setFrom("info@al-amin.com.bd", "Al-Amin Traders");
        $confirmation->addAddress($email, $name);

        $confirmation->isHTML(true);
        $confirmation->Subject = "We've received your message — Al-Amin Traders";
        $confirmation->Body = "
            <h3>Thank you, $name!</h3>
            <p>We've received your message and will get back to you within 24 hours.</p>
            <p><strong>Your submission:</strong></p>
            <p><strong>Subject:</strong> $subject</p>
            <p><strong>Message:</strong><br>$message</p>
            <p>For urgent procurement inquiries, WhatsApp us at +8801711353546.</p>
            <p>— Al-Amin Traders</p>
        ";
        $confirmation->send();
    } catch (Exception $e) {
        // Don't fail the whole request if only the confirmation copy fails to send
        error_log("Confirmation email failed: " . $confirmation->ErrorInfo);
    }

    echo json_encode([
        "status" => "success",
        "message" => "Mail sent"
    ]);
    exit;
}
catch (Exception $e) {
    echo json_encode(["status"=>"error","message"=>$mail->ErrorInfo]);
}
