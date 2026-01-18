<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

use App\Service\Wallet;


if (!$isLoggedIn) {
    header('Location: /login.php');
    exit;
}

$pdo = getDb();
$user = $_SESSION['user'];
$steamId = $user['SteamId'];

$amount = (int) ($_POST['amount'] ?? 0);

if ($amount <= 0) {
    $_SESSION['topup_error'] = 'Сумма должна быть больше 0.';
    header('Location: /profile.php');
    exit;
}

$wallet = new Wallet($pdo, $steamId);
$wallet->add($pdo, $steamId, $amount);

$_SESSION['topup_success'] = "Баланс пополнен на $amount арков.";
header('Location: /profile.php');
exit;
