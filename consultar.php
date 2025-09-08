<?php
// consultar.php — encontra ticket por Protocolo (code) OU nº da issue e redireciona para track.php
require __DIR__ . '/db.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $protocolo = trim($_POST['protocolo'] ?? '');
  $issueNum  = trim($_POST['issue'] ?? '');

  if ($protocolo !== '') {
    if (!preg_match('/^[a-f0-9]{32}$/', $protocolo)) {
      $err = 'Protocolo inválido.';
    } else {
      $st = $pdo->prepare("SELECT 1 FROM issue_tickets WHERE code = ?");
      $st->execute([$protocolo]);
      if ($st->fetch()) {
        $base = rtrim((getenv('APP_BASE_URL') ?: ($_SERVER['APP_BASE_URL'] ?? '/projetos/')), '/') . '/';
        header('Location: ' . $base . 'track.php?code=' . urlencode($protocolo));
        exit;
      } else {
        $err = 'Protocolo não encontrado.';
      }
    }
  } elseif ($issueNum !== '') {
    if (!ctype_digit($issueNum)) {
      $err = 'Número da issue inválido.';
    } else {
      $st = $pdo->prepare("SELECT code FROM issue_tickets WHERE issue_number = ?");
      $st->execute([(int)$issueNum]);
      if ($row = $st->fetch()) {
        $base = rtrim((getenv('APP_BASE_URL') ?: ($_SERVER['APP_BASE_URL'] ?? '/projetos/')), '/') . '/';
        header('Location: ' . $base . 'track.php?code=' . urlencode($row['code']));
        exit;
      } else {
        $err = 'Não há protocolo associado a essa issue.';
      }
    }
  } else {
    $err = 'Informe o Protocolo ou o Nº da issue.';
  }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Consultar chamado</title>
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
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h1 class="h4 mb-3">Consultar chamado</h1>
          <?php if ($err): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
          <?php endif; ?>
          <form method="post" class="row g-3">
            <div class="col-12">
              <label class="form-label">Protocolo</label>
              <input name="protocolo" class="form-control" placeholder="32 caracteres hex (ex.: 3fa85f64...)" />
              <div class="form-text">Se tiver o protocolo, não precisa preencher o número da issue.</div>
            </div>
            <div class="col-12 text-center text-muted">— ou —</div>
            <div class="col-12">
              <label class="form-label">Nº da issue (GitHub)</label>
              <input name="issue" class="form-control" placeholder="Ex.: 12" />
            </div>
            <div class="col-12 d-flex justify-content-end">
              <button class="btn btn-primary">Consultar</button>
            </div>
          </form>
        </div>
      </div>
      <div class="text-center mt-3">
        <a class="text-decoration-none" href="reportar.html">Reportar novo problema</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>
