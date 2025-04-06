<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

use App\Service\Cart;
use App\Service\Wallet;

$pdo = getDb();

if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$user = $_SESSION['user'];
$steamId = $user['SteamId'] ?? null;

if (!$steamId) {
    die("У вас не привязан SteamID.");
}

$cart = Cart::getItems();
$total = Cart::getTotalPrice();

if (empty($cart)) {
    die("Корзина пуста.");
}

$balance = Wallet::getBalance();
if ($balance < $total) {
    die("Недостаточно средств. Нужно $total арков, у вас $balance.");
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO arkshop_orders 
        (SteamId, Type, Name, Pic, Params, Qty, Price) 
        VALUES (:steamId, :type, :name, :pic, :params, :qty, :price)");

    foreach ($cart as $item) {
        $stmt->execute([
            'steamId' => $steamId,
            'type'    => $item['type'],
            'name'    => $item['name'],
            'pic'     => $item['image'],
            'params'  => $item['params'],
            'qty'     => $item['qty'],
            'price'   => $item['price'],
        ]);
    }

    // списание баланса
    Wallet::spend($pdo, $steamId, $total);

    echo "<h3>✅ Заказ оформлен! Вы можете получить товары в игре командой /delivery</h3>";
    echo "<p><a href='/'>Вернуться на главную</a></p>";
} catch (Throwable $e) {
    $pdo->rollBack();
    echo "❌ Ошибка оформления заказа: " . htmlspecialchars($e->getMessage());
    return;
}
Cart::clear();
$pdo->commit();
