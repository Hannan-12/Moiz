<?php
// api/currencies.php

header('Content-Type: application/json');

$apiUrl = 'https://api.nowpayments.io/v1/currencies';

try {
    // Use file_get_contents for a simple GET request
    $response = file_get_contents($apiUrl);

    if ($response === FALSE) {
        throw new Exception('Failed to fetch currencies from NOWPayments.');
    }

    // Echo the response directly to the client
    echo $response;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => $e->getMessage()]);
}
?>