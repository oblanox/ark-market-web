<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Service\Cart;

session_start();

$key = $_POST['key'] ?? '';
if ($key) {
    Cart::removeItem($key);
}

header('Location: /cart.php');
exit;
