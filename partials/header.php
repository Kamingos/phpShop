<?php
$user = current_user();
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title ?? 'Шопик') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
<div class="page">
    <header class="site-header">
        <div class="logo"><a href="/">Шопик</a></div>
        <nav class="site-nav">
            <a href="/">Каталог</a>
            <?php if ($user): ?>
                <a href="/cart.php">Корзина</a>
                <a href="/my_orders.php">Заказы</a>
                <a href="/profile.php">Профиль</a>
            <?php endif; ?>
            <?php if ($user && $user['role'] === 'admin'): ?>
                <a href="/admin/index.php">Админка</a>
                <a href="/admin/orders.php">Заказы онлайн</a>
            <?php endif; ?>
            <span class="nav-spacer"></span>
            <?php if ($user): ?>
                <span class="nav-user"><?= e($user['email']) ?></span>
                <a href="/logout.php">Выйти</a>
            <?php else: ?>
                <a href="/login.php">Войти</a>
                <a href="/register.php">Регистрация</a>
            <?php endif; ?>
        </nav>
    </header>
    <main class="site-main">
        <?php if ($msg = flash('success')): ?>
            <div class="alert success"><?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = flash('error')): ?>
            <div class="alert error"><?= e($msg) ?></div>
        <?php endif; ?>
