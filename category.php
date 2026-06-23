<?php
require_once __DIR__ . '/db.php';

$pdo = db();
$categoryId = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
$stmt->execute([$categoryId]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

$page_title = $category ? $category['name'] : 'Категория';
include __DIR__ . '/partials/header.php';

if (!$category):
?>
    <div class="card">
        <p>Категория не найдена.</p>
    </div>
<?php
    include __DIR__ . '/partials/footer.php';
    exit;
endif;

$stmt = $pdo->prepare(
    'SELECT p.id, p.name, p.description, p.price, p.image_path
     FROM products p
     WHERE p.category_id = ? AND p.is_active = 1
     ORDER BY p.id DESC'
);
$stmt->execute([$categoryId]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section>
    <h2 class="section-title"><?= e($category['name']) ?></h2>
    <div class="grid">
        <?php if (empty($products)): ?>
            <div class="card">
                <p>В этой категории пока нет товаров.</p>
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
                <p class="price"><?= number_format((float) $product['price'], 0) ?>₽</p>
                <a class="button" href="/product.php?id=<?= (int) $product['id'] ?>">Смотреть</a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
