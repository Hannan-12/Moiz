<?php
// api/payment-webhook.php

// Get the raw POST data
$paymentData = file_get_contents('php://input');

// Log the data to a file for review
$logMessage = "--- Webhook Received: " . date("Y-m-d H:i:s") . " ---\n";
$logMessage .= $paymentData . "\n\n";

// Append to a log file (make sure your server has write permissions here)
file_put_contents('webhook_log.txt', $logMessage, FILE_APPEND);

// Respond to NOWPayments to acknowledge receipt
http_response_code(200);
echo 'Webhook received';
?>