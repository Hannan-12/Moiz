<?php
// api/submit-details.php

// Load Composer's autoloader
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- 🚨 HARDCODED SENSITIVE VALUES - VERY RISKY! ---
$EMAIL_USER = 'apaynet@aol.com'; // Hardcoded Email User
$EMAIL_PASS = 'YOUR_AOL_APP_PASSWORD_GOES_HERE'; // Hardcoded AOL App Password
// --- End of Hardcoded Values ---

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method Not Allowed');
}

if (!$EMAIL_USER || !$EMAIL_PASS) {
     http_response_code(500);
     die('Error: Email configuration is missing on the server.');
}

// Get form data
$details = $_POST;
$idFile = $_FILES['idUpload'] ?? null;

if (!$idFile || $idFile['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    die('Error: No ID file was uploaded or an error occurred during upload.');
}

// Sanitize inputs for display
$fullName = htmlspecialchars($details['fullName'] ?? 'Not provided');
$dob = htmlspecialchars($details['dob'] ?? 'Not provided');
$contact = htmlspecialchars($details['contact'] ?? 'Not provided');
$email = htmlspecialchars($details['email'] ?? 'Not provided');
$bankName = htmlspecialchars($details['bankName'] ?? 'Not provided');
$branch = htmlspecialchars($details['branch'] ?? 'N/A');
$country = htmlspecialchars($details['country'] ?? 'Not provided');
$currency = htmlspecialchars($details['currency'] ?? 'Not provided');
$iban = htmlspecialchars($details['iban'] ?? 'Not provided');
$remarks = htmlspecialchars($details['remarks'] ?? 'N/A');

$emailBody = "
    <h1>New Beneficiary Submission</h1>
    <p>A payment was completed and the beneficiary has submitted their details.</p>
    <hr>
    <h2>Beneficiary Personal Details</h2>
    <p><strong>Full Name:</strong> $fullName</p>
    <p><strong>Date of Birth:</strong> $dob</p>
    <p><strong>Contact Number:</strong> $contact</p>
    <p><strong>Email:</strong> $email</p>
    <hr>
    <h2>Beneficiary Bank Details</h2>
    <p><strong>Bank Name:</strong> $bankName</p>
    <p><strong>Branch:</strong> $branch</p>
    <p><strong>Country:</strong> $country</p>
    <p><strong>Currency:</strong> $currency</p>
    <p><strong>IBAN:</strong> $iban</p>
    <hr>
    <p><strong>Remarks:</strong> $remarks</p>
    <br>
    <p>The user's ID file is attached to this email.</p>
";

$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.aol.com'; // AOL SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = $EMAIL_USER;
    $mail->Password   = $EMAIL_PASS;      // Use your AOL App Password here
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    //Recipients
    $mail->setFrom($EMAIL_USER, 'A-Payments Network');
    $mail->addAddress($EMAIL_USER); // Send to yourself

    //Attachments
    $mail->addAttachment(
        $idFile['tmp_name'], 
        $idFile['name']
    );

    //Content
    $mail->isHTML(true);
    $mail->Subject = 'New Beneficiary Submission - ' . $fullName;
    $mail->Body    = $emailBody;

    $mail->send();
    
    // Redirect to a success page (assuming it's at the root)
    header('Location: /?submission=success');
    exit;

} catch (Exception $e) {
    http_response_code(500);
    die("Error processing your request. Please contact support. Mailer Error: {$mail->ErrorInfo}");
}
?>