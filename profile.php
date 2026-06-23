<?php
require_once __DIR__ . '/db.php';

require_login();
$user = current_user();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';
    
    if ($action === 'change_password') {
        $current = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');
        
        if (empty($current) || empty($new) || empty($confirm)) {
            flash('error', 'Все поля обязательны.');
        } elseif (!password_verify($current, $user['password_hash'])) {
            flash('error', 'Неверный текущий пароль.');
        } elseif (strlen($new) < 6) {
            flash('error', 'Новый пароль должен быть не короче 6 символов.');
        } elseif ($new !== $confirm) {
            flash('error', 'Пароли не совпадают.');
        } else {
            $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $stmt->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
            flash('success', 'Пароль изменен.');
        }
    }
}

// Получаем статистику
$stmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
$stmt->execute([$user['id']]);
$totalOrders = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT SUM(oi.price * oi.qty) FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE o.user_id = ?');
$stmt->execute([$user['id']]);
$totalSpent = $stmt->fetchColumn() ?: 0;

$page_title = 'Профиль';
include __DIR__ . '/partials/header.php';
?>

<section class="card">
    <h2>Профиль пользователя</h2>
    <p><strong>Email:</strong> <?= e($user['email']) ?></p>
    <p><strong>Роль:</strong> <?= $user['role'] === 'admin' ? 'Администратор' : 'Пользователь' ?></p>
    <p><strong>Всего заказов:</strong> <?= $totalOrders ?></p>
    <p><strong>Потрачено:</strong> <?= number_format((float) $totalSpent, 2) ?>₽</p>
</section>

<section class="card">
    <h2>Сменить пароль</h2>
    <form class="form" method="post" action="/profile.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="change_password">
        <label>
            Текущий пароль
            <input type="password" name="current_password" required>
        </label>
        <label>
            Новый пароль
            <input type="password" name="new_password" required minlength="6">
        </label>
        <label>
            Подтвердите пароль
            <input type="password" name="confirm_password" required minlength="6">
        </label>
        <button type="submit">Сменить пароль</button>
    </form>
</section>

<section class="card">
    <h2>Быстрые ссылки</h2>
    <div class="inline-form">
        <a class="button" href="/orders.php">Мои заказы</a>
        <a class="button secondary" href="/cart.php">Корзина</a>
        <?php if ($user['role'] === 'admin'): ?>
            <a class="button secondary" href="/admin/index.php">Админка</a>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
