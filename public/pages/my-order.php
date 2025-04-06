<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

$pdo = getDb();

if (!isset($_SESSION['user'])) {
  header("Location: /login.php");
  exit;
}

$user = $_SESSION['user'];
$steamId = $user['SteamId'];

$stmt = $pdo->prepare("SELECT * FROM arkshop_orders WHERE SteamId = :steamId AND Delivered = 0 ORDER BY CreatedAt DESC");
$stmt->execute(['steamId' => $steamId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="mb-4">📦 Ваши заказы, ожидающие получения</h2>

<?php if (!$orders): ?>
  <div class="alert alert-info">У вас нет ожидающих заказов.</div>
<?php else: ?>
  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>Изображение</th>
        <th>Название</th>
        <th>Параметры</th>
        <th>Кол-во</th>
        <th>Сумма</th>
        <th>Дата</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td><img src="/upl/creature/<?= htmlspecialchars($o['Pic']) ?>" width="64" height="64" style="object-fit: contain;"></td>
          <td><?= htmlspecialchars($o['Name']) ?></td>
          <td><?= nl2br(htmlspecialchars(str_replace('|', "\n", $o['Params']))) ?></td>
          <td><?= $o['Qty'] ?></td>
          <td><?= $o['Qty'] * $o['Price'] ?> арков</td>
          <td><?= date("d.m.Y H:i", strtotime($o['CreatedAt'])) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>