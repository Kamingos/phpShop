<?php
require_once __DIR__ . '/db.php';

require_login();
$user = current_user();
$pdo = db();

$stmt = $pdo->prepare(
    'SELECT p.id, p.name, p.price, c.qty
     FROM cart_items c
     JOIN products p ON p.id = c.product_id
     WHERE c.user_id = ?'
);
$stmt->execute([$user['id']]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0.0;
foreach ($items as $item) {
    $total += (float) $item['price'] * (int) $item['qty'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (!$items) {
        flash('error', 'Корзина пуста.');
        redirect('/cart.php');
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('INSERT INTO orders (user_id, status) VALUES (?, ?)');
        $stmt->execute([$user['id'], 'new']);
        $orderId = (int) $pdo->lastInsertId();

        $stmt = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, qty, price) VALUES (?, ?, ?, ?)'
        );
        foreach ($items as $item) {
            $stmt->execute([$orderId, $item['id'], (int) $item['qty'], (float) $item['price']]);
        }

        $stmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        $pdo->commit();

        flash('success', 'Заказ #' . $orderId . ' оформлен!');
        redirect('/my_orders.php');
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash('error', 'Не удалось оформить заказ.');
        redirect('/cart.php');
    }
}

$page_title = 'Оформление';
include __DIR__ . '/partials/header.php';
?>

<section class="card">
    <h2>Оформление заказа</h2>
    <p class="alert" style="background:#fff3cd;color:#856404;margin-bottom:16px;">
        <strong>Тестовый режим:</strong> оплата не производится. Заказ будет виден в админке.
    </p>
    <?php if (!$items): ?>
        <p>Корзина пуста.</p>
    <?php else: ?>
        <div class="order-summary">
            <h3>Состав заказа:</h3>
            <?php foreach ($items as $item): ?>
                <p><?= e($item['name']) ?> — <?= (int) $item['qty'] ?> x ₽<?= number_format((float) $item['price'], 0) ?></p>
            <?php endforeach; ?>
            <p><strong>Итого: ₽<?= number_format($total, 0) ?></strong></p>
        </div>
        <form method="post" action="/checkout.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <button type="submit">Подтвердить заказ</button>
        </form>
    <?php endif; ?>
</section>

<style>
.order-summary {
    background: #f8f4ed;
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 16px;
}
.order-summary h3 {
    margin: 0 0 12px;
    font-size: 16px;
}
.order-summary p {
    margin: 4px 0;
}
</style>

<?php include __DIR__ . '/partials/footer.php'; ?>
