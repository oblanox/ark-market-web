<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Service\Cart;

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Метод не разрешён";
    exit;
}

$id     = (int) ($_POST['id'] ?? 0);
$type   = trim($_POST['type'] ?? '');
$name   = trim($_POST['name'] ?? '');
$image  = trim($_POST['image'] ?? '');
$price  = (int) ($_POST['price'] ?? 0);
$params = trim($_POST['params'] ?? '');
$qty    = (int) ($_POST['qty'] ?? 1);

if (!$id || !$type || !$name || !$image || $price <= 0 || $qty <= 0) {
    http_response_code(400);
    echo "Неверные данные";
    exit;
}

Cart::addItem($id, $type, $name, $image, $price, $params, $qty);
header("Location: /cart.php");
exit;
