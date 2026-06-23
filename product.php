<?php
require_once __DIR__ . '/db.php';

$pdo = db();
$productId = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    'SELECT p.*, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.id = ? AND p.is_active = 1'
);
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

$page_title = $product ? $product['name'] : 'Товар';
include __DIR__ . '/partials/header.php';

if (empty($product)):
?>
    <div class="card">
        <p>Товар не найден.</p>
    </div>
<?php
    include __DIR__ . '/partials/footer.php';
    exit;
endif;
?>

<section class="card">
    <?php if ($product['image_path']): ?>
        <img class="product-image" src="<?= e($product['image_path']) ?>" alt="<?= e($product['name']) ?>">
    <?php else: ?>
        <div class="product-image"></div>
    <?php endif; ?>
    <h2><?= e($product['name']) ?></h2>
    <p><?= e($product['category_name'] ?? 'Без категории') ?></p>
    <p><?= e($product['description']) ?></p>
    <p class="price"><?= number_format((float) $product['price'], 0) ?>₽</p>

    <?php if (is_logged_in()): ?>
        <form class="inline-form" method="post" action="/cart.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
            <input type="number" name="qty" value="1" min="1" max="99">
            <button type="submit">В корзину</button>
        </form>
    <?php else: ?>
        <p><a href="/login.php">Войдите</a>, чтобы добавить товар в корзину.</p>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
