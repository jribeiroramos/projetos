<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();

// Filtros (datas opcionais)
$start = isset($_GET['start']) && $_GET['start'] !== '' ? $_GET['start'] : null;
$end   = isset($_GET['end'])   && $_GET['end']   !== '' ? $_GET['end']   : null;

// Sanitização simples de formato (YYYY-MM-DD)
$validDate = function($d) {
  return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
};
if ($start && !$validDate($start)) $start = null;
if ($end   && !$validDate($end))   $end   = null;

// Filtros aplicados às queries
$whereProj = [];
$paramsProj = [];

if ($start) { $whereProj[] = "DATE(p.created_at) >= ?"; $paramsProj[] = $start; }
if ($end)   { $whereProj[] = "DATE(p.created_at) <= ?"; $paramsProj[] = $end; }

$whereClause = $whereProj ? ('WHERE ' . implode(' AND ', $whereProj)) : '';

// Totais por status (com filtros por período no created_at)
$sqlStatus = "
  SELECT s.name, s.sort_order, COUNT(p.id) AS total
  FROM statuses s
  LEFT JOIN projects p ON p.status_id = s.id
  " . ($whereProj ? ' AND ' . implode(' AND ', array_map(fn($c)=>preg_replace('/p\./','p.',$c), $whereProj)) : '') . "
  GROUP BY s.id
  ORDER BY s.sort_order, s.name
";
$stmt = $pdo->prepare($sqlStatus);
$stmt->execute($paramsProj);
$byStatus = $stmt->fetchAll();

// Criados por mês (com filtros)
$sqlMonthly = "
  SELECT DATE_FORMAT(p.created_at, '%Y-%m-01') AS month, COUNT(*) AS total
  FROM projects p
  $whereClause
  GROUP BY DATE_FORMAT(p.created_at, '%Y-%m-01')
  ORDER BY month ASC
";
$stmt2 = $pdo->prepare($sqlMonthly);
$stmt2->execute($paramsProj);
$byMonth = $stmt2->fetchAll();

include __DIR__ . '/header.php';
?>
<h3 class="mb-4">Relatórios</h3>

<form class="row g-3 mb-4" method="get">
  <div class="col-md-3">
    <label class="form-label">Data inicial</label>
    <input type="date" name="start" value="<?= htmlspecialchars($start ?? '') ?>" class="form-control">
  </div>
  <div class="col-md-3">
    <label class="form-label">Data final</label>
    <input type="date" name="end" value="<?= htmlspecialchars($end ?? '') ?>" class="form-control">
  </div>
  <div class="col-md-6 d-flex align-items-end gap-2">
    <button class="btn btn-primary">Aplicar</button>
    <a class="btn btn-outline-secondary" href="reports.php">Limpar</a>
    <!-- Exportações CSV -->
    <a class="btn btn-outline-success"
       href="reports_export.php?type=status<?= $start ? '&start='.urlencode($start):'' ?><?= $end ? '&end='.urlencode($end):'' ?>">
       Exportar CSV (por status)
    </a>
    <a class="btn btn-outline-success"
       href="reports_export.php?type=monthly<?= $start ? '&start='.urlencode($start):'' ?><?= $end ? '&end='.urlencode($end):'' ?>">
       Exportar CSV (por mês)
    </a>
  </div>
</form>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">Projetos por Status</div>
      <div class="card-body">
        <?php if (!$byStatus): ?>
          <p class="text-muted m-0">Sem dados.</p>
        <?php else: ?>
          <?php
            $grand = 0;
            foreach ($byStatus as $r) { $grand += (int)$r['total']; }
          ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead><tr><th>Status</th><th class="text-end">Total</th></tr></thead>
              <tbody>
              <?php foreach ($byStatus as $r):
                $name = htmlspecialchars($r['name']);
                $tot  = (int)$r['total'];
                $pct  = $grand > 0 ? round(($tot / $grand) * 100) : 0;
              ?>
                <tr>
                  <td><?= $name ?></td>
                  <td class="text-end">
                    <?= $tot ?>
                    <div class="progress mt-1" style="height: 6px;">
                      <div class="progress-bar" role="progressbar" style="width: <?= $pct ?>%;"
                           aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr><th>Total geral</th><th class="text-end"><?= $grand ?></th></tr>
              </tfoot>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">Projetos criados por mês</div>
      <div class="card-body">
        <?php if (!$byMonth): ?>
          <p class="text-muted m-0">Sem dados.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead><tr><th>Mês</th><th class="text-end">Total</th></tr></thead>
              <tbody>
                <?php foreach ($byMonth as $m): ?>
                  <tr>
                    <td><?= date('m/Y', strtotime($m['month'])) ?></td>
                    <td class="text-end"><?= (int)$m['total'] ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
