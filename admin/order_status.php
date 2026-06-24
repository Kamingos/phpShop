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

// Если это AJAX запрос, возвращаем JSON вместо редиректа
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} else {
    redirect('/admin/orders.php');
}
