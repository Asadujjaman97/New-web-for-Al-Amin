<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name       = $_POST['name'] ?? '';
    $company    = $_POST['company'] ?? '';
    $email      = $_POST['email'] ?? '';
    $phone      = $_POST['phone'] ?? '';
    $product    = $_POST['product'] === "other" ? ($_POST['other_product'] ?? '') : ($_POST['product'] ?? '');
    $quantity   = $_POST['quantity'] ?? '';
    $date       = $_POST['date'] ?? '';
    $notes      = $_POST['notes'] ?? '';

    $to = "sales@carbolabs.com.bd, info@carbolabs.com.bd";
    $subject = "New Chemical Order Received";

    $message = "
    New Order Received:

    Name: $name
    Company: $company
    Email: $email
    Phone: $phone

    Product: $product
    Quantity: $quantity
    Delivery Date: $date

    Notes: 
    $notes
    ";

    $headers = "From: noreply@carbolabs.com.bd\r\n";
    $headers .= "Reply-To: $email\r\n";

    if (mail($to, $subject, $message, $headers)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
