<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'];
$db = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASS'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $steamId = '76561198000000000'; // Заменить на существующий SteamID

    $stmt = $pdo->prepare("SELECT SteamId, Points, TotalSpent, LastTime FROM ArkShopPlayers WHERE SteamId = ?");
    $stmt->execute([$steamId]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo "✅ Найден игрок:\n";
        print_r($result);
    } else {
        echo "❌ Игрок с таким SteamID не найден.";
    }

} catch (PDOException $e) {
    echo "Ошибка подключения к БД: " . $e->getMessage();
}
