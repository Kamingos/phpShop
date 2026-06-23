$(function () {
    // Live-обновление заказов (админка)
    var $live = $('#orders-live');
    if ($live.length) {
        var url = $live.data('feed-url');
        var load = function () {
            $.ajax({
                url: url,
                dataType: 'json',
                success: function (data) {
                    if (data && data.html) {
                        $live.html(data.html);
                    }
                },
                error: function() {
                    console.error('Ошибка при загрузке заказов');
                }
            });
        };
        load();
        setInterval(load, 5000);
    }
});
