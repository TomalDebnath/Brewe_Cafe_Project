<?php
declare(strict_types=1);

define('DB_HOST', 'localhost');
define('DB_NAME', 'north_end_cafe');
define('DB_USER', 'root');
define('DB_PASS', '');

define('BASE_URL', '/Brewe_Cafe_Project'); // Change if your folder name is different.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never {
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sent = $_POST['csrf'] ?? '';
        if (!hash_equals($_SESSION['csrf'] ?? '', $sent)) {
            http_response_code(419);
            exit('Your form session expired. Please go back and try again.');
        }
    }
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_login(): void {
    if (!current_user()) {
        $_SESSION['flash'] = 'Please log in to continue.';
        redirect('login.php');
    }
}

function require_admin(): void {
    require_login();
    if ((current_user()['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Access denied.');
    }
}

function flash(string $message = ''): ?string {
    if ($message !== '') {
        $_SESSION['flash'] = $message;
        return null;
    }
    $msg = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $msg;
}

/* cart_items.session_token references sessions.token in the supplied schema.
   A secure random token is created for each authenticated PHP session. */
function cart_token(): string {
    require_login();
    if (empty($_SESSION['cart_token'])) {
        $token = bin2hex(random_bytes(32));
        $stmt = db()->prepare('INSERT INTO sessions (token, user_id) VALUES (?, ?)');
        $stmt->execute([$token, current_user()['id']]);
        $_SESSION['cart_token'] = $token;
    }
    return $_SESSION['cart_token'];
}
