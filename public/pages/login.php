<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

use App\Model\User;
use App\Service\Wallet as ServiceWallet;

session_start();

$pdo = getDb();
$userModel = new User($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $user = $userModel->findByEmail($email);

    if ($user && password_verify($password, $user['Password'])) {
        $_SESSION['user'] = $user;
        ServiceWallet::refresh($pdo, $user['SteamId']);
        header('Location: /index.php');
        exit;
    } else {
        $error = 'Неверный email или пароль.';
    }
}
?>
<h2 class="mb-4"><?= $pageTitle ?></h2>
<?php if ($error): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <li><?= htmlspecialchars($error) ?></li>
        </ul>
    </div>
<?php endif; ?>
<form method="post">
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($email ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Пароль</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Войти</button>
</form>