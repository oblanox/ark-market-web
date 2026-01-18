<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

use App\Model\User;

session_start();

$pdo = getDb();
$userModel = new User($pdo);

$errors = [];
$success = false;
exit;
// ...
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $name     = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    $token    = trim($_POST['token'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Неверный формат email.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Пароль должен быть не менее 6 символов.";
    }

    if ($password !== $confirm) {
        $errors[] = "Пароли не совпадают.";
    }

    if ($userModel->isEmailTaken($email)) {
        $errors[] = "Email уже зарегистрирован.";
    }

    if ($userModel->isNameTaken($name)) {
        $errors[] = "Имя занято.";
    }

    if (!$userModel->isTokenValid($token)) {
        $errors[] = "Неверный токен. Проверьте, выдали ли вы его себе в игре через /regshop.";
    }

    $steamId = $userModel->getSteamIdByToken($token);
    if (!$steamId) {
        $errors[] = "Ошибка получения SteamID по токену. Возможно, токен не записан или устарел.";
    }

    if (empty($errors)) {
        if ($userModel->create($email, $name, $password, $token, $steamId)) {
            $success = true;
        } else {
            $errors[] = "Ошибка регистрации. Попробуйте позже.";
        }
    }
}
?>
<h2 class="mb-4"><?= $pageTitle ?></h2>
<?php if ($success): ?>
    <div class="alert alert-success">✅ Регистрация успешна. Можете войти!</div>
<?php elseif ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post">
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($email ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Имя</label>
        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($name ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Пароль</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Повторите пароль</label>
        <input type="password" name="confirm" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Токен</label>
        <input type="text" name="token" class="form-control" required value="<?= htmlspecialchars($token ?? '') ?>">
    </div>
    <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
</form>