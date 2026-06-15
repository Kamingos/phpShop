<?php
/**
 * Альтернативный вариант: страница заказов для пользователя
 * С live-обновлением через jQuery
 */

require_once __DIR__ . '/db.php';

require_login();
$user = current_user();

$page_title = 'Следить за заказами';
include __DIR__ . '/partials/header.php';
?>

<section>
    <h2 class="section-title">Следить за заказами</h2>
    <p>Статусы обновляются в реальном времени (каждые 5 секунд).</p>
    
    <div id="orders-live" data-feed-url="/api/user_orders.php"></div>
</section>

<script>
$(function() {
    var $live = $('#orders-live');
    var url = $live.data('feed-url');
    
    function load() {
        $.get(url, function(data) {
            if (data.html) {
                $live.html(data.html);
            }
        });
    }
    
    load();
    setInterval(load, 5000);
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
