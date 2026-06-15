<?php
require_once __DIR__ . '/../db.php';

require_admin();

$categories = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$products = db()->query(
    'SELECT p.id, p.name, p.price, p.is_active, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     ORDER BY p.id DESC'
)->fetchAll(PDO::FETCH_ASSOC);

// Статистика
$totalProducts = (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalCategories = (int) db()->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$totalOrders = (int) db()->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$newOrders = (int) db()->query("SELECT COUNT(*) FROM orders WHERE status = 'new'")->fetchColumn();

$page_title = 'Админка';
include __DIR__ . '/../partials/header.php';
?>

<section class="admin-stats">
    <div class="stat-card">
        <h3><?= $totalCategories ?></h3>
        <p>Категории</p>
    </div>
    <div class="stat-card">
        <h3><?= $totalProducts ?></h3>
        <p>Товары</p>
    </div>
    <div class="stat-card">
        <h3><?= $totalOrders ?></h3>
        <p>Заказы</p>
    </div>
    <div class="stat-card new">
        <h3><?= $newOrders ?></h3>
        <p>Новых заказов</p>
    </div>
</section>

<section class="card">
    <h2>Категории</h2>
    <form class="inline-form" method="post" action="/admin/category_edit.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="add">
        <input type="text" name="name" placeholder="Новая категория" required>
        <button type="submit">Добавить</button>
    </form>
    <div class="form">
        <?php foreach ($categories as $category): ?>
            <form class="inline-form" method="post" action="/admin/category_edit.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
                <input type="text" name="name" value="<?= e($category['name']) ?>" required>
                <button type="submit" name="action" value="update" class="secondary">Сохранить</button>
                <button type="submit" name="action" value="delete" class="danger">Удалить</button>
            </form>
        <?php endforeach; ?>
    </div>
</section>

<section class="card">
    <div class="inline-form">
        <h2>Товары</h2>
        <a class="button" href="/admin/product_edit.php">Добавить товар</a>
    </div>
    <?php if (!$products): ?>
        <p>Товаров пока нет.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Товар</th>
                    <th>Категория</th>
                    <th>Цена</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <?php if ($product['image_path']): ?>
                                <img src="<?= e($product['image_path']) ?>" alt="" style="width:40px;height:40px;border-radius:4px;object-fit:cover;margin-right:8px;">
                            <?php endif; ?>
                            <strong><?= e($product['name']) ?></strong>
                        </td>
                        <td><?= e($product['category_name'] ?? 'Без категории') ?></td>
                        <td>₽<?= number_format((float) $product['price'], 0) ?></td>
                        <td><?= $product['is_active'] ? '<span class="status-chip">Активен</span>' : '<span class="status-chip" style="background:#f8d7da">Скрыт</span>' ?></td>
                        <td>
                            <div class="inline-form">
                                <a class="button secondary" href="/admin/product_edit.php?id=<?= (int) $product['id'] ?>">Редактировать</a>
                                <form method="post" action="/admin/product_delete.php" onsubmit="return confirm('Удалить товар?');">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                    <button type="submit" class="danger">Удалить</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>
