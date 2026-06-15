<?php
require_once __DIR__ . '/db.php';

require_login();
$user = current_user();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';
    $productId = (int) ($_POST['product_id'] ?? 0);

    if ($action === 'add') {
        $qty = max(1, min(99, (int) ($_POST['qty'] ?? 1)));
        $stmt = $pdo->prepare('SELECT id FROM products WHERE id = ? AND is_active = 1');
        $stmt->execute([$productId]);
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare(
                'INSERT INTO cart_items (user_id, product_id, qty)
                 VALUES (?, ?, ?)
                 ON CONFLICT(user_id, product_id) DO UPDATE SET qty = qty + excluded.qty'
            );
            $stmt->execute([$user['id'], $productId, $qty]);
            flash('success', 'Добавлено в корзину.');
        } else {
            flash('error', 'Товар недоступен.');
        }
    }

    if ($action === 'update') {
        $qty = max(0, min(99, (int) ($_POST['qty'] ?? 0)));
        if ($qty === 0) {
            $stmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?');
            $stmt->execute([$user['id'], $productId]);
        } else {
            $stmt = $pdo->prepare('UPDATE cart_items SET qty = ? WHERE user_id = ? AND product_id = ?');
            $stmt->execute([$qty, $user['id'], $productId]);
        }
        flash('success', 'Корзина обновлена.');
    }

    if ($action === 'remove') {
        $stmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$user['id'], $productId]);
        flash('success', 'Товар удален.');
    }

    if ($action === 'clear') {
        $stmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        flash('success', 'Корзина очищена.');
    }

    redirect('/cart.php');
}

$stmt = $pdo->prepare(
    'SELECT p.id, p.name, p.price, p.image_path, c.qty
     FROM cart_items c
     JOIN products p ON p.id = c.product_id
     WHERE c.user_id = ?
     ORDER BY p.name'
);
$stmt->execute([$user['id']]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0.0;
foreach ($items as $item) {
    $total += (float) $item['price'] * (int) $item['qty'];
}

$page_title = 'Корзина';
include __DIR__ . '/partials/header.php';
?>

<section>
    <h2 class="section-title">Ваша корзина</h2>
    <?php if (!$items): ?>
        <div class="card">
            <p>Корзина пуста.</p>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Товар</th>
                    <th>Кол-во</th>
                    <th>Цена</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <strong><?= e($item['name']) ?></strong>
                        </td>
                        <td>
                            <form class="inline-form" method="post" action="/cart.php">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
                                <input type="number" name="qty" value="<?= (int) $item['qty'] ?>" min="0" max="99">
                                <button type="submit" class="secondary">Обновить</button>
                            </form>
                        </td>
                        <td>$<?= number_format((float) $item['price'], 2) ?></td>
                        <td>
                            <form method="post" action="/cart.php">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
                                <button type="submit" class="danger">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="card">
            <p><strong>Итого:</strong> $<?= number_format($total, 2) ?></p>
            <div class="inline-form">
                <a class="button" href="/checkout.php">Оформить (тест)</a>
                <form method="post" action="/cart.php">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="secondary">Очистить корзину</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
