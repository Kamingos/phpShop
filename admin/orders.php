<?php
require_once __DIR__ . '/../db.php';

require_admin();

$page_title = 'Заказы онлайн';
include __DIR__ . '/../partials/header.php';
?>

<section class="card">
    <h2>Заказы онлайн</h2>
    <p>Обновляется каждые 5 секунд.</p>
    <div id="orders-live" data-feed-url="/admin/orders_feed.php"></div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>
