<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

use App\Model\User;

$pdo = getDb();
$userModel = new User($pdo);
$user = $userModel->findByEmail($_SESSION['user']['Email'] ?? '');

$wallet = ($user && $user['SteamId']) ? $userModel->getPlayerWallet($user['SteamId']) : null;
?>
<h2 class="mb-4"><?= $pageTitle ?></h2>

<div class="card mb-4">
    <div class="card-body">
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

<a href="/change-password.php" class="btn btn-outline-primary">Сменить пароль</a>