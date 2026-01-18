<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

$pdo = getDb();

session_start();

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

  <!-- 🖥️ Десктоп: Таблица -->
  <div class="table-responsive d-none d-md-block">
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
            <td>
              <img src="/upl/<?= $o['Type'] === 'dino' ? 'creature' : 'items' ?>/<?= htmlspecialchars($o['Pic']) ?>" width="64" height="64" style="object-fit: contain;">
            </td>
            <td><?= htmlspecialchars($o['Name']) ?></td>
            <td><?= nl2br(htmlspecialchars(str_replace('|', "\n", $o['Params']))) ?></td>
            <td><?= $o['Qty'] ?></td>
            <td><?= $o['Qty'] * $o['Price'] ?> арков</td>
            <td><?= date("d.m.Y H:i", strtotime($o['CreatedAt'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- 📱 Мобильные: Карточки -->
  <div class="d-md-none">
    <div class="row g-3">
      <?php foreach ($orders as $o): ?>
        <div class="col-12">
          <div class="card p-2">
            <div class="d-flex align-items-start">
              <img src="/upl/<?= $o['Type'] === 'dino' ? 'creature' : 'items' ?>/<?= htmlspecialchars($o['Pic']) ?>"
                alt="<?= htmlspecialchars($o['Name']) ?>" style="width: 70px; height: 70px; object-fit: contain;">
              <div class="ms-3">
                <div class="fw-bold"><?= htmlspecialchars($o['Name']) ?></div>
                <div class="small text-muted mb-1">Кол-во: <?= $o['Qty'] ?> • Сумма: <?= $o['Qty'] * $o['Price'] ?> арков</div>
                <div class="small mb-1"><code><?= htmlspecialchars(str_replace('|', ' | ', $o['Params'])) ?></code></div>
                <div class="text-muted small"><?= date("d.m.Y H:i", strtotime($o['CreatedAt'])) ?></div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach ?>
    </div>
  </div>

<?php endif; ?>