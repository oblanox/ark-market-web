<?php

use App\Config\Config;
?>

<h1 class="mb-4">Добро пожаловать в ARK Market!</h1>

<?php if ($isLoggedIn): ?>
    <div class="alert alert-success">
        <?php if ($user['SteamId'] == Config::ADMIN_STEAM_ID) : ?>
            Вы зашли как ADMIN
        <?php endif ?>
        Привет, <strong><?= htmlspecialchars($user['Name']) ?></strong>!
    </div>

    <p>Перейти в <a href="/profile.php" class="btn btn-outline-primary btn-sm">Профиль</a></p>
    <form action="/logout.php" method="post" class="d-inline">
        <button type="submit" class="btn btn-outline-danger btn-sm">Выйти</button>
    </form>
<?php else: ?>
    <p>
        <a href="/login.php" class="btn btn-primary me-2">Войти</a>
        <a href="/register-user.php" class="btn btn-outline-secondary">Создать аккаунт</a>
    </p>
<?php endif; ?>