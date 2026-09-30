<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Twilio\Rest\Client;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $pdo = new PDO(
        sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $_ENV['DB_HOST'],
            $_ENV['DB_NAME']
        ),
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

$twilio = new Client(
    $_ENV['TWILIO_ACCOUNT_SID'],
    $_ENV['TWILIO_AUTH_TOKEN']
);

function redirect(string $location): never
{
    header("Location: $location");
    exit;
}

function e(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_QUOTES,
        'UTF-8'
    );
}