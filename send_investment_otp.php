<?php
session_start();
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/phpmailer/src/Exception.php";
require __DIR__ . "/phpmailer/src/PHPMailer.php";
require __DIR__ . "/phpmailer/src/SMTP.php";
require __DIR__ . "/mail-config.php";

$name    = trim($_POST['name'] ?? "");
$email   = trim($_POST['email'] ?? "");
$phone   = trim($_POST['phone'] ?? "");
$company = trim($_POST['company'] ?? "");
$message = trim($_POST['message'] ?? "");

if ($name === "" || $email === "" || $phone === "") {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Invalid email address"]);
    exit;
}

if (!preg_match('/^\+?[0-9\s\-]{7,20}$/', $phone)) {
    echo json_encode(["status" => "error", "message" => "Invalid phone number"]);
    exit;
}

$otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

try {
    // OTP email to the requester
    $otpMail = new PHPMailer(true);
    $otpMail->isSMTP();
    $otpMail->Host       = $mail_host;
    $otpMail->SMTPAuth   = true;
    $otpMail->Username   = $mail_username;
    $otpMail->Password   = $mail_password;
    $otpMail->SMTPSecure = $mail_smtp_secure;
    $otpMail->Port       = $mail_port;

    $otpMail->setFrom("info@al-amin.com.bd", "Al-Amin Traders");
    $otpMail->addAddress($email, $name);

    $otpMail->isHTML(true);
    $otpMail->Subject = "Your Strategic Investment Access Code";
    $otpMail->Body = "
        <h3>Hi $name,</h3>
        <p>Your verification code to access Al-Amin Traders' Strategic Investment Project profiles is:</p>
        <p style=\"font-size:28px;font-weight:bold;letter-spacing:4px;\">$otp</p>
        <p>This code expires in <strong>60 seconds</strong>. If it expires, just request a new one on the website.</p>
        <p>— Al-Amin Traders</p>
    ";
    $otpMail->send();

    // Only store the OTP once we know it actually reached the requester's inbox
    $_SESSION['investment_otp'] = [
        'code'    => $otp,
        'expires' => time() + 60,
        'email'   => $email,
        'name'    => $name,
    ];

    // Lead notification to the business (does not block the OTP response if it fails)
    try {
        $leadMail = new PHPMailer(true);
        $leadMail->isSMTP();
        $leadMail->Host       = $mail_host;
        $leadMail->SMTPAuth   = true;
        $leadMail->Username   = $mail_username;
        $leadMail->Password   = $mail_password;
        $leadMail->SMTPSecure = $mail_smtp_secure;
        $leadMail->Port       = $mail_port;

        $leadMail->setFrom("info@al-amin.com.bd", "Website Investment Inquiry");
        $leadMail->addAddress("info@al-amin.com.bd");

        $leadMail->isHTML(true);
        $leadMail->Subject = "New Strategic Investment Projects Access Request";
        $leadMail->Body = "
            <h3>Strategic Investment Projects — Access Request</h3>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Phone:</strong> $phone</p>
            <p><strong>Company:</strong> $company</p>
            <p><strong>Message:</strong> $message</p>
        ";
        $leadMail->send();
    } catch (Exception $e) {
        error_log("Investment lead notification failed: " . $leadMail->ErrorInfo);
    }

    echo json_encode(["status" => "success", "message" => "Verification code sent"]);
    exit;
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Could not send verification code. Please try again."]);
    exit;
}
