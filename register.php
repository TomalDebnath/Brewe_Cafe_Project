<?php
require_once __DIR__ . '/config.php';
if (current_user()) redirect('index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? ''); $username = trim($_POST['username'] ?? ''); $password = $_POST['password'] ?? '';
    if ($name === '' || !preg_match('/^[A-Za-z0-9_]{3,50}$/', $username) || strlen($password) < 8) {
        $error = 'Enter your name, a username (3–50 letters/numbers/underscores), and a password of at least 8 characters.';
    } else {
        try {
            $stmt = db()->prepare("INSERT INTO users (username,password_hash,name,role) VALUES (?,?,?,'customer')");
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $name]);
            flash('Account created. Please log in.'); redirect('login.php');
        } catch (PDOException $ex) { $error = 'That username is already taken.'; }
    }
}
$pageTitle = 'Create account'; require __DIR__ . '/includes/header.php';
?>
<section class="auth-layout">
 <div class="auth-panel"><span class="eyebrow">JOIN THE BREWÉ COMMUNITY</span><h1>Create your <em>account.</em></h1><p class="muted">A few details and your next coffee is closer.</p>
 <?php if ($error): ?><div class="error-message"><?= e($error) ?></div><?php endif; ?>
 <form method="post" class="auth-form"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <label>Your name<input name="name" required maxlength="100" placeholder="Full name"></label>
  <label>Username<input name="username" required minlength="3" maxlength="50" placeholder="Choose a username"></label>
  <label>Password<input type="password" name="password" required minlength="8" placeholder="At least 8 characters"></label>
  <button class="button button-brown" type="submit">Create account →</button>
 </form><p class="auth-switch">Already registered? <a href="<?= BASE_URL ?>/login.php">Log in</a></p></div>
 <div class="auth-photo"><div><span class="eyebrow">A LITTLE MORE JOY</span><p>Find your<br>favourite ritual.</p></div></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
