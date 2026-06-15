<?php
require_once __DIR__ . '/../db.php';

require_admin();

$page_title = 'Заказы онлайн';
include __DIR__ . '/../partials/header.php';
?>

<section>
    <h2 class="section-title">Заказы онлайн</h2>
    <p>Статусы обновляются автоматически каждые 5 секунд. Администратор может менять статус заказа.</p>
    
    <div id="orders-live" data-feed-url="/admin/orders_feed.php"></div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>
