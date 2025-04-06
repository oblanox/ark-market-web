<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

use App\Config\Config;
use App\Model\Item;

session_start();
$user = $_SESSION['user'] ?? [];
$isLoggedIn = !empty($user);

if (!$isLoggedIn || $user['SteamId'] != Config::ADMIN_STEAM_ID) {
    die("Доступ запрещён");
}

$pdo = getDb();
$itemModel = new Item($pdo);

$id = $_GET['id'] ?? null;
$dino = null;

if ($id && is_numeric($id)) {
    $item = $itemModel->findById((int)$id);
    if (!$item) {
        die("Предмет не найден.");
    }
} else {
    $item = [
        'NameRU' => '',
        'NameEN' => '',
        'Pic' => '',
        'Price' => 0,
        'ShortCode' => '',
        'Type' => 'resource',
        'StackSize' => 1,
        'Enable' => 1,
        'Visible' => 1,
        'HasQuality' => 0,
        'DefaultQuality' => '',
        'Customizable' => 0,
        'Note' => ''
    ];
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'NameRU'        => trim($_POST['nameRU']),
        'NameEN'        => trim($_POST['nameEN']),
        'Pic'           => trim($_POST['pic']),
        'Price'         => (int)$_POST['price'],
        'ShortCode'     => trim($_POST['shortCode']),
        'Type'          => $_POST['type'],
        'StackSize'     => (int)$_POST['stackSize'],
        'Enable'        => isset($_POST['enable']) ? 1 : 0,
        'Visible'       => isset($_POST['visible']) ? 1 : 0,
        'HasQuality'    => isset($_POST['hasQuality']) ? 1 : 0,
        'DefaultQuality' => $_POST['defaultQuality'] ?? '',
        'Customizable'  => isset($_POST['customizable']) ? 1 : 0,
        'Note'          => trim($_POST['note']),
    ];

    if ($data['NameRU'] === '' || $data['ShortCode'] === '' || $data['Pic'] === '') {
        $errors[] = "Обязательные поля: Название, ShortCode, Картинка.";
    }

    if (empty($errors)) {
        try {
            if (isset($_POST['createNew']) || !$id) {
                $itemModel->create($data);
            } else {
                $itemModel->update((int)$id, $data);
            }
            $success = true;
        } catch (PDOException $e) {
            $errors[] = "Ошибка: " . $e->getMessage();
        }
    }

    // обновим отображение
    if ($success && !$id) {
        header("Location: /item-list.php");
        exit;
    }
}
?>

<?php include __DIR__ . '/layout.php'; ?>

<h2 class="mb-4">✏ Редактирование предмета</h2>

<?php if ($success): ?>
    <div class="alert alert-success">✅ Сохранено</div>
<?php elseif ($errors): ?>
    <div class="alert alert-danger">
        <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="post">
    <div class="mb-3"><label>Название (RU)</label><input type="text" name="nameRU" value="<?= htmlspecialchars($item['NameRU']) ?>" class="form-control" required></div>
    <div class="mb-3"><label>Название (EN)</label><input type="text" name="nameEN" value="<?= htmlspecialchars($item['NameEN']) ?>" class="form-control"></div>
    <div class="mb-3"><label>ShortCode</label><input type="text" name="shortCode" value="<?= htmlspecialchars($item['ShortCode']) ?>" class="form-control" required></div>
    <div class="mb-3"><label>Картинка (файл)</label><input type="text" name="pic" value="<?= htmlspecialchars($item['Pic']) ?>" class="form-control" required></div>
    <div class="mb-3"><label>Цена</label><input type="number" name="price" value="<?= (int)$item['Price'] ?>" class="form-control"></div>
    <div class="mb-3"><label>Тип</label>
        <select name="type" class="form-select">
            <?php foreach (['resource', 'inventory', 'structure', 'skin', 'consumable'] as $type): ?>
                <option value="<?= $type ?>" <?= $item['Type'] === $type ? 'selected' : '' ?>><?= ucfirst($type) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3"><label>Стак</label><input type="number" name="stackSize" value="<?= (int)$item['StackSize'] ?>" class="form-control" min="1"></div>
    <div class="mb-3"><label>Качество по умолчанию</label>
        <select name="defaultQuality" class="form-select">
            <option value="">—</option>
            <?php foreach (['Primitive', 'Ramshackle', 'Apprentice', 'Journeyman', 'Mastercraft', 'Ascendant'] as $q): ?>
                <option value="<?= $q ?>" <?= $item['DefaultQuality'] === $q ? 'selected' : '' ?>><?= $q ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3"><label>Примечание</label><textarea name="note" class="form-control"><?= htmlspecialchars($item['Note']) ?></textarea></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="enable" id="enable" <?= $item['Enable'] ? 'checked' : '' ?>><label class="form-check-label" for="enable">Включен</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="visible" id="visible" <?= $item['Visible'] ? 'checked' : '' ?>><label class="form-check-label" for="visible">Видим в магазине</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="hasQuality" id="hasQuality" <?= $item['HasQuality'] ? 'checked' : '' ?>><label class="form-check-label" for="hasQuality">Есть качество</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="customizable" id="customizable" <?= $item['Customizable'] ? 'checked' : '' ?>><label class="form-check-label" for="customizable">Кастомизация</label></div>
    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="createNew" id="createNew"><label class="form-check-label" for="createNew">Создать как новый</label></div>

    <button type="submit" class="btn btn-primary">💾 Сохранить</button>
    <a href="/item-list.php" class="btn btn-link">← Назад</a>
</form>