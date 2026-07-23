<?php
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

$name     = trim($_POST['name'] ?? "");
$email    = trim($_POST['email'] ?? "");
$phone    = trim($_POST['phone'] ?? "");
$position = trim($_POST['position'] ?? "");
$message  = trim($_POST['message'] ?? "");

if ($name === "" || $email === "" || $phone === "" || $position === "") {
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

if (!isset($_FILES['cv']) || $_FILES['cv']['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($_FILES['cv']['tmp_name'])) {
    echo json_encode(["status" => "error", "message" => "Please attach a valid CV file"]);
    exit;
}

$cvTmpPath = $_FILES['cv']['tmp_name'];
$cvSize    = $_FILES['cv']['size'];

$maxSize = 5 * 1024 * 1024; // 5MB
if ($cvSize > $maxSize) {
    echo json_encode(["status" => "error", "message" => "CV file must be under 5MB"]);
    exit;
}

$originalName = $_FILES['cv']['name'];
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$allowedExt = ['pdf', 'doc', 'docx'];
if (!in_array($ext, $allowedExt, true)) {
    echo json_encode(["status" => "error", "message" => "CV must be a PDF, DOC, or DOCX file"]);
    exit;
}

$allowedMime = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/zip', // some .docx files are detected as zip containers
];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$detectedMime = finfo_file($finfo, $cvTmpPath);
finfo_close($finfo);
if (!in_array($detectedMime, $allowedMime, true)) {
    echo json_encode(["status" => "error", "message" => "CV file type could not be verified"]);
    exit;
}

$safeBaseName = preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
$attachmentName = $safeBaseName . '.' . $ext;

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $mail_host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $mail_username;
    $mail->Password   = $mail_password;
    $mail->SMTPSecure = $mail_smtp_secure;
    $mail->Port       = $mail_port;

    $mail->setFrom("info@al-amin.com.bd", "Website Career Application");
    $mail->addAddress("info@al-amin.com.bd");
    $mail->addReplyTo($email, $name);

    $mail->addAttachment($cvTmpPath, $attachmentName);

    $mail->isHTML(true);
    $mail->Subject = "New Career Application — " . $position;
    $mail->Body = "
        <h3>Career Application</h3>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Phone:</strong> $phone</p>
        <p><strong>Area of Interest:</strong> $position</p>
        <p><strong>Message:</strong><br>" . nl2br($message) . "</p>
    ";

    $mail->send();

    // Confirmation email back to the applicant
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
        $confirmation->Subject = "We've received your application — Al-Amin Traders";
        $confirmation->Body = "
            <h3>Thank you, $name!</h3>
            <p>We've received your CV and application for <strong>$position</strong>. Our team will keep your details on file and reach out if a suitable opportunity arises.</p>
            <p>— Al-Amin Traders</p>
        ";
        $confirmation->send();
    } catch (Exception $e) {
        error_log("Career confirmation email failed: " . $confirmation->ErrorInfo);
    }

    echo json_encode(["status" => "success", "message" => "Application received"]);
    exit;
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Could not submit application. Please try again."]);
    exit;
}
