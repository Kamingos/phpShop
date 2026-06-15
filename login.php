<?php
require_once __DIR__ . '/db.php';

if (is_logged_in()) {
    redirect('/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = (int) $user['id'];
        flash('success', 'С возвращением.');
        redirect('/');
    }

    flash('error', 'Неверный логин или пароль.');
}

$page_title = 'Вход';
include __DIR__ . '/partials/header.php';
?>

<section class="card">
    <h2>Вход</h2>
    <form class="form" method="post" action="/login.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>
            Эл. почта
            <input type="email" name="email" required>
        </label>
        <label>
            Пароль
            <input type="password" name="password" required>
        </label>
        <button type="submit">Войти</button>
    </form>
    <p>Демо-админ: admin@local / admin123</p>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
