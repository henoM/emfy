<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();


file_put_contents(__DIR__ . '/webhook.log', date('Y-m-d H:i:s') . " - Callback received with code: " . ($_GET['code'] ?? 'no code') . "\n", FILE_APPEND);

if (!isset($_GET['code'])) {
    echo 'No authorization code provided.';
    exit;
}

$code = $_GET['code'];

try {
    $amo = new AmoAPI();
    
    $tokens = $amo->makeRequest('POST', '/oauth2/access_token', [
        'client_id' => $_ENV['AMOCRM_CLIENT_ID'],
        'client_secret' => $_ENV['AMOCRM_CLIENT_SECRET'],
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => $_ENV['AMOCRM_REDIRECT_URI'],
    ]);

    file_put_contents(__DIR__ . '/webhook.log', date('Y-m-d H:i:s') . " - Received tokens: " . print_r($tokens, true) . "\n", FILE_APPEND);
    
    if (!isset($tokens['access_token']) || !isset($tokens['refresh_token'])) {
        throw new Exception('Invalid token response');
    }
    
    echo 'Tokens received and stored successfully.';
} catch (Exception $e) {
    file_put_contents(__DIR__ . '/webhook.log', date('Y-m-d H:i:s') . " - Error exchanging code for tokens: " . $e->getMessage() . "\n", FILE_APPEND);
    echo 'Error exchanging code for tokens: ' . $e->getMessage();
}
