<?php
require_once __DIR__ . '/db.php';

require_login();
$user = current_user();

// Получаем заказы с товарами
$stmt = db()->prepare(
    'SELECT o.id, o.status, o.created_at
     FROM orders o
     WHERE o.user_id = ?
     ORDER BY o.created_at DESC'
);
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statusLabels = [
    'new' => 'Новый',
    'paid_test' => 'Оплачен (тест)',
    'processing' => 'В работе',
    'shipped' => 'Отправлен',
    'done' => 'Завершен',
    'canceled' => 'Отменен',
];

$statusColors = [
    'new' => '#ede6da',
    'paid_test' => '#d4edda',
    'processing' => '#fff3cd',
    'shipped' => '#cce5ff',
    'done' => '#d4edda',
    'canceled' => '#f8d7da',
];

// Генерируем HTML
$html = '';
if (!$orders) {
    $html = '<div class="card"><p>У вас пока нет заказов.</p></div>';
} else {
    foreach ($orders as $order) {
        // Получаем товары заказа
        $stmt = db()->prepare(
            'SELECT oi.qty, oi.price, p.name, p.image_path
             FROM order_items oi
             JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = ?'
        );
        $stmt->execute([$order['id']]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total = 0;
        foreach ($items as $item) {
            $total += (float) $item['price'] * (int) $item['qty'];
        }
        
        $html .= '<div class="card order-card" data-order-id="' . (int) $order['id'] . '">';
        $html .= '<div class="order-header">';
        $html .= '<strong>Заказ #' . (int) $order['id'] . '</strong>';
        $html .= '<span class="status-chip" style="background: ' . ($statusColors[$order['status']] ?? '#ede6da') . '">';
        $html .= htmlspecialchars($statusLabels[$order['status']] ?? $order['status'], ENT_QUOTES, 'UTF-8');
        $html .= '</span>';
        $html .= '<span class="order-date">' . htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8') . '</span>';
        $html .= '</div>';
        $html .= '<div class="order-items">';
        
        foreach ($items as $item) {
            $html .= '<div class="order-item">';
            if ($item['image_path']) {
                $html .= '<img src="' . htmlspecialchars($item['image_path'], ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') . '" class="item-image">';
            }
            $html .= '<div class="item-info">';
            $html .= '<p><strong>' . htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') . '</strong></p>';
            $html .= '<p>' . (int) $item['qty'] . ' x ₽' . number_format((float) $item['price'], 0) . '</p>';
            $html .= '</div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '<div class="order-total"><strong>Итого: ₽' . number_format($total, 0) . '</strong></div>';
        $html .= '</div>';
    }
}

header('Content-Type: application/json');
echo json_encode(['html' => $html]);
