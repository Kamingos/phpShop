<?php
require_once __DIR__ . '/../db.php';

require_admin();
check_csrf();

$id = (int) ($_POST['id'] ?? 0);
if ($id > 0) {
    $stmt = db()->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$id]);
    flash('success', 'Товар удален.');
}

redirect('/admin/index.php');
