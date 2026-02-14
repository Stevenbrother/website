<?php
require_once __DIR__ . '/config.php';

require_admin();

$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $sku = trim($_POST['sku'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $quantity = (int) ($_POST['quantity'] ?? 0);

        if ($sku && $name) {
            $insert = $pdo->prepare('INSERT INTO stocks (sku, name, quantity) VALUES (:sku, :name, :quantity)');
            $insert->execute([
                ':sku' => $sku,
                ':name' => $name,
                ':quantity' => $quantity,
            ]);
            $notice = t('notice_added');
        }
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 0);

        $update = $pdo->prepare('UPDATE stocks SET quantity = :quantity WHERE id = :id');
        $update->execute([
            ':quantity' => $quantity,
            ':id' => $id,
        ]);
        $notice = t('notice_updated');
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $delete = $pdo->prepare('DELETE FROM stocks WHERE id = :id');
        $delete->execute([':id' => $id]);
        $notice = t('notice_deleted');
    }
}

$stocks = $pdo->query('SELECT id, sku, name, quantity FROM stocks ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
$lang = current_lang();
$languages = get_supported_languages();
?>
<!doctype html>
<html lang="<?php echo htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo htmlspecialchars(t('stock_title'), ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="admin.css" />
  </head>
  <body>
    <header class="topbar">
      <h1><?php echo htmlspecialchars(t('stock_heading'), ENT_QUOTES, 'UTF-8'); ?></h1>
      <div class="topbar-actions">
        <div class="lang-switcher">
          <span><?php echo htmlspecialchars(t('language'), ENT_QUOTES, 'UTF-8'); ?>:</span>
          <?php foreach ($languages as $code => $label): ?>
            <a class="<?php echo $lang === $code ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(lang_url('stocks.php', $code), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></a>
          <?php endforeach; ?>
        </div>
        <span><?php echo htmlspecialchars(t('hello'), ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($_SESSION['admin_user'], ENT_QUOTES, 'UTF-8'); ?></span>
        <a href="<?php echo htmlspecialchars(lang_url('logout.php', $lang), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(t('logout'), ENT_QUOTES, 'UTF-8'); ?></a>
      </div>
    </header>
    <main class="container">
      <?php if ($notice): ?>
        <div class="notice"><?php echo htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>
      <section class="card">
        <h2><?php echo htmlspecialchars(t('add_stock_heading'), ENT_QUOTES, 'UTF-8'); ?></h2>
        <form method="post" class="grid">
          <input type="hidden" name="action" value="add" />
          <label>
            <?php echo htmlspecialchars(t('sku'), ENT_QUOTES, 'UTF-8'); ?>
            <input type="text" name="sku" required />
          </label>
          <label>
            <?php echo htmlspecialchars(t('product_name'), ENT_QUOTES, 'UTF-8'); ?>
            <input type="text" name="name" required />
          </label>
          <label>
            <?php echo htmlspecialchars(t('quantity'), ENT_QUOTES, 'UTF-8'); ?>
            <input type="number" name="quantity" value="0" min="0" required />
          </label>
          <button type="submit"><?php echo htmlspecialchars(t('add'), ENT_QUOTES, 'UTF-8'); ?></button>
        </form>
      </section>
      <section class="card">
        <h2><?php echo htmlspecialchars(t('stock_list_heading'), ENT_QUOTES, 'UTF-8'); ?></h2>
        <?php if (!$stocks): ?>
          <p class="empty"><?php echo htmlspecialchars(t('no_stock'), ENT_QUOTES, 'UTF-8'); ?></p>
        <?php else: ?>
          <div class="table">
            <div class="table-header">
              <span><?php echo htmlspecialchars(t('sku'), ENT_QUOTES, 'UTF-8'); ?></span>
              <span><?php echo htmlspecialchars(t('product'), ENT_QUOTES, 'UTF-8'); ?></span>
              <span><?php echo htmlspecialchars(t('quantity'), ENT_QUOTES, 'UTF-8'); ?></span>
              <span><?php echo htmlspecialchars(t('action'), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <?php foreach ($stocks as $stock): ?>
              <div class="table-row">
                <span><?php echo htmlspecialchars($stock['sku'], ENT_QUOTES, 'UTF-8'); ?></span>
                <span><?php echo htmlspecialchars($stock['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                <form method="post" class="inline-form">
                  <input type="hidden" name="action" value="update" />
                  <input type="hidden" name="id" value="<?php echo (int) $stock['id']; ?>" />
                  <input type="number" name="quantity" value="<?php echo (int) $stock['quantity']; ?>" min="0" />
                  <button type="submit"><?php echo htmlspecialchars(t('save'), ENT_QUOTES, 'UTF-8'); ?></button>
                </form>
                <form method="post" class="inline-form">
                  <input type="hidden" name="action" value="delete" />
                  <input type="hidden" name="id" value="<?php echo (int) $stock['id']; ?>" />
                  <button type="submit" class="danger"><?php echo htmlspecialchars(t('delete'), ENT_QUOTES, 'UTF-8'); ?></button>
                </form>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </main>
  </body>
</html>
