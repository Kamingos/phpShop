<?php
require_once __DIR__ . '/../db.php';

require_admin();
check_csrf();

$id = (int) ($_POST['id'] ?? 0);
$status = (string) ($_POST['status'] ?? 'new');
$allowed = ['new', 'paid_test', 'processing', 'shipped', 'done', 'canceled'];

if ($id > 0 && in_array($status, $allowed, true)) {
    $stmt = db()->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);
    flash('success', 'Статус заказа обновлен.');
}

redirect('/admin/orders.php');
