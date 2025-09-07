<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM statuses WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    http_response_code(404);
    exit("Status não encontrado.");
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name'] ?? '');
    $order  = (int)($_POST['sort_order'] ?? 0);
    $active = isset($_POST['is_active']) ? 1 : 0;

    if ($name) {
        $upd = $pdo->prepare("UPDATE statuses SET name=?, sort_order=?, is_active=? WHERE id=?");
        $upd->execute([$name, $order, $active, $id]);
        header("Location: statuses_list.php");
        exit;
    } else {
        $error = "Nome é obrigatório.";
    }
}

include __DIR__ . '/header.php';
?>
<h3>Editar Status</h3>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="post" class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Nome</label>
    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Ordem</label>
    <input type="number" name="sort_order" class="form-control" value="<?= (int)$row['sort_order'] ?>">
  </div>
  <div class="col-md-3 form-check" style="padding-top: 2.35rem;">
    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?= $row['is_active'] ? 'checked' : '' ?>>
    <label class="form-check-label" for="is_active">Ativo</label>
  </div>
  <div class="col-12">
    <button class="btn btn-primary">Salvar</button>
    <a href="statuses_list.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>
<?php include __DIR__ . '/footer.php'; ?>
