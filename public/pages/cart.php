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
                    <td><img src="/upl/creature/<?= htmlspecialchars($item['image']) ?>" width="64" height="64" style="object-fit: contain;"></td>
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
    <div class="alert alert-success">
        Итого: <strong><?= $total ?> арков</strong>
    </div>
    <form method="post" action="/checkout.php" class="mt-3">
        <button type="submit" class="btn btn-success">Оформить заказ</button>
    </form>
<?php endif; ?>