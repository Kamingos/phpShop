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

$page_title = 'Админка';
include __DIR__ . '/../partials/header.php';
?>

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
    <table class="table">
        <thead>
            <tr>
                <th>Название</th>
                <th>Категория</th>
                <th>Цена</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= e($product['name']) ?></td>
                    <td><?= e($product['category_name'] ?? 'Без категории') ?></td>
                    <td>$<?= number_format((float) $product['price'], 2) ?></td>
                    <td><?= $product['is_active'] ? 'Активен' : 'Скрыт' ?></td>
                    <td>
                        <div class="inline-form">
                            <a class="button secondary" href="/admin/product_edit.php?id=<?= (int) $product['id'] ?>">Редактировать</a>
                            <form method="post" action="/admin/product_delete.php">
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
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>
