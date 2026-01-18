<?php

use App\Service\Cart;

session_start();
$items = Cart::getItems();
$total = Cart::getTotalPrice();
?>

<h2 class="mb-4">🛒 Ваша корзина</h2>

<?php if (empty($items)): ?>
    <div class="alert alert-info">Корзина пуста. Добавьте товары.</div>
<?php else: ?>

    <!-- 🖥️ Десктоп: Таблица -->
    <div class="table-responsive d-none d-md-block">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Изображение</th>
                    <th>Название</th>
                    <th>Параметры</th>
                    <th>Цена</th>
                    <th>Кол-во</th>
                    <th>Сумма</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $key => $item): ?>
                    <tr>
                        <td>
                            <img src="/upl/<?= $item['type'] === 'dino' ? 'creature' : 'items' ?>/<?= htmlspecialchars($item['image']) ?>"
                                width="64" height="64" style="object-fit: contain;">
                        </td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= nl2br(htmlspecialchars(str_replace('|', "\n", $item['params']))) ?></td>
                        <td><?= $item['price'] ?> арк</td>
                        <td><?= $item['qty'] ?></td>
                        <td><?= $item['qty'] * $item['price'] ?> арк</td>
                        <td>
                            <form action="/remove-from-cart.php" method="post" style="display:inline;">
                                <input type="hidden" name="key" value="<?= htmlspecialchars($key) ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- 📱 Мобильный вид: карточки -->
    <div class="d-md-none">
        <div class="row g-3">
            <?php foreach ($items as $key => $item): ?>
                <div class="col-12">
                    <div class="card p-2">
                        <div class="d-flex align-items-center">
                            <img src="/upl/<?= $item['type'] === 'dino' ? 'creature' : 'items' ?>/<?= htmlspecialchars($item['image']) ?>"
                                alt="<?= htmlspecialchars($item['name']) ?>" style="width: 70px; height: 70px; object-fit: contain;">
                            <div class="ms-3 flex-grow-1">
                                <div class="fw-bold"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="small text-muted">Цена: <?= $item['price'] ?> арк</div>
                                <div class="small text-muted">Кол-во: <?= $item['qty'] ?></div>
                                <div class="small text-muted">Сумма: <?= $item['qty'] * $item['price'] ?> арк</div>
                                <div class="small text-muted mt-1"><code><?= htmlspecialchars(str_replace('|', ' | ', $item['params'])) ?></code></div>
                            </div>
                            <form method="post" action="/remove-from-cart.php">
                                <input type="hidden" name="key" value="<?= htmlspecialchars($key) ?>">
                                <button class="btn btn-sm btn-outline-danger" title="Удалить"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>

    <div class="alert alert-success mt-4 text-end">
        💰 Итого: <strong><?= $total ?> арков</strong>
    </div>

    <form method="post" action="/checkout.php" class="mt-3 text-end">
        <button type="submit" class="btn btn-success">🚀 Оформить заказ</button>
    </form>
<?php endif; ?>