<?php
declare(strict_types=1);

require __DIR__ . '/_ui.php';
admin_session();

$error = '';
$ip = $_SERVER['REMOTE_ADDR'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (login_blocked($ip)) {
        $error = 'Too many failed attempts. Try again in 15 minutes.';
    } else {
        $user = trim((string)($_POST['username'] ?? ''));
        $pass = (string)($_POST['password'] ?? '');
        $hash = cfg('admins')[$user] ?? '';
        // Verify against a dummy hash when the user is unknown to keep timing similar.
        $ok = password_verify($pass, $hash ?: '$2y$10$usesomesillystringforsaleuQ3sdy2d9cZ0e3H0k5Q3Q3Q3Q3Q3Q3Q');
        if ($hash && $ok) {
            session_regenerate_id(true);
            $_SESSION['admin'] = $user;
            header('Location: index.php');
            exit;
        }
        record_failed_login($ip);
        $error = 'Invalid username or password.';
    }
}

admin_head('Login', false);
?>
<div class="card" style="max-width:360px;margin:80px auto">
  <h2>Admin login</h2>
  <?php if ($error): ?><p class="err"><?= h($error) ?></p><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <p><input name="username" placeholder="Username" required autofocus style="width:100%;box-sizing:border-box"></p>
    <p><input type="password" name="password" placeholder="Password" required style="width:100%;box-sizing:border-box"></p>
    <button type="submit">Sign in</button>
  </form>
</div>
<?php admin_foot();
