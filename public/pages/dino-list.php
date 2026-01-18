<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

use App\Model\Dino;
use App\Config\Config;

$pdo = getDb();
$dinoModel = new Dino($pdo);
$user = $_SESSION['user'] ?? [];
$dinosaurs = $dinoModel->getVisibleForUser($user);
?>
<h2 class="mb-4">Доступные динозавры</h2>


<div class="row mb-3">
    <div class="col-md-6 mb-2">
        <input type="text" id="dinoSearchInput" class="form-control mb-3" placeholder="Поиск по имени...">
    </div>
    <?php if ($user['SteamId'] == Config::ADMIN_STEAM_ID): ?>
        <div class="col-md-6 mb-2 form-check pt-2">
            <input class="form-check-input" type="checkbox" id="enabledOnlyFilter">
            <label class="form-check-label" for="enabledOnlyFilter">Только включённые</label>
        </div>
    <?php endif; ?>
</div>


<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="dinoList">
    <?php foreach ($dinosaurs as $dino): ?>
        <div class="col" data-search="<?= htmlspecialchars($dino['NameRU'] . ' ' . $dino['NameEN']) ?>" data-enabled="<?= $dino['Enable'] ? '1' : '0' ?>">
            <div class="card h-100">
                <img src="/upl/creature/<?= htmlspecialchars($dino['Pic']) ?>" class="card-img-top" alt="<?= htmlspecialchars($dino['NameRU']) ?>"
                    style="object-fit: contain; height: 200px;">
                <div class="card-body d-flex flex-column justify-content-between">
                    <h5 class="card-title"><?= htmlspecialchars($dino['NameRU']) ?> (<?= htmlspecialchars($dino['NameEN']) ?>)</h5>
                    <!-- <p class="card-text"><Базовая цена: <?= $dino['Price'] ?> арков</p> -->
                    <p class="card-text"><code>/buy dino <?= htmlspecialchars($dino['ShortCode']) ?></code></p>

                    <form method="post" action="/add-to-cart.php">
                        <input type="hidden" name="id" value="<?= $dino['id'] ?>">
                        <input type="hidden" name="type" value="dino">
                        <input type="hidden" name="name" value="<?= htmlspecialchars($dino['NameRU']) ?>">
                        <input type="hidden" name="image" value="<?= htmlspecialchars($dino['Pic']) ?>">
                        <p class="card-text price-output"
                            data-base="<?= $dino['Price'] ?>"
                            data-dino-id="<?= $dino['id'] ?>">
                            Цена: <?= $dino['Price'] ?> арков
                        </p>
                        <?php $dino_level = $dino['Level'] == "0" ?  223 : $dino['Level']; ?>
                        <input type="hidden" name="level" value="<?= $dino_level ?>">

                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="xp" id="xp_<?= $dino['id'] ?>">
                            <label class="form-check-label" for="xp_<?= $dino['id'] ?>">С максимумом опыта</label>
                        </div>

                        <?php if (!$dino['NoSex']): ?>
                            <div class="mb-2">
                                <label class="form-label mb-1">Пол:</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" value="random" checked id="g_r_<?= $dino['id'] ?>">
                                    <label class="form-check-label" for="g_r_<?= $dino['id'] ?>">Рандом</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" value="female" id="g_f_<?= $dino['id'] ?>">
                                    <label class="form-check-label" for="g_f_<?= $dino['id'] ?>">Девочка</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" value="male" id="g_m_<?= $dino['id'] ?>">
                                    <label class="form-check-label" for="g_m_<?= $dino['id'] ?>">Мальчик</label>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($dino['NoSex']): ?>
                            <input type="hidden" name="gender" value="nosex">
                        <?php endif; ?>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" checked name="neutered" id="neut_<?= $dino['id'] ?>" <?= $dino['NoSex'] ? 'checked disabled' : '' ?>>
                            <label class="form-check-label" for="neut_<?= $dino['id'] ?>">Кастрат</label>
                        </div>

                        <div class="input-group mb-2">
                            <span class="input-group-text">Кол-во</span>
                            <input type="number" class="form-control" name="count" value="1" min="1">
                        </div>
                        <div class="mt-auto">
                            <button type="submit" class="btn btn-success w-100">➕ В корзину</button>
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
<script>
    window.DINO_PRICE_PERCENT = <?= json_encode(Config::PRICE_PERCENT, JSON_UNESCAPED_UNICODE) ?>;
</script>


<script type="module">
    import {
        Search
    } from '/assets/js/Search.js';
    new Search('#dinoSearchInput', '#dinoList .col', 'data-search');

    document.addEventListener('DOMContentLoaded', () => {

        const PERCENT = window.DINO_PRICE_PERCENT;

        function calculatePrice(base, xp, gender, neutered) {
            let price = base;
            if (xp) price += base * (PERCENT.XP / 100);
            if (gender !== 'random') price += base * (PERCENT.MALE / 100);
            if (!neutered) price += base * (PERCENT.NEUTERED / 100);
            return Math.round(price);
        }

        document.querySelectorAll('form[action=\"/add-to-cart.php\"]').forEach(form => {
            const dinoId = form.querySelector('input[name=\"id\"]').value;
            const priceBlock = document.querySelector(`.price-output[data-dino-id=\"${dinoId}\"]`);
            const base = parseInt(priceBlock.dataset.base);

            const update = () => {
                const xp = form.querySelector('[name=\"xp\"]')?.checked;
                const gender = form.querySelector('[name=\"gender\"]:checked')?.value;
                const neutered = form.querySelector('[name=\"neutered\"]')?.checked;

                const final = calculatePrice(base, xp, gender, neutered);
                priceBlock.textContent = `Цена: ${final} арков`;
            };

            // навесим на все переключатели
            form.querySelectorAll('input').forEach(input => {
                input.addEventListener('change', update);
            });

            // первый запуск
            update();
        });
    });
</script>