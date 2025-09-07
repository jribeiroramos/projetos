<?php
require __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/flash.php';
require_login();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $type  = $_POST['type'] ?? 'Cliente';
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $active = isset($_POST['is_active']) ? 1 : 0;

    if ($name && in_array($type, ['Cliente','Fornecedor','Outro'], true)) {
        $st = $pdo->prepare("INSERT INTO stakeholders (name, type, email, phone, notes, is_active) VALUES (?,?,?,?,?,?)");
        $st->execute([$name, $type, $email, $phone, $notes, $active]);
        set_flash('st_create_ok', 'Parte interessada criada.', 'success');
        header('Location: stakeholders_list.php');
        exit;
    } else {
        $error = 'Nome é obrigatório e tipo deve ser válido.';
    }
}

include __DIR__ . '/header.php';
?>
<h3>Nova Parte Interessada</h3>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="post" class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Nome</label>
    <input name="name" class="form-control" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Tipo</label>
    <select name="type" class="form-select">
      <option>Cliente</option>
      <option>Fornecedor</option>
      <option>Outro</option>
    </select>
  </div>
  <div class="col-md-3 form-check" style="padding-top:2.35rem">
    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
    <label class="form-check-label" for="is_active">Ativo</label>
  </div>
  <div class="col-md-6">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control">
  </div>
  <div class="col-md-6">
    <label class="form-label">Telefone</label>
    <input type="text" name="phone" class="form-control">
  </div>
  <div class="col-12">
    <label class="form-label">Notas</label>
    <textarea name="notes" class="form-control" rows="3"></textarea>
  </div>
  <div class="col-12">
    <button class="btn btn-primary">Salvar</button>
    <a href="stakeholders_list.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>
<?php include __DIR__ . '/footer.php'; ?>
