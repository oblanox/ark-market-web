<?php
$pagePath = $pagePath ?? null;
$pageTitle = $pageTitle ?? 'ARK Market';
session_start();
$isLoggedIn = isset($_SESSION['user']);
$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php require_once __DIR__ . '/header.php'; ?>
    <main class="container my-4">
        <?php
        if ($pagePath && file_exists($pagePath)) {
            include $pagePath;
        } else {
            echo "<div class='alert alert-danger'>Ошибка: страница не найдена.</div>";
        }
        ?>
    </main>
    <?php require_once __DIR__ . '/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
</body>

</html>