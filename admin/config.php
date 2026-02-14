<?php
session_start();

define('DB_PATH', __DIR__ . '/../data/stock.sqlite');

define('ADMIN_USER_DEFAULT', getenv('ADMIN_USER') ?: 'admin');
define('ADMIN_PASSWORD_DEFAULT', getenv('ADMIN_PASSWORD') ?: 'admin123');

if (!is_dir(dirname(DB_PATH))) {
    mkdir(dirname(DB_PATH), 0755, true);
}

$pdo = new PDO('sqlite:' . DB_PATH);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS stocks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sku TEXT NOT NULL,
    name TEXT NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 0
)');

$stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
$stmt->execute([':username' => ADMIN_USER_DEFAULT]);
if (!$stmt->fetch()) {
    $passwordHash = password_hash(ADMIN_PASSWORD_DEFAULT, PASSWORD_DEFAULT);
    $insert = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)');
    $insert->execute([
        ':username' => ADMIN_USER_DEFAULT,
        ':password_hash' => $passwordHash,
    ]);
}

function require_admin(): void
{
    if (empty($_SESSION['admin_user'])) {
        header('Location: login.php');
        exit;
    }
}
?>
