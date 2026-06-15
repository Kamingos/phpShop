$(function () {
    // Live-обновление заказов (админка)
    var $live = $('#orders-live');
    if ($live.length) {
        var url = $live.data('feed-url');
        var load = function () {
            $.get(url, function (data) {
                if (data && data.html) {
                    $live.html(data.html);
                }
            });
        };
        load();
        setInterval(load, 5000);
    }
});
