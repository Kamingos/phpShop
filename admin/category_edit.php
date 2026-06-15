<?php
require_once __DIR__ . '/../db.php';

require_admin();
check_csrf();

$action = $_POST['action'] ?? '';
$name = trim((string) ($_POST['name'] ?? ''));
$id = (int) ($_POST['id'] ?? 0);

if ($action === 'add') {
    if ($name !== '') {
        $stmt = db()->prepare('INSERT INTO categories (name) VALUES (?)');
        $stmt->execute([$name]);
        flash('success', 'Категория добавлена.');
    }
}

if ($action === 'update' && $id > 0) {
    if ($name !== '') {
        $stmt = db()->prepare('UPDATE categories SET name = ? WHERE id = ?');
        $stmt->execute([$name, $id]);
        flash('success', 'Категория обновлена.');
    }
}

if ($action === 'delete' && $id > 0) {
    $stmt = db()->prepare('DELETE FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    flash('success', 'Категория удалена.');
}

redirect('/admin/index.php');
