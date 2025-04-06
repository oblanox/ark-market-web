<?php
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

if (!function_exists('getDb')) {
    function getDb(): PDO {
        static $pdo = null;

        if ($pdo === null) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->load();

            $pdo = new PDO(
                "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
                $_ENV['DB_USER'],
                $_ENV['DB_PASS']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $pdo;
    }
}
