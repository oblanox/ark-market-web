<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

use App\Model\Item;
use App\Config\Config;


$pdo = getDb();

$itemModel = new Item($pdo);
$user = $_SESSION['user'] ?? [];
$items = $itemModel->getVisibleForUser($user);
?>

<h2 class="mb-4">Предметы магазина</h2>

<div class="row mb-3">
    <div class="col-md-6 mb-2">
        <input type="text" id="itemSearchInput" class="form-control" placeholder="Поиск по имени...">
    </div>
    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-info"><?= $_SESSION['flash'] ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
    <div class="col-md-6 mb-2">
        <select id="typeFilter" class="form-select">
            <option value="">Все категории</option>
            <?php foreach (Config::ITEM_TYPES as $typeKey => $typeName): ?>
                <option value="<?= $typeKey ?>"><?= $typeName ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="itemList">
    <?php foreach ($items as $item): ?>
        <div class="col"
            data-search="<?= htmlspecialchars($item['NameRU'] . ' ' . $item['NameEN']) ?>"
            data-type="<?= htmlspecialchars($item['Type']) ?>">
            <div class="card h-100">
                <?php
                $folder = Config::ITEM_IMAGE_FOLDERS[$item['Type']] ?? 'upl/items';
                $imgPath = $folder . '/' . $item['Pic'];
                ?>
                <img src="<?= htmlspecialchars($imgPath) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['NameRU']) ?>" style="object-fit: contain; height: 200px;">
                <div class="card-body d-flex flex-column justify-content-between">
                    <h5 class="card-title"><?= htmlspecialchars($item['NameRU']) ?> (<?= htmlspecialchars($item['NameEN']) ?>)</h5>

                    <p class="card-text">Цена: <?= $item['Price'] ?> арков (<?= $item['StackSize'] ?> шт.)</p>
                    <p class="card-text"><code>/buy item <?= htmlspecialchars($item['ShortCode']) ?></code></p>

                    <form method="post" action="/add-to-cart.php">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <input type="hidden" name="type" value="<?= $item['Type'] ?>">
                        <input type="hidden" name="name" value="<?= htmlspecialchars($item['NameRU']) ?>">
                        <input type="hidden" name="image" value="<?= htmlspecialchars($item['Pic']) ?>">
                        <input type="hidden" name="price" value="<?= $item['Price'] ?>">

                        <?php if (!empty($item['HasQuality'])): ?>
                            <div class="mb-2">
                                <label class="form-label">Качество:</label>
                                <select name="quality" class="form-select">
                                    <?php foreach (Config::QUALITY_LABELS as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= $item['DefaultQuality'] === $value ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php else : ?>
                            <div class="mb-3"></div>
                        <?php endif; ?>

                        <div class="input-group mb-2">
                            <span class="input-group-text">Кол-во</span>
                            <input type="number" class="form-control" name="count" value="1" min="1">
                        </div>

                        <div class="mt-auto">
                            <button type="submit" class="btn btn-success w-100">➕ В корзину</button>
                        </div>
                    </form>

                    <?php if ($user['SteamId'] == Config::ADMIN_STEAM_ID): ?>
                        <div class="text-end mt-2">
                            <a href="/edit-item.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-warning">✏ Редактировать</a>
                        </div>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script type="module">
    import {
        Search
    } from '/assets/js/Search.js';
    const search = new Search('#itemSearchInput', '#itemList .col', 'data-search');

    const typeFilter = document.getElementById('typeFilter');
    const searchInput = document.getElementById('itemSearchInput');
    const cards = document.querySelectorAll('#itemList .col');

    function applyFilters() {
        const selectedType = typeFilter.value;
        const query = searchInput.value.toLowerCase();

        cards.forEach(card => {
            const nameMatch = card.dataset.search.toLowerCase().includes(query);
            const typeMatch = !selectedType || card.dataset.type === selectedType;
            card.style.display = nameMatch && typeMatch ? '' : 'none';
        });
    }

    typeFilter.addEventListener('change', applyFilters);
    searchInput.addEventListener('input', applyFilters);
</script>