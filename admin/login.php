<?php
require_once __DIR__ . '/config.php';

if (!empty($_SESSION['admin_user'])) {
    header('Location: ' . lang_url('stocks.php', current_lang()));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT username, password_hash FROM users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['admin_user'] = $user['username'];
        header('Location: ' . lang_url('stocks.php', current_lang()));
        exit;
    }

    $error = t('invalid_credentials');
}

$lang = current_lang();
$languages = get_supported_languages();
?>
<!doctype html>
<html lang="<?php echo htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo htmlspecialchars(t('login_title'), ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="admin.css" />
  </head>
  <body>
    <main class="auth">
      <section class="card">
        <div class="lang-switcher">
          <span><?php echo htmlspecialchars(t('language'), ENT_QUOTES, 'UTF-8'); ?>:</span>
          <?php foreach ($languages as $code => $label): ?>
            <a class="<?php echo $lang === $code ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(lang_url('login.php', $code), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></a>
          <?php endforeach; ?>
        </div>
        <h1><?php echo htmlspecialchars(t('login_heading'), ENT_QUOTES, 'UTF-8'); ?></h1>
        <p><?php echo htmlspecialchars(t('login_subtitle'), ENT_QUOTES, 'UTF-8'); ?></p>
        <?php if ($error): ?>
          <div class="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="post" class="form">
          <label>
            <?php echo htmlspecialchars(t('username'), ENT_QUOTES, 'UTF-8'); ?>
            <input type="text" name="username" required />
          </label>
          <label>
            <?php echo htmlspecialchars(t('password'), ENT_QUOTES, 'UTF-8'); ?>
            <input type="password" name="password" required />
          </label>
          <button type="submit"><?php echo htmlspecialchars(t('login_button'), ENT_QUOTES, 'UTF-8'); ?></button>
        </form>
        <p class="hint"><?php echo htmlspecialchars(t('default_hint'), ENT_QUOTES, 'UTF-8'); ?></p>
      </section>
    </main>
  </body>
</html>
