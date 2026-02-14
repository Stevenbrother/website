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
            $notice = 'Stok berhasil ditambahkan.';
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
        $notice = 'Stok berhasil diperbarui.';
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $delete = $pdo->prepare('DELETE FROM stocks WHERE id = :id');
        $delete->execute([':id' => $id]);
        $notice = 'Stok berhasil dihapus.';
    }
}

$stocks = $pdo->query('SELECT id, sku, name, quantity FROM stocks ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manajemen Stok - Admin</title>
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
      <h1>Manajemen Stok</h1>
      <div class="topbar-actions">
        <span>Halo, <?php echo htmlspecialchars($_SESSION['admin_user'], ENT_QUOTES, 'UTF-8'); ?></span>
        <a href="logout.php">Keluar</a>
      </div>
    </header>
    <main class="container">
      <?php if ($notice): ?>
        <div class="notice"><?php echo htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>
      <section class="card">
        <h2>Tambah Stok Baru</h2>
        <form method="post" class="grid">
          <input type="hidden" name="action" value="add" />
          <label>
            SKU
            <input type="text" name="sku" required />
          </label>
          <label>
            Nama Produk
            <input type="text" name="name" required />
          </label>
          <label>
            Jumlah
            <input type="number" name="quantity" value="0" min="0" required />
          </label>
          <button type="submit">Tambah</button>
        </form>
      </section>
      <section class="card">
        <h2>Daftar Stok</h2>
        <?php if (!$stocks): ?>
          <p class="empty">Belum ada data stok.</p>
        <?php else: ?>
          <div class="table">
            <div class="table-header">
              <span>SKU</span>
              <span>Produk</span>
              <span>Jumlah</span>
              <span>Aksi</span>
            </div>
            <?php foreach ($stocks as $stock): ?>
              <div class="table-row">
                <span><?php echo htmlspecialchars($stock['sku'], ENT_QUOTES, 'UTF-8'); ?></span>
                <span><?php echo htmlspecialchars($stock['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                <form method="post" class="inline-form">
                  <input type="hidden" name="action" value="update" />
                  <input type="hidden" name="id" value="<?php echo (int) $stock['id']; ?>" />
                  <input type="number" name="quantity" value="<?php echo (int) $stock['quantity']; ?>" min="0" />
                  <button type="submit">Simpan</button>
                </form>
                <form method="post" class="inline-form">
                  <input type="hidden" name="action" value="delete" />
                  <input type="hidden" name="id" value="<?php echo (int) $stock['id']; ?>" />
                  <button type="submit" class="danger">Hapus</button>
                </form>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </main>
  </body>
</html>
