<?php
require_once __DIR__ . '/config.php';
if (current_user()) redirect('index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = db()->prepare('SELECT id, username, password_hash, name, role FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]); $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id'=>(int)$user['id'], 'username'=>$user['username'], 'name'=>$user['name'], 'role'=>$user['role']];
        unset($_SESSION['cart_token']);
        flash('Welcome back, ' . $user['name'] . '.');
        redirect('index.php');
    } else { $error = 'The username or password is incorrect.'; }
}
$pageTitle = 'Welcome back';
require __DIR__ . '/includes/header.php';
?>

<section class="auth-layout auth-layout-no-image">
  <div class="auth-panel">
    <span class="eyebrow">YOUR TABLE IS WAITING</span>
    <h1>Welcome <em>back.</em></h1>
    <p class="muted">Log in to order your favourites.</p>

    <?php if ($error): ?>
      <div class="error-message"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" class="auth-form">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <label>Username
        <input name="username" autocomplete="username" required placeholder="Enter your username">
      </label>

      <label>Password
        <input type="password" name="password" autocomplete="current-password" required placeholder="Enter your password">
      </label>

      <button class="button button-brown" type="submit">
        Log in <span>→</span>
      </button>
    </form>

    <p class="auth-switch">
      New to Brewé? <a href="<?= BASE_URL ?>/register.php">Create an account</a>
    </p>

    <div class="demo-hint">
      <strong>Demo accounts</strong><br>
      Customer: customer / customer123<br>
      Admin: admin / admin123
    </div>
  </div>
</section>
