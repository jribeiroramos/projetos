<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();

$statuses = $pdo->query("SELECT * FROM statuses ORDER BY sort_order, name")->fetchAll();

include __DIR__ . '/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="m-0">Status</h3>
  <a href="statuses_create.php" class="btn btn-success">+ Novo Status</a>
</div>

<div class="table-responsive">
<table class="table table-striped align-middle">
  <thead>
    <tr>
      <th>Nome</th>
      <th>Ordem</th>
      <th>Ativo</th>
      <th style="width:160px">Ações</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!$statuses): ?>
      <tr><td colspan="4" class="text-muted">Nenhum status cadastrado.</td></tr>
    <?php else: ?>
      <?php foreach ($statuses as $s): ?>
        <tr>
          <td><?= htmlspecialchars($s['name']) ?></td>
          <td><?= (int)$s['sort_order'] ?></td>
          <td><?= $s['is_active'] ? 'Sim' : 'Não' ?></td>
          <td>
            <a class="btn btn-sm btn-primary" href="statuses_edit.php?id=<?= (int)$s['id'] ?>">Editar</a>
            <a class="btn btn-sm btn-outline-danger"
               href="statuses_delete.php?id=<?= (int)$s['id'] ?>"
               onclick="return confirm('Excluir este status?')">Excluir</a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>
</div>
<?php include __DIR__ . '/footer.php'; ?>
