<?php
session_start();
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

$submittedCode = trim($_POST['otp'] ?? "");
$pending = $_SESSION['investment_otp'] ?? null;

if (!$pending) {
    echo json_encode(["status" => "error", "message" => "No verification in progress. Please request a new code."]);
    exit;
}

if (time() > $pending['expires']) {
    unset($_SESSION['investment_otp']);
    echo json_encode(["status" => "error", "message" => "Code expired. Please request a new one."]);
    exit;
}

if (!hash_equals($pending['code'], $submittedCode)) {
    echo json_encode(["status" => "error", "message" => "Incorrect code. Please try again."]);
    exit;
}

unset($_SESSION['investment_otp']); // one-time use

echo json_encode(["status" => "success"]);
