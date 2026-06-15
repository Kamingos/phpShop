<?php
require_once __DIR__ . '/db.php';

require_login();
$user = current_user();

$page_title = 'Мои заказы';
include __DIR__ . '/partials/header.php';
?>

<section>
    <h2 class="section-title">Мои заказы</h2>
    <p>Статусы обновляются автоматически каждые 5 секунд.</p>
    
    <div id="user-orders">
        <p>Загрузка...</p>
    </div>
</section>

<script>
$(function() {
    function loadOrders() {
        $.get('/api/user_orders.php', function(data) {
            if (data.html && $('#user-orders').length) {
                $('#user-orders').html(data.html);
            }
        });
    }
    
    loadOrders();
    setInterval(loadOrders, 5000);
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
