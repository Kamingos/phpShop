<?php
/**
 * Скрипт для добавления тестовых данных
 * Запустить: php seed.php
 */

require_once __DIR__ . '/db.php';

$pdo = db();

// Очистка старых данных (кроме админа)
$pdo->exec('DELETE FROM order_items');
$pdo->exec('DELETE FROM orders');
$pdo->exec('DELETE FROM cart_items');
$pdo->exec('DELETE FROM products');
$pdo->exec('DELETE FROM categories');
$pdo->exec('DELETE FROM users WHERE email NOT IN ("admin@local")');

echo "Добавляем категории...\n";

$categories = [
    'Электроника',
    'Одежда',
    'Книги',
    'Дом и сад',
    'Спорт и отдых',
];

$categoryIds = [];
foreach ($categories as $name) {
    $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
    $stmt->execute([$name]);
    $categoryIds[$name] = $pdo->lastInsertId();
    echo "  + $name\n";
}

echo "\nДобавляем товары...\n";

$products = [
    ['name' => 'Смартфон XYZ', 'category' => 'Электроника', 'price' => 29999, 'description' => 'Отличный смартфон с хорошей камерой и долгим временем работы от батареи. 128GB памяти, 6 дюймов экран.'],
    ['name' => 'Ноутбук Pro 15', 'category' => 'Электроника', 'price' => 89999, 'description' => 'Мощный ноутбук для работы и игр. Процессор последнего поколения, 16GB RAM, SSD 512GB.'],
    ['name' => 'Беспроводные наушники', 'category' => 'Электроника', 'price' => 7999, 'description' => 'Качественный звук и комфортное ношение. До 20 часов работы без подзарядки.'],
    ['name' => 'Футболка хлопковая', 'category' => 'Одежда', 'price' => 1999, 'description' => 'Удобная футболка из 100% хлопка. Доступна в нескольких цветах и размерах.'],
    ['name' => 'Джинсы классические', 'category' => 'Одежда', 'price' => 4999, 'description' => 'Классические джинсы прямого кроя. Качественная ткань, долговечная носка.'],
    ['name' => 'Кроссовки беговые', 'category' => 'Спорт и отдых', 'price' => 8999, 'description' => 'Легкие и удобные кроссовки для бега и фитнеса. Дышащий материал, амортизация.'],
    ['name' => 'Гарри Поттер и философский камень', 'category' => 'Книги', 'price' => 1499, 'description' => 'Первая книга из легендарной серии о юном волшебнике. Твердый переплет, иллюстрации.'],
    ['name' => 'Sapiens. Краткая история человечества', 'category' => 'Книги', 'price' => 1899, 'description' => 'Бестселлер о истории человеческого рода. Интересное и понятное изложение.'],
    ['name' => 'Набор для выращивания растений', 'category' => 'Дом и сад', 'price' => 2499, 'description' => 'Начните свою домашнюю ферму! В наборе все необходимое для выращивания микрозелени.'],
    ['name' => 'Йога коврик', 'category' => 'Спорт и отдых', 'price' => 2999, 'description' => 'Не Скользящий коврик для йоги и фитнеса. Толщина 6мм, удобная текстура.'],
    ['name' => 'Умные часы FitTrack', 'category' => 'Электроника', 'price' => 12999, 'description' => 'Следите за здоровьем и уведомлениями на запястье. Пульс, шаги, сон, уведомления.'],
    ['name' => 'Куртка осенняя', 'category' => 'Одежда', 'price' => 7999, 'description' => 'Теплая и легкая куртка для прохладной погоды. Водонепроницаемая ткань.'],
];

foreach ($products as $product) {
    $stmt = $pdo->prepare('INSERT INTO products (category_id, name, description, price, is_active) VALUES (?, ?, ?, ?, 1)');
    $stmt->execute([
        $categoryIds[$product['category']],
        $product['name'],
        $product['description'],
        $product['price']
    ]);
    echo "  + {$product['name']}\n";
}

// Создаем тестового пользователя
echo "\nСоздаем тестового пользователя...\n";
$stmt = $pdo->prepare('INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)');
$stmt->execute([
    'user@test',
    password_hash('user123', PASSWORD_DEFAULT),
    'user'
]);
echo "  + user@test / user123\n";

// Создаем тестовый заказ
echo "\nСоздаем тестовый заказ...\n";
$stmt = $pdo->prepare('INSERT INTO orders (user_id, status) VALUES (?, ?)');
$stmt->execute([$pdo->query('SELECT id FROM users WHERE email = "user@test"')->fetchColumn(), 'processing']);
$orderId = $pdo->lastInsertId();

$stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, qty, price) VALUES (?, ?, ?, ?)');
$testProducts = $pdo->query('SELECT id, price FROM products LIMIT 3')->fetchAll(PDO::FETCH_ASSOC);
foreach ($testProducts as $i => $p) {
    $stmt->execute([$orderId, $p['id'], $i + 1, $p['price']]);
}
echo "  + Заказ #{$orderId} создан\n";

echo "\n✅ Готово! Тестовые данные добавлены.\n";
echo "\nДоступы:\n";
echo "  Админ: admin@local / admin123\n";
echo "  Пользователь: user@test / user123\n";
echo "\nЦены в рублях (₽)\n";
