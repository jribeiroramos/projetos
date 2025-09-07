<?php
require __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/flash.php';
require_login();

$rows = $pdo->query("SELECT * FROM stakeholders ORDER BY is_active DESC, name")->fetchAll();

include __DIR__ . '/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="m-0">Partes Interessadas</h3>
  <a href="stakeholders_create.php" class="btn btn-success">+ Nova Parte Interessada</a>
</div>

<div class="table-responsive">
<table class="table table-striped align-middle">
  <thead>
    <tr>
      <th>Nome</th>
      <th>Tipo</th>
      <th>Email</th>
      <th>Telefone</th>
      <th>Ativo</th>
      <th style="width: 160px">Ações</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!$rows): ?>
      <tr><td colspan="6" class="text-muted">Nenhum registro.</td></tr>
    <?php else: foreach ($rows as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['name']) ?></td>
        <td><?= htmlspecialchars($r['type']) ?></td>
        <td><?= htmlspecialchars($r['email'] ?? '') ?></td>
        <td><?= htmlspecialchars($r['phone'] ?? '') ?></td>
        <td><?= $r['is_active'] ? 'Sim' : 'Não' ?></td>
        <td>
          <a class="btn btn-sm btn-primary" href="stakeholders_edit.php?id=<?= (int)$r['id'] ?>">Editar</a>
          <a class="btn btn-sm btn-outline-danger"
             href="stakeholders_delete.php?id=<?= (int)$r['id'] ?>"
             onclick="return confirm('Excluir esta parte interessada?')">Excluir</a>
        </td>
      </tr>
    <?php endforeach; endif; ?>
  </tbody>
</table>
</div>
<?php include __DIR__ . '/footer.php'; ?>
