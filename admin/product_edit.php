<?php
require_once __DIR__ . '/../db.php';

require_admin();

$pdo = db();
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

$productId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$product = null;
if ($productId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $price = (float) ($_POST['price'] ?? 0);
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $categoryValue = $categoryId > 0 ? $categoryId : null;

    if ($name === '' || $price <= 0) {
        flash('error', 'Название и цена обязательны.');
    } else {
        $imagePath = null;
        $uploadError = null;
        if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['image'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                if ($file['size'] > 2 * 1024 * 1024) {
                    $uploadError = 'Изображение должно быть меньше 2 МБ.';
                } elseif (!getimagesize($file['tmp_name'])) {
                    $uploadError = 'Файл не является изображением.';
                } else {
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                    if (!in_array($ext, $allowed, true)) {
                        $uploadError = 'Недопустимый тип изображения.';
                    } else {
                        $fileName = bin2hex(random_bytes(8)) . '.' . $ext;
                        $destination = __DIR__ . '/../uploads/' . $fileName;
                        if (move_uploaded_file($file['tmp_name'], $destination)) {
                            $imagePath = '/uploads/' . $fileName;
                        } else {
                            $uploadError = 'Не удалось сохранить изображение.';
                        }
                    }
                }
            } else {
                $uploadError = 'Ошибка загрузки.';
            }
        }

        if ($uploadError) {
            flash('error', $uploadError);
        }

        if ($productId > 0) {
            $params = [$categoryValue, $name, $description, $price, $isActive];
            $sql = 'UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, is_active = ?';
            if ($imagePath) {
                $sql .= ', image_path = ?';
                $params[] = $imagePath;
            }
            $sql .= ' WHERE id = ?';
            $params[] = $productId;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            flash('success', 'Товар обновлен.');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO products (category_id, name, description, price, image_path, is_active)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$categoryValue, $name, $description, $price, $imagePath, $isActive]);
            flash('success', 'Товар создан.');
        }

        redirect('/admin/index.php');
    }
}

$page_title = $product ? 'Редактировать товар' : 'Добавить товар';
include __DIR__ . '/../partials/header.php';
?>

<section class="card">
    <h2><?= e($page_title) ?></h2>
    <form class="form" method="post" enctype="multipart/form-data" action="/admin/product_edit.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= (int) $productId ?>">
        <label>
            Название
            <input type="text" name="name" value="<?= e($product['name'] ?? '') ?>" required>
        </label>
        <label>
            Категория
            <select name="category_id">
                <option value="0">Без категории</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= ($product && (int) $product['category_id'] === (int) $category['id']) ? 'selected' : '' ?>>
                        <?= e($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Цена
            <input type="number" name="price" step="0.01" min="0" value="<?= e($product['price'] ?? '') ?>" required>
        </label>
        <label>
            Описание
            <textarea name="description" required><?= e($product['description'] ?? '') ?></textarea>
        </label>
        <label>
            Изображение (необязательно)
            <input type="file" name="image" accept="image/*">
        </label>
        <?php if (!empty($product['image_path'])): ?>
            <img class="product-image" src="<?= e($product['image_path']) ?>" alt="<?= e($product['name']) ?>">
        <?php endif; ?>
        <label>
            <input type="checkbox" name="is_active" <?= (!isset($product['is_active']) || $product['is_active']) ? 'checked' : '' ?>>
            Показывать в каталоге
        </label>
        <button type="submit">Сохранить</button>
    </form>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>
