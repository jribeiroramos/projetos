<?php
require __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/flash.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare("SELECT * FROM stakeholders WHERE id = ?");
$st->execute([$id]);
$row = $st->fetch();
if (!$row) { http_response_code(404); exit('Registro não encontrado'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $type  = $_POST['type'] ?? 'Cliente';
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $active = isset($_POST['is_active']) ? 1 : 0;

    if ($name && in_array($type, ['Cliente','Fornecedor','Outro'], true)) {
        $upd = $pdo->prepare("UPDATE stakeholders SET name=?, type=?, email=?, phone=?, notes=?, is_active=? WHERE id=?");
        $upd->execute([$name, $type, $email, $phone, $notes, $active, $id]);
        set_flash('st_edit_ok', 'Parte interessada atualizada.', 'success');
        header('Location: stakeholders_list.php');
        exit;
    } else {
        $error = 'Nome é obrigatório e tipo deve ser válido.';
    }
}

include __DIR__ . '/header.php';
?>
<h3>Editar Parte Interessada</h3>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="post" class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Nome</label>
    <input name="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Tipo</label>
    <select name="type" class="form-select">
      <?php foreach (['Cliente','Fornecedor','Outro'] as $t): ?>
        <option <?= $row['type']===$t ? 'selected':'' ?>><?= $t ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3 form-check" style="padding-top:2.35rem">
    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= $row['is_active'] ? 'checked':'' ?>>
    <label class="form-check-label" for="is_active">Ativo</label>
  </div>
  <div class="col-md-6">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($row['email'] ?? '') ?>">
  </div>
  <div class="col-md-6">
    <label class="form-label">Telefone</label>
    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($row['phone'] ?? '') ?>">
  </div>
  <div class="col-12">
    <label class="form-label">Notas</label>
    <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($row['notes'] ?? '') ?></textarea>
  </div>
  <div class="col-12">
    <button class="btn btn-primary">Salvar</button>
    <a href="stakeholders_list.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>
<?php include __DIR__ . '/footer.php'; ?>
