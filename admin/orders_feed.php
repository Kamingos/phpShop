<?php
require_once __DIR__ . '/../db.php';

require_admin();

$orders = db()->query(
    'SELECT o.id, o.status, o.created_at, u.email
     FROM orders o
     JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC
     LIMIT 30'
)->fetchAll(PDO::FETCH_ASSOC);

$statusLabels = [
    'new' => 'Новый',
    'paid_test' => 'Оплачен (тест)',
    'processing' => 'В работе',
    'shipped' => 'Отправлен',
    'done' => 'Завершен',
    'canceled' => 'Отменен',
];
?>

<?php if (!$orders): ?>
    <div class="card">
        <p>Пока нет заказов.</p>
    </div>
<?php endif; ?>

<?php foreach ($orders as $order): ?>
    <div class="card">
        <div class="inline-form">
            <strong>Заказ #<?= (int) $order['id'] ?></strong>
            <span><?= e($order['email']) ?></span>
            <span class="status-chip"><?= e($statusLabels[$order['status']] ?? $order['status']) ?></span>
            <span><?= e($order['created_at']) ?></span>
        </div>
        <div>
            <?php
            $stmt = db()->prepare(
                'SELECT oi.qty, oi.price, p.name
                 FROM order_items oi
                 JOIN products p ON p.id = oi.product_id
                 WHERE oi.order_id = ?'
            );
            $stmt->execute([(int) $order['id']]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <?php foreach ($items as $item): ?>
                <p><?= e($item['name']) ?> — <?= (int) $item['qty'] ?> x $<?= number_format((float) $item['price'], 2) ?></p>
            <?php endforeach; ?>
        </div>
        <form class="inline-form" method="post" action="/admin/order_status.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int) $order['id'] ?>">
            <select name="status">
                <?php foreach ($statusLabels as $status => $label): ?>
                    <option value="<?= e($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="secondary">Обновить</button>
        </form>
    </div>
<?php endforeach; ?>
