<?php
// api/create-payment.php

// --- 🚨 HARDCODED SENSITIVE VALUES ---
$NOWPAYMENTS_PRIVATE_KEY = '06PYHFE-FM6MH8Z-M5PYY98-EWPN3MJ'; // Hardcoded API Key
$YOUR_DOMAIN = 'https://apaymentsnetwork.cloud'; // ✅ UPDATED to your correct domain
// --- End of Hardcoded Values ---

// --- 💡 CONFIGURATION ---
// Set this to the folder you created in 'public_html' on Hostinger
// If your URL is apaymentsnetwork.cloud/public/
// then $PROJECT_FOLDER = '';
// If it's apaymentsnetwork.cloud/payment-project/public/
// then $PROJECT_FOLDER = '/payment-project';
$PROJECT_FOLDER = ''; // Or '/payment-project'

// --- End of Configuration ---

header('Content-Type: application/json');

// Get the posted JSON data
$input = json_decode(file_get_contents('php://input'), true);

$price_amount = $input['price_amount'] ?? null;
$pay_currency = $input['pay_currency'] ?? null;

if (!$price_amount || !$pay_currency) {
    http_response_code(400);
    echo json_encode(['message' => 'Missing amount or currency']);
    exit;
}

if (!$NOWPAYMENTS_PRIVATE_KEY || !$YOUR_DOMAIN) {
    http_response_code(500);
    echo json_encode(['message' => 'Server configuration error.']);
    exit;
}

// Construct the full URLs including the project folder and public folder
$base_url = rtrim($YOUR_DOMAIN, '/') . rtrim($PROJECT_FOLDER, '/');

$requestBody = json_encode([
    'price_amount' => (float)$price_amount,
    'price_currency' => 'usd',
    'pay_currency' => $pay_currency,
    'ipn_callback_url' => $base_url . '/api/payment-webhook.php',
    'success_url' => $base_url . '/public/beneficiary-form.php'
]);

try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.nowpayments.io/v1/invoice');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . $NOWPAYMENTS_PRIVATE_KEY,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    if ($http_status != 200 && $http_status != 201) {
         throw new Exception($data['message'] ?? 'Failed to create invoice (Status: ' . $http_status . ')');
    }

    // Success
    echo json_encode(['invoiceUrl' => $data['invoice_url']]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => $e.getMessage()]);
}
?>

