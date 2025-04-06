<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

use App\Model\Dino;
use App\Config\Config;

$pdo = getDb();
$dinoModel = new Dino($pdo);
$dinosaurs = $dinoModel->getVisibleForUser($user);
?>
<h2 class="mb-4">Доступные динозавры</h2>

<input type="text" id="dinoSearchInput" class="form-control mb-3" placeholder="Поиск по имени...">

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="dinoList">
    <?php foreach ($dinosaurs as $dino): ?>
        <div class="col" data-search="<?= htmlspecialchars($dino['NameRU'] . ' ' . $dino['NameEN']) ?>">
            <div class="card h-100">
                <img src="/upl/creature/<?= htmlspecialchars($dino['Pic']) ?>" class="card-img-top" alt="<?= htmlspecialchars($dino['NameRU']) ?>" style="object-fit: contain; height: 200px;">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($dino['NameRU']) ?> (<?= htmlspecialchars($dino['NameEN']) ?>)</h5>
                    <p class="card-text">Цена: <?= $dino['Price'] ?> арков</p>
                    <p class="card-text"><code>/buy dino <?= htmlspecialchars($dino['ShortCode']) ?></code></p>

                    <form action="/add-to-cart.php" method="post" class="mt-3">
                        <input type="hidden" name="id" value="<?= $dino['id'] ?>">
                        <input type="hidden" name="type" value="dino">
                        <input type="hidden" name="name" value="<?= htmlspecialchars($dino['NameRU']) ?>">
                        <input type="hidden" name="image" value="<?= htmlspecialchars($dino['Pic']) ?>">
                        <input type="hidden" name="price" value="<?= $dino['Price'] ?>">
                        <input type="hidden" name="params" value="Уровень:<?= $dino['Level'] ?><?= $dino['NoSex'] ? '|Кастрат:Да' : '' ?>">

                        <div class="input-group">
                            <input type="number" name="qty" class="form-control" value="1" min="1">
                            <button type="submit" class="btn btn-success">В корзину</button>
                        </div>
                    </form>
                    <?php
                    if ($user['SteamId'] == Config::ADMIN_STEAM_ID): ?>
                        <div class="text-end mt-2">
                            <a href="/edit-dino.php?id=<?= $dino['id'] ?>" class="btn btn-sm btn-outline-warning">✏ Редактировать</a>
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
    new Search('#dinoSearchInput', '#dinoList .col', 'data-search');
</script>