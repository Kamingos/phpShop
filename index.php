<?php
require_once __DIR__ . '/db.php';

$categories = db()->query(
    'SELECT c.id, c.name, COUNT(p.id) AS product_count
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
     GROUP BY c.id
     ORDER BY c.name'
)->fetchAll(PDO::FETCH_ASSOC);

$products = db()->query(
    'SELECT p.id, p.name, p.description, p.price, p.image_path, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.is_active = 1
     ORDER BY p.id DESC
     LIMIT 6'
)->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Каталог';
include __DIR__ . '/partials/header.php';
?>

<section class="hero">
    <h1>Каталог Шопика</h1>
    <p>Смотрите категории и товары. Зарегистрируйтесь, чтобы добавлять в корзину и оформлять тестовые заказы.</p>
    <?php if (!is_logged_in()): ?>
        <div class="inline-form" style="margin-top: 16px;">
            <a class="button" href="/register.php">Регистрация</a>
            <a class="button secondary" href="/login.php">Войти</a>
        </div>
    <?php else: ?>
        <div class="inline-form" style="margin-top: 16px;">
            <a class="button" href="/cart.php">Корзина</a>
            <a class="button secondary" href="/my_orders.php">Мои заказы</a>
        </div>
    <?php endif; ?>
</section>

<section>
    <h2 class="section-title">Категории</h2>
    <div class="grid">
        <?php if (!$categories): ?>
            <div class="card">
                <p>Категорий пока нет. Администратор может добавить.</p>
            </div>
        <?php endif; ?>
        <?php foreach ($categories as $category): ?>
            <div class="card">
                <h3><?= e($category['name']) ?></h3>
                <p><?= (int) $category['product_count'] ?> товаров</p>
                <a class="button secondary" href="/category.php?id=<?= (int) $category['id'] ?>">Открыть</a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section>
    <h2 class="section-title">Новые товары</h2>
    <div class="grid">
        <?php if (!$products): ?>
            <div class="card">
                <p>Товаров пока нет. Администратор может добавить.</p>
            </div>
        <?php endif; ?>
        <?php foreach ($products as $product): ?>
            <div class="card">
                <?php if ($product['image_path']): ?>
                    <img class="product-image" src="<?= e($product['image_path']) ?>" alt="<?= e($product['name']) ?>">
                <?php else: ?>
                    <div class="product-image"></div>
                <?php endif; ?>
                <h3><?= e($product['name']) ?></h3>
                <p><?= e($product['category_name'] ?? 'Без категории') ?></p>
                <p class="price">₽<?= number_format((float) $product['price'], 0) ?></p>
                <a class="button" href="/product.php?id=<?= (int) $product['id'] ?>">Смотреть</a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
