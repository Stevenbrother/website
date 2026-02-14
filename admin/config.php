<?php
session_start();

define('DB_PATH', __DIR__ . '/../data/stock.sqlite');

define('ADMIN_USER_DEFAULT', getenv('ADMIN_USER') ?: 'admin');
define('ADMIN_PASSWORD_DEFAULT', getenv('ADMIN_PASSWORD') ?: 'admin123');
define('DEFAULT_LANG', 'id');

function get_supported_languages(): array
{
    return [
        'id' => 'Indonesia',
        'en' => 'English',
        'zh-CN' => '中文（简体）',
    ];
}

function current_lang(): string
{
    $supported = get_supported_languages();

    if (!empty($_GET['lang']) && isset($supported[$_GET['lang']])) {
        $_SESSION['lang'] = $_GET['lang'];
        return $_GET['lang'];
    }

    if (!empty($_SESSION['lang']) && isset($supported[$_SESSION['lang']])) {
        return $_SESSION['lang'];
    }

    $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    if (stripos($acceptLanguage, 'zh') === 0) {
        $_SESSION['lang'] = 'zh-CN';
        return 'zh-CN';
    }
    if (stripos($acceptLanguage, 'en') === 0) {
        $_SESSION['lang'] = 'en';
        return 'en';
    }
    if (stripos($acceptLanguage, 'id') === 0) {
        $_SESSION['lang'] = 'id';
        return 'id';
    }

    $_SESSION['lang'] = DEFAULT_LANG;
    return DEFAULT_LANG;
}

function admin_translations(): array
{
    return [
        'id' => [
            'app_name' => 'PrimaPlast Admin',
            'login_title' => 'Login Admin - PrimaPlast',
            'login_heading' => 'Masuk Admin',
            'login_subtitle' => 'Gunakan akun admin untuk mengelola stok.',
            'invalid_credentials' => 'Username atau password salah.',
            'username' => 'Username',
            'password' => 'Password',
            'login_button' => 'Masuk',
            'default_hint' => 'Default: admin / admin123 (ubah via env).',
            'stock_title' => 'Manajemen Stok - Admin',
            'stock_heading' => 'Manajemen Stok',
            'hello' => 'Halo',
            'logout' => 'Keluar',
            'notice_added' => 'Stok berhasil ditambahkan.',
            'notice_updated' => 'Stok berhasil diperbarui.',
            'notice_deleted' => 'Stok berhasil dihapus.',
            'add_stock_heading' => 'Tambah Stok Baru',
            'sku' => 'SKU',
            'product_name' => 'Nama Produk',
            'quantity' => 'Jumlah',
            'add' => 'Tambah',
            'stock_list_heading' => 'Daftar Stok',
            'no_stock' => 'Belum ada data stok.',
            'product' => 'Produk',
            'action' => 'Aksi',
            'save' => 'Simpan',
            'delete' => 'Hapus',
            'language' => 'Bahasa',
        ],
        'en' => [
            'app_name' => 'PrimaPlast Admin',
            'login_title' => 'Admin Login - PrimaPlast',
            'login_heading' => 'Admin Sign In',
            'login_subtitle' => 'Use an admin account to manage inventory.',
            'invalid_credentials' => 'Invalid username or password.',
            'username' => 'Username',
            'password' => 'Password',
            'login_button' => 'Sign In',
            'default_hint' => 'Default: admin / admin123 (set via env).',
            'stock_title' => 'Inventory Management - Admin',
            'stock_heading' => 'Inventory Management',
            'hello' => 'Hello',
            'logout' => 'Logout',
            'notice_added' => 'Stock item added successfully.',
            'notice_updated' => 'Stock updated successfully.',
            'notice_deleted' => 'Stock item deleted successfully.',
            'add_stock_heading' => 'Add New Stock',
            'sku' => 'SKU',
            'product_name' => 'Product Name',
            'quantity' => 'Quantity',
            'add' => 'Add',
            'stock_list_heading' => 'Stock List',
            'no_stock' => 'No stock data available yet.',
            'product' => 'Product',
            'action' => 'Action',
            'save' => 'Save',
            'delete' => 'Delete',
            'language' => 'Language',
        ],
        'zh-CN' => [
            'app_name' => 'PrimaPlast 管理后台',
            'login_title' => '管理员登录 - PrimaPlast',
            'login_heading' => '管理员登录',
            'login_subtitle' => '使用管理员账号管理库存。',
            'invalid_credentials' => '用户名或密码错误。',
            'username' => '用户名',
            'password' => '密码',
            'login_button' => '登录',
            'default_hint' => '默认账号：admin / admin123（可通过环境变量修改）。',
            'stock_title' => '库存管理 - 管理员',
            'stock_heading' => '库存管理',
            'hello' => '您好',
            'logout' => '退出',
            'notice_added' => '库存添加成功。',
            'notice_updated' => '库存更新成功。',
            'notice_deleted' => '库存删除成功。',
            'add_stock_heading' => '新增库存',
            'sku' => 'SKU',
            'product_name' => '产品名称',
            'quantity' => '数量',
            'add' => '添加',
            'stock_list_heading' => '库存列表',
            'no_stock' => '暂无库存数据。',
            'product' => '产品',
            'action' => '操作',
            'save' => '保存',
            'delete' => '删除',
            'language' => '语言',
        ],
    ];
}

function t(string $key): string
{
    $lang = current_lang();
    $translations = admin_translations();
    return $translations[$lang][$key] ?? $translations[DEFAULT_LANG][$key] ?? $key;
}

function lang_url(string $path, string $lang): string
{
    return $path . '?lang=' . urlencode($lang);
}

function require_admin(): void
{
    if (empty($_SESSION['admin_user'])) {
        header('Location: ' . lang_url('login.php', current_lang()));
        exit;
    }
}

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
?>
