<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

use App\Config\Config;
use App\Model\Item;

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete']) && $id) {
    if ($itemModel->delete((int)$id)) {
        $_SESSION['flash'] = 'Предмет удалён.';
        header('Location: /item-list.php');
    } else {
        $_SESSION['flash'] = 'Ошибка при удалении.';
        header("Location: edit-item.php?id=" . $id);
    }
    exit;
}

$user = $_SESSION['user'] ?? [];
$isLoggedIn = !empty($user);

if (!$isLoggedIn || $user['SteamId'] != Config::ADMIN_STEAM_ID) {
    die("Доступ запрещён");
}

$pdo = getDb();

$itemModel = new Item($pdo);

$id = $_GET['id'] ?? null;

$imageMap = [];

foreach (Config::ITEM_IMAGE_FOLDERS as $type => $folderPath) {
    $absPath = dirname(__DIR__, 1) . '/' . $folderPath;
    $imageMap[$type] = [];

    if (is_dir($absPath)) {
        foreach (scandir($absPath) as $file) {
            if (preg_match('/\\.(png|jpg|jpeg|gif)$/i', $file)) {
                $imageMap[$type][] = $file;
            }
        }
    }
}

$errors = [];

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
        'DefaultQuality' => in_array($_POST['defaultQuality'] ?? '', array_keys(Config::QUALITY_LABELS)) ? $_POST['defaultQuality'] : null,
        'Customizable'  => isset($_POST['customizable']) ? 1 : 0,
        'Note'          => trim($_POST['note']),
        'Code'          => trim($_POST['code']),
    ];

    if ($data['NameRU'] === '' || $data['ShortCode'] === '' || $data['Pic'] === '' || $data['Code'] === '') {
        $errors[] = "Обязательные поля: Название, ShortCode, Картинка, BlueprintCode.";
    }

    if (empty($errors)) {
        try {
            if (isset($_POST['createNew']) || !$id) {
                $itemModel->create($data);
                $_SESSION['saved'] = 'Создан новый предмет.';
                $id = $pdo->lastInsertId();
            } else {
                $itemModel->update((int)$id, $data);
                $_SESSION['saved'] = 'Изменения сохранены.';
            }
            header("Location: edit-item.php?id=" . $id);
            exit;
        } catch (PDOException $e) {
            $errors[] = "Ошибка: " . $e->getMessage();
        }
    }
}

$item = $id ? $itemModel->findById((int)$id) : [
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
    'Note' => '',
    'Code' => ''
];

$savedMessage = $_SESSION['saved'] ?? null;
unset($_SESSION['saved']);

include __DIR__ . '/layout.php';
?>

<h2 class="mb-4">✏ Редактирование предмета</h2>

<?php if ($savedMessage): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($savedMessage) ?></div>
<?php elseif ($errors): ?>
    <div class="alert alert-danger">
        <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="post">
    <div class="mb-3"><label>Название (RU)</label><input type="text" name="nameRU" value="<?= htmlspecialchars($item['NameRU']) ?>" class="form-control" required></div>
    <div class="mb-3"><label>Название (EN)</label><input type="text" name="nameEN" value="<?= htmlspecialchars($item['NameEN']) ?>" class="form-control"></div>
    <div class="mb-3"><label>ShortCode</label><input type="text" name="shortCode" value="<?= htmlspecialchars($item['ShortCode']) ?>" class="form-control" required></div>
    <div class="mb-3"><label>BlueprintCode</label><input type="text" name="code" value="<?= htmlspecialchars($item['Code']) ?>" class="form-control" required></div>

    <div class="mb-3">
        <label class="form-label">Картинка (файл из /upl/items/)</label>
        <div class="input-group">
            <input type="text" class="form-control" name="pic" id="picInput" value="<?= htmlspecialchars($item['Pic']) ?>" readonly>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#picModal">Выбрать</button>
        </div>
        <div class="mt-2">
            <?php
            $folder = Config::ITEM_IMAGE_FOLDERS[$item['Type']] ?? 'upl/items';
            $imgPath = $folder . '/' . $item['Pic'];
            ?>
            <img id="picPreview" src="<?= htmlspecialchars($imgPath) ?>" style="max-height: 100px;" class="border">
        </div>
    </div>

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
            <?php foreach (Config::QUALITY_LABELS as $val => $label): ?>
                <option value="<?= $val ?>" <?= $item['DefaultQuality'] === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3"><label>Примечание</label><textarea name="note" class="form-control"><?= htmlspecialchars($item['Note']) ?></textarea></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="enable" <?= $item['Enable'] ? 'checked' : '' ?>><label class="form-check-label">Включен</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="visible" <?= $item['Visible'] ? 'checked' : '' ?>><label class="form-check-label">Видим</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="hasQuality" <?= $item['HasQuality'] ? 'checked' : '' ?>><label class="form-check-label">Есть качество</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="customizable" <?= $item['Customizable'] ? 'checked' : '' ?>><label class="form-check-label">Кастомизация</label></div>
    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="createNew"><label class="form-check-label">Создать как новый</label></div>

    <button type="submit" class="btn btn-primary">💾 Сохранить</button>
    <a href="/item-list.php" class="btn btn-link">← Назад</a>
    <?php if ($id): ?>
        <hr>
        <form method="post" onsubmit="return confirm('Удалить этот предмет?');">
            <input type="hidden" name="delete" value="1">
            <button type="submit" class="btn btn-outline-danger">🗑 Удалить предмет</button>
        </form>
    <?php endif; ?>
</form>

<!-- Модалка выбора картинки -->
<div class="modal fade" id="picModal" tabindex="-1" aria-labelledby="picModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Выберите картинку</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="picSearch" class="form-control mb-3" placeholder="Фильтр по названию...">
                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 g-2" id="picList">
                    <?php foreach ($images as $img): ?>
                        <div class="col" data-name="<?= strtolower($img) ?>">
                            <div class="card h-100 select-pic" data-filename="<?= $img ?>" style="cursor: pointer;">
                                <img src="/upl/items/<?= $img ?>" class="card-img-top" style="height: 100px; object-fit: contain;">
                                <div class="card-body p-1 text-center small"><?= $img ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.ITEM_IMAGE_MAP = <?= json_encode($imageMap, JSON_UNESCAPED_UNICODE) ?>;
    window.ITEM_IMAGE_FOLDERS = <?= json_encode(Config::ITEM_IMAGE_FOLDERS, JSON_UNESCAPED_UNICODE) ?>;
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const typeSelect = document.querySelector('[name="type"]');
        const picList = document.getElementById('picList');
        const picInput = document.getElementById('picInput');
        const picPreview = document.getElementById('picPreview');

        function renderImagesForType(type) {
            const folder = window.ITEM_IMAGE_FOLDERS[type];
            const files = window.ITEM_IMAGE_MAP[type] || [];

            picList.innerHTML = '';

            files.forEach(img => {
                const col = document.createElement('div');
                col.className = 'col';
                col.dataset.name = img.toLowerCase();

                col.innerHTML = `
            <div class="card h-100 select-pic" data-filename="${img}" data-folder="${folder}" style="cursor: pointer;">
                <img src="/${folder}/${img}" class="card-img-top" style="height: 100px; object-fit: contain;">
                <div class="card-body p-1 text-center small">${img}</div>
            </div>
        `;

                picList.appendChild(col);
            });
            const picSearch = document.getElementById('picSearch');

            picSearch?.addEventListener('input', () => {
                const q = picSearch.value.toLowerCase().trim();
                document.querySelectorAll('#picList .col').forEach(col => {
                    const name = col.dataset.name;
                    col.style.display = name.includes(q) ? '' : 'none';
                });
            });

            bindImageClickHandlers();
        }

        function bindImageClickHandlers() {
            document.querySelectorAll('.select-pic').forEach(el => {
                el.addEventListener('click', () => {
                    const filename = el.dataset.filename;
                    const folder = el.dataset.folder;
                    picInput.value = filename;
                    picPreview.src = `/${folder}/${filename}`;
                    bootstrap.Modal.getInstance(document.getElementById('picModal')).hide();
                });
            });
        }

        // при выборе типа
        typeSelect.addEventListener('change', () => {
            renderImagesForType(typeSelect.value);
        });

        // при загрузке страницы — показать картинки для текущего типа
        renderImagesForType(typeSelect.value);

    });
</script>