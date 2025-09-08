<?php
// meus_chamados.php — lista tickets por contato (e-mail/telefone)
require __DIR__ . '/db.php';
$lista = [];
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $contato = trim($_POST['contato'] ?? '');
  if ($contato === '') {
    $err = 'Informe seu e-mail ou telefone.';
  } else {
    $st = $pdo->prepare("SELECT code, issue_number, created_at FROM issue_tickets WHERE contact = ? ORDER BY created_at DESC");
    $st->execute([$contato]);
    $lista = $st->fetchAll();
    if (!$lista) $err = 'Nenhum chamado encontrado para este contato.';
  }
}
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$base = rtrim((getenv('APP_BASE_URL') ?: ($_SERVER['APP_BASE_URL'] ?? '/projetos/')), '/') . '/';
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Meus chamados</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
    crossorigin="anonymous">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h1 class="h4 mb-3">Meus chamados</h1>
          <form method="post" class="row g-3">
            <div class="col-12">
              <label class="form-label">Seu contato</label>
              <input name="contato" class="form-control" placeholder="seuemail@exemplo.com" required>
            </div>
            <div class="col-12 d-flex justify-content-end">
              <button class="btn btn-primary">Buscar</button>
            </div>
          </form>

          <?php if ($err): ?>
            <div class="alert alert-warning mt-3"><?= h($err) ?></div>
          <?php endif; ?>

          <?php if ($lista): ?>
            <div class="table-responsive mt-3">
              <table class="table table-striped align-middle">
                <thead>
                  <tr>
                    <th>Issue #</th>
                    <th>Protocolo</th>
                    <th>Criado em</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($lista as $r): ?>
                  <tr>
                    <td><?= (int)$r['issue_number'] ?></td>
                    <td><code><?= h($r['code']) ?></code></td>
                    <td><?= h($r['created_at']) ?></td>
                    <td><a class="btn btn-sm btn-outline-primary"
                           href="<?= $base.'track.php?code='.urlencode($r['code']) ?>">Acompanhar</a></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="text-center mt-3">
        <a class="text-decoration-none" href="reportar.html">Reportar novo problema</a>
        <span class="mx-2 text-muted">•</span>
        <a class="text-decoration-none" href="consultar.php">Consultar por protocolo/issue</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>
