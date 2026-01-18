<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

use App\Model\User;
use App\Service\Wallet;


$pdo = getDb();
$userModel = new User($pdo);
$user = $userModel->findByEmail($_SESSION['user']['Email'] ?? '');

$wallet = ($user && $user['SteamId']) ? $userModel->getPlayerWallet($user['SteamId']) : null;
$walletService = new Wallet($pdo, $user['SteamId']);
$balance = $walletService->getBalance();


?>
<h2 class="mb-4"><?= $pageTitle ?></h2>

<div class="card mb-4">
    <div class="card-body">
        <?php if (!empty($_SESSION['topup_success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['topup_success'] ?></div>
            <?php unset($_SESSION['topup_success']); ?>
        <?php elseif (!empty($_SESSION['topup_error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['topup_error'] ?></div>
            <?php unset($_SESSION['topup_error']); ?>
        <?php endif; ?>
        <p><strong>Имя:</strong> <?= htmlspecialchars($user['Name'] ?? '-') ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['Email'] ?? '-') ?></p>
        <p><strong>SteamID:</strong> <?= htmlspecialchars($user['SteamId'] ?? '—') ?></p>
        <?php if ($wallet): ?>
            <hr>
            <p><strong>Баланс:</strong> <?= $wallet['Points'] ?> 💰</p>
            <p><strong>Потрачено всего:</strong> <?= $wallet['TotalSpent'] ?> 💸</p>
            <p><strong>Последний вход:</strong> <?= date('d.m.Y H:i', (int) $wallet['LastTime']) ?></p>
        <?php else: ?>
            <p class="text-warning">Данные о кошельке отсутствуют.</p>
        <?php endif; ?>
    </div>
</div>

<a href="/password.php" class="btn btn-outline-primary">Сменить пароль</a>

<h3 class="mb-3">💳 Пополнить баланс</h3>

<form method="post" action="/wallet-tupup.php" class="row g-2 align-items-center" style="max-width: 300px;">
    <div class="col-8">
        <input type="number" name="amount" class="form-control" placeholder="Сколько арков?" min="40" required>
    </div>
    <div class="col-4">
        <button type="submit" class="btn btn-primary w-150">Пополнить</button>
    </div>
</form>