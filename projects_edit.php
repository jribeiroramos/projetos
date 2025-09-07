<?php
require __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/flash.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$id]);
$project = $stmt->fetch();
if (!$project) {
    http_response_code(404);
    exit("Projeto não encontrado.");
}

// status e stakeholders
$statuses = $pdo->query("SELECT id, name FROM statuses WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
$stakeholders = $pdo->query("SELECT id, name, type FROM stakeholders WHERE is_active = 1 ORDER BY name")->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title         = trim($_POST['title'] ?? '');
    $status_id     = (int)($_POST['status_id'] ?? 0);

    $stakeholder_id = ($_POST['stakeholder_id'] ?? '') !== '' ? (int)$_POST['stakeholder_id'] : null;
    $requested_by   = trim($_POST['requested_by'] ?? '');

    $description   = trim($_POST['description'] ?? '');
    $date_idea     = $_POST['date_idea']  ?: null;
    $date_start    = $_POST['date_start'] ?: null;
    $date_due      = $_POST['date_due']   ?: null;
    $date_done     = $_POST['date_done']  ?: null;

    if ($title && $status_id) {
        $upd = $pdo->prepare("
            UPDATE projects
               SET title=?, requested_by=?, stakeholder_id=?, status_id=?, description=?, date_idea=?, date_start=?, date_due=?, date_done=?
             WHERE id=?
        ");
        $upd->execute([
            $title, $requested_by, $stakeholder_id, $status_id, $description,
            $date_idea, $date_start, $date_due, $date_done, $id
        ]);

        set_flash('project_saved', 'Projeto atualizado com sucesso.', 'success');
        header("Location: index.php");
        exit;
    } else {
        $error = "Título e Status são obrigatórios.";
    }
}

include __DIR__ . '/header.php';
?>
<h3>Editar Projeto</h3>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<form method="post" class="row g-3">
  <div class="col-md-8">
    <label class="form-label">Título</label>
    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($project['title']) ?>" required>
  </div>

  <div class="col-md-4">
    <label class="form-label">Status</label>
    <select name="status_id" class="form-select" required>
      <?php foreach ($statuses as $s): ?>
        <option value="<?= (int)$s['id'] ?>" <?= ((int)$project['status_id'] === (int)$s['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($s['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-6">
    <label class="form-label">Parte interessada (cadastrada)</label>
    <select name="stakeholder_id" class="form-select">
      <option value="">— Selecionar —</option>
      <?php foreach ($stakeholders as $st): ?>
        <option value="<?= (int)$st['id'] ?>"
          <?= ((int)$project['stakeholder_id'] === (int)$st['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($st['name']) ?> (<?= htmlspecialchars($st['type']) ?>)
        </option>
      <?php endforeach; ?>
    </select>
    <div class="form-text">Se não existir na lista, use o campo “Quem pediu”.</div>
  </div>

  <div class="col-md-6">
    <label class="form-label">Quem pediu (texto livre)</label>
    <input type="text" name="requested_by" class="form-control"
           value="<?= htmlspecialchars($project['requested_by'] ?? '') ?>">
  </div>

  <div class="col-md-3">
    <label class="form-label">Data da ideia</label>
    <input type="date" name="date_idea" class="form-control" value="<?= htmlspecialchars($project['date_idea'] ?? '') ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Início</label>
    <input type="date" name="date_start" class="form-control" value="<?= htmlspecialchars($project['date_start'] ?? '') ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Prazo</label>
    <input type="date" name="date_due" class="form-control" value="<?= htmlspecialchars($project['date_due'] ?? '') ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Concluído em</label>
    <input type="date" name="date_done" class="form-control" value="<?= htmlspecialchars($project['date_done'] ?? '') ?>">
  </div>

  <div class="col-12">
    <label class="form-label">Descrição</label>
    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
  </div>

  <div class="col-12">
    <button class="btn btn-primary">Salvar</button>
    <a href="index.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php include __DIR__ . '/footer.php'; ?>
