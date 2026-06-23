<?php
require_once __DIR__ . '/db.php';

if (is_logged_in()) {
    redirect('/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Введите корректный email.');
    } elseif (strlen($password) < 6) {
        flash('error', 'Пароль должен быть не короче 6 символов.');
    } else {
        try {
            $pdo = db();
            $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)');
            $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT), 'user']);
            $_SESSION['user_id'] = (int) $pdo->lastInsertId();
            flash('success', 'Аккаунт создан.');
            redirect('/');
        } catch (Throwable $e) {
            flash('error', 'Email уже зарегистрирован.');
        }
    }
}

$page_title = 'Регистрация';
include __DIR__ . '/partials/header.php';
?>

<section class="card">
    <h2>Регистрация</h2>
    <form class="form" method="post" action="/register.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>
            Эл. почта
            <input type="email" name="email" required>
        </label>
        <label>
            Пароль
            <input type="password" name="password" required>
        </label>
        <button type="submit">Создать аккаунт</button>
    </form>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
