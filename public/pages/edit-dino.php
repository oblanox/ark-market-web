<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

use App\Config\Config;

$pdo = getDb();

if (!$isLoggedIn || $user['SteamId'] != Config::ADMIN_STEAM_ID) {
    die("Доступ запрещён");
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    die("Некорректный ID динозавра.");
}

$stmt = $pdo->prepare("SELECT * FROM arkshop_dino WHERE Id = :id");
$stmt->execute(['id' => $id]);
$dino = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dino) {
    die("Динозавр не найден.");
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nameRU = trim($_POST['nameRU']);
    $nameEN = trim($_POST['nameEN']);
    $shortCode = trim($_POST['shortCode']);
    $pic = trim($_POST['pic']);
    $level = (int)$_POST['level'];
    $price = (int)$_POST['price'];
    $enable = isset($_POST['enable']) ? 1 : 0;
    $aber = isset($_POST['aber']) ? 1 : 0;
    $nosex = isset($_POST['nosex']) ? 1 : 0;

    if ($nameRU === '' || $shortCode === '' || $pic === '') {
        $errors[] = "Обязательные поля: Название, ShortCode, Картинка.";
    }

    if (empty($errors)) {
        if (isset($_POST['createNew'])) {
            $stmt = $pdo->prepare("INSERT INTO arkshop_dino
                (NameRU, NameEN, ShortCode, Pic, Level, Price, Enable, Aber, NoSex)
                VALUES (:nameRU, :nameEN, :shortCode, :pic, :level, :price, :enable, :aber, :nosex)");
            try {
                $stmt->execute([
                    'nameRU' => $nameRU,
                    'nameEN' => $nameEN,
                    'shortCode' => $shortCode,
                    'pic' => $pic,
                    'level' => $level,
                    'price' => $price,
                    'enable' => $enable,
                    'aber' => $aber,
                    'nosex' => $nosex,
                ]);
                $success = true;
            } catch (PDOException $e) {
                $errors[] = "Ошибка добавления: " . $e->getMessage();
            }
        } else {
            $stmt = $pdo->prepare("UPDATE arkshop_dino SET 
            NameRU = :nameRU, NameEN = :nameEN, ShortCode = :shortCode, Pic = :pic,
            Level = :level, Price = :price, Enable = :enable, Aber = :aber, NoSex = :nosex
            WHERE Id = :id");
            $stmt->execute([
                'nameRU' => $nameRU,
                'nameEN' => $nameEN,
                'shortCode' => $shortCode,
                'pic' => $pic,
                'level' => $level,
                'price' => $price,
                'enable' => $enable,
                'aber' => $aber,
                'nosex' => $nosex,
                'id' => $id
            ]);
            $success = true;
        }
    }
}
?>

<h2 class="mb-4">✏ Редактирование динозавра</h2>

<?php if ($success): ?>
    <div class="alert alert-success">✅ Сохранено</div>
<?php elseif ($errors): ?>
    <div class="alert alert-danger">
        <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="post">
    <div class="mb-3"><label>Название (RU)</label><input type="text" name="nameRU" value="<?= htmlspecialchars($dino['NameRU']) ?>" class="form-control" required></div>
    <div class="mb-3"><label>Название (EN)</label><input type="text" name="nameEN" value="<?= htmlspecialchars($dino['NameEN']) ?>" class="form-control"></div>
    <div class="mb-3"><label>ShortCode</label><input type="text" name="shortCode" value="<?= htmlspecialchars($dino['ShortCode']) ?>" class="form-control" required></div>
    <div class="mb-3"><label>Картинка (с расширением)</label><input type="text" name="pic" value="<?= htmlspecialchars($dino['Pic']) ?>" class="form-control" required></div>
    <div class="mb-3"><label>Уровень</label><input type="number" name="level" value="<?= (int)$dino['Level'] ?>" class="form-control"></div>
    <div class="mb-3"><label>Цена</label><input type="number" name="price" value="<?= (int)$dino['Price'] ?>" class="form-control"></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="enable" id="enable" <?= $dino['Enable'] ? 'checked' : '' ?>><label class="form-check-label" for="enable">Включен</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="aber" id="aber" <?= $dino['Aber'] ? 'checked' : '' ?>><label class="form-check-label" for="aber">Аберация</label></div>
    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="nosex" id="nosex" <?= $dino['NoSex'] ? 'checked' : '' ?>><label class="form-check-label" for="nosex">Кастрат</label></div>
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="createNew" id="createNew">
        <label class="form-check-label" for="createNew">Создать как нового динозавра</label>
    </div>

    <button type="submit" class="btn btn-primary">💾 Сохранить</button>
    <a href="/dino.php" class="btn btn-link">← Назад</a>
</form>