<?php
require_once __DIR__ . '/db.php';

require_login();
$user = current_user();

$stmt = db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statusLabels = [
    'new' => 'Новый',
    'paid_test' => 'Оплачен (тест)',
    'processing' => 'В работе',
    'shipped' => 'Отправлен',
    'done' => 'Завершен',
    'canceled' => 'Отменен',
];

$page_title = 'Заказы';
include __DIR__ . '/partials/header.php';
?>

<section>
    <h2 class="section-title">Ваши заказы</h2>
    <?php if (!$orders): ?>
        <div class="card">
            <p>Пока нет заказов.</p>
        </div>
    <?php endif; ?>
    <?php foreach ($orders as $order): ?>
        <div class="card">
            <div class="inline-form">
                <strong>Заказ #<?= (int) $order['id'] ?></strong>
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
        </div>
    <?php endforeach; ?>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
