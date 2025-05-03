<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\TokenManager;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$tokenManager = new TokenManager($_ENV);

try {
    $tokenManager->getValidTokens();
    echo 'Token refreshed';
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage();
} 