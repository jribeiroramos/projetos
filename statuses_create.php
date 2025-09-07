<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $order = (int)($_POST['sort_order'] ?? 0);
    $active = isset($_POST['is_active']) ? 1 : 0;

    if ($name) {
        $stmt = $pdo->prepare("INSERT INTO statuses (name, sort_order, is_active) VALUES (?,?,?)");
        $stmt->execute([$name, $order, $active]);
        header("Location: statuses_list.php");
        exit;
    } else {
        $error = "Nome é obrigatório.";
    }
}

include __DIR__ . '/header.php';
?>
<h3>Novo Status</h3>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="post" class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Nome</label>
    <input type="text" name="name" class="form-control" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Ordem</label>
    <input type="number" name="sort_order" class="form-control" value="0">
  </div>
  <div class="col-md-3 form-check" style="padding-top: 2.35rem;">
    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
    <label class="form-check-label" for="is_active">Ativo</label>
  </div>
  <div class="col-12">
    <button class="btn btn-primary">Salvar</button>
    <a href="statuses_list.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>
<?php include __DIR__ . '/footer.php'; ?>
