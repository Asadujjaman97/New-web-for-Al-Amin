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

// Read form data
$name    = trim($_POST['name'] ?? "");
$email   = trim($_POST['email'] ?? "");
$phone   = trim($_POST['phone'] ?? "");
$subject = trim($_POST['subject'] ?? "");
$message = trim($_POST['message'] ?? "");

if ($name === "" || $email === "" || $subject === "" || $message === "") {
    echo json_encode(["status"=>"error","message"=>"Missing required fields"]);
    exit;
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = "al-amin.com.bd";
    $mail->SMTPAuth   = true;
    $mail->Username   = "info@al-amin.com.bd";
    $mail->Password   = "Chanmia@9127!";
    $mail->SMTPSecure = "ssl";
    $mail->Port       = 465;

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

    // echo json_encode(["status"=>"success","message"=>"Mail sent"]);
    echo json_encode([
    "status" => "success",
    "message" => "Mail sent"
]);
exit;
}
catch (Exception $e) {
    echo json_encode(["status"=>"error","message"=>$mail->ErrorInfo]);
}
