<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();

// filtro por status (opcional)
$status_id = isset($_GET['status_id']) && $_GET['status_id'] !== '' ? (int)$_GET['status_id'] : null;

// carregar status para o filtro
$statuses = $pdo->query('SELECT id, name FROM statuses WHERE is_active = 1 ORDER BY sort_order, name')->fetchAll();

// montar query da lista

$sql = "SELECT p.*, s.name AS status_name, st.name AS stakeholder_name, st.type AS stakeholder_type
        FROM projects p
        JOIN statuses s ON s.id = p.status_id
        LEFT JOIN stakeholders st ON st.id = p.stakeholder_id";

$params = [];

if ($status_id !== null) {
    $sql .= ' WHERE p.status_id = ?';
    $params[] = $status_id;
}
$sql .= ' ORDER BY s.sort_order, p.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

include __DIR__ . '/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="m-0">Projetos</h3>
  <div>
    <a href="projects_create.php" class="btn btn-success">+ Novo Projeto</a>
  </div>
</div>

<form class="row row-cols-lg-auto g-2 align-items-center mb-3" method="get">
  <div class="col-12">
    <label class="form-label me-2">Status</label>
    <select class="form-select" name="status_id" onchange="this.form.submit()">
      <option value="">Todos</option>
      <?php foreach ($statuses as $st): ?>
        <option value="<?= (int)$st['id'] ?>" <?= $status_id===$st['id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($st['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php if ($status_id !== null): ?>
    <div class="col-12">
      <a class="btn btn-outline-secondary" href="index.php">Limpar</a>
    </div>
  <?php endif; ?>
</form>

<div class="table-responsive">
<table class="table table-striped align-middle">
  <thead>
    <tr>
      <th>Título</th>
      <th>Status</th>
      <th>Quem pediu</th>
      <th>Datas</th>
      <th style="width:160px">Ações</th>
    </tr>
  </thead>
  <tbody>
  <?php if (!$projects): ?>
    <tr><td colspan="5" class="text-muted">Nenhum projeto cadastrado.</td></tr>
  <?php else: ?>
    <?php foreach ($projects as $p): ?>
      <tr>
        <td><?= htmlspecialchars($p['title']) ?></td>
        <td><span class="badge bg-secondary"><?= htmlspecialchars($p['status_name']) ?></span></td>
        <td>
  <?php
    if (!empty($p['stakeholder_name'])) {
      echo htmlspecialchars($p['stakeholder_name']) .
           ' <span class="text-muted small">(' . htmlspecialchars($p['stakeholder_type']) . ')</span>';
    } else {
      echo htmlspecialchars($p['requested_by'] ?? '');
    }
  ?>
</td>

        <td class="small">
          <?php if ($p['date_idea'])  echo 'Ideia: '    . htmlspecialchars($p['date_idea'])  . '<br>'; ?>
          <?php if ($p['date_start']) echo 'Início: '   . htmlspecialchars($p['date_start']) . '<br>'; ?>
          <?php if ($p['date_due'])   echo 'Prazo: '    . htmlspecialchars($p['date_due'])   . '<br>'; ?>
          <?php if ($p['date_done'])  echo 'Concluído: '. htmlspecialchars($p['date_done']); ?>
        </td>
        <td>
          <a class="btn btn-sm btn-primary" href="projects_edit.php?id=<?= (int)$p['id'] ?>">Editar</a>
          <a class="btn btn-sm btn-outline-danger"
             href="projects_delete.php?id=<?= (int)$p['id'] ?>"
             onclick="return confirm('Excluir este projeto?')">Excluir</a>
        </td>
      </tr>
    <?php endforeach; ?>
  <?php endif; ?>
  </tbody>
</table>
</div>

<?php include __DIR__ . '/footer.php'; ?>
