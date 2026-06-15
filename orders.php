<?php
/**
 * Страница заказов пользователя (старая версия)
 * Перенаправляет на my_orders.php с live-обновлением
 */
require_once __DIR__ . '/db.php';

require_login();
redirect('/my_orders.php');
