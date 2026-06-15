$(function () {
    var $live = $('#orders-live');
    if ($live.length) {
        var url = $live.data('feed-url');
        var load = function () {
            $.get(url, function (html) {
                $live.html(html);
            });
        };
        load();
        setInterval(load, 5000);
    }
});
