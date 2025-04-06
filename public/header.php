<?php

use App\Service\Wallet;

$isLoggedIn = isset($_SESSION['user']);
$user = $_SESSION['user'] ?? null;
$balance = $isLoggedIn ? Wallet::getBalance() : 0;
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">ARK Market</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
            aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Переключить навигацию">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a class="nav-link" href="/profile.php">Профиль</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="productsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Товары
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="productsDropdown">
                        <li><a class="dropdown-item" href="#">Предметы</a></li>
                        <li><a class="dropdown-item" href="/dino.php">Дино</a></li>
                        <li><a class="dropdown-item" href="#">Услуги</a></li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/cart.php">Корзина</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/order.php">Ждёт доставки</a>
                </li>

            </ul>

            <ul class="navbar-nav">
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item d-flex align-items-center text-white me-3">
                        💰 <?= $balance ?> арков
                    </li>
                    <li class="nav-item">
                        <span class="navbar-text me-2">Привет, <?= htmlspecialchars($user['Name']) ?></span>
                    </li>
                    <li class="nav-item">
                        <form method="post" action="/logout.php" class="d-inline">
                            <button class="btn btn-outline-light btn-sm">Выйти</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm" href="/login.php">Войти</a>
                    </li>
                <?php endif; ?>
            </ul>


        </div>
    </div>
</nav>