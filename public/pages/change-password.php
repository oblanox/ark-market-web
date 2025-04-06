<?php
require_once __DIR__ . '/../config/db.php';

use App\Model\User;
use App\Service\SessionActivity;

$pdo = getDb();
$userModel = new User($pdo);
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current'] ?? '';
    $new     = $_POST['new'] ?? '';
    $repeat  = $_POST['repeat'] ?? '';

    $user = $userModel->findByEmail($_SESSION['user']['Email']);

    $lastChange = SessionActivity::getPasswordChangeTime();
    if ($lastChange && time() - $lastChange < 300) { // 300 сек = 5 минут
        $errors[] = "Смену пароля можно производить не чаще, чем раз в 5 минут.";
    }

    if (!$user || !password_verify($current, $user['Password'])) {
        $errors[] = '❌ Неверный текущий пароль.';
    }

    if (strlen($new) < 6) {
        $errors[] = '🔒 Новый пароль должен быть не менее 6 символов.';
    }

    if ($new !== $repeat) {
        $errors[] = '🚫 Пароли не совпадают.';
    }

    if (empty($errors)) {
        $userModel->updatePassword($user['Id'], $new);
        $success = true;
    }
}
?>
<h2 class="mb-4">Смена пароля</h2>

<?php if ($success): ?>
    <div class="alert alert-success">✅ Пароль успешно изменён!</div>
<?php elseif ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" class="mt-3">
    <div class="mb-3">
        <label class="form-label">Текущий пароль</label>
        <input type="password" name="current" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Новый пароль</label>
        <input type="password" name="new" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Повторите новый пароль</label>
        <input type="password" name="repeat" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Сменить пароль</button>
    <a href="/profile.php" class="btn btn-link">← Назад в профиль</a>
</form>