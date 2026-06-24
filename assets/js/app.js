$(function () {
    // Live-обновление заказов (админка)
    var $live = $('#orders-live');
    if ($live.length) {
        var url = $live.data('feed-url');
        var isInteracting = false;

        var load = function () {
            if (isInteracting) {
                return;
            }

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
        
        // Отключаем автообновление пока админ изменяет статус заказа
        $(document).on('focusin', '.inline-form select, .inline-form button', function() {
            isInteracting = true;
        });

        $(document).on('focusout', '.inline-form select, .inline-form button', function() {
            setTimeout(function() {
                if (!$(document.activeElement).closest('.inline-form').length) {
                    isInteracting = false;
                }
            }, 10);
        });

        // Обработка отправки формы изменения статуса (AJAX)
        $(document).on('submit', '.inline-form', function(e) {
            e.preventDefault();
            var $form = $(this);
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                success: function() {
                    isInteracting = false;
                    // Сразу обновляем список заказов
                    load();
                },
                error: function() {
                    alert('Ошибка при обновлении статуса');
                }
            });
        });
    }
});
