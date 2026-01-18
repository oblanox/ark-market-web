<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Service\Cart;

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Метод не разрешён";
    exit;
}

$id    = (int) ($_POST['id'] ?? 0);
$type  = trim($_POST['type'] ?? '');
$qty   = (int) ($_POST['count'] ?? 1); // количество отправляется как "count"

// Данные для отображения в корзине
$name  = trim($_POST['name'] ?? '');
$image = trim($_POST['image'] ?? '');
$price = (int) ($_POST['price'] ?? 0);


// Валидация
if (!$id || !$type || !$name || !$image || $price <= 0 || $qty <= 0) {
    http_response_code(400);
    echo "Неверные данные";
    exit;
}

// Параметры — разные для динозавров и предметов
$params = '';

if ($type === 'dino') {
    $xp       = isset($_POST['xp']) ? 'yes' : 'no';
    $gender   = $_POST['gender'] ?? 'random';
    $neutered = isset($_POST['neutered']) ? 'yes' : 'no';
    $level = (int) ($_POST['level']);

    $params = "xp:$xp|gender:$gender|neutered:$neutered|level:$level";
} elseif (in_array($type, ['resource', 'inventory', 'structure', 'skin', 'consumable'])) {
    $quality = $_POST['quality'] ?? 'Primitive';
    $params = "quality:$quality";
}

Cart::addItem($id, $type, $name, $image, $price, $params, $qty);

header("Location: /cart.php");
exit;
