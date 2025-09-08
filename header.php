<?php
// no topo do header.php, garanta uma base_url consistente:
$base_url = rtrim((getenv('APP_BASE_URL') ?: ($_SERVER['APP_BASE_URL'] ?? '/projetos/')), '/') . '/';
require __DIR__ . '/auth.php';
$config = require __DIR__ . '/config.php';
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Projetos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= $config['app']['base_url'] ?>index.php">Projetos</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Lista</a></li>
        <li class="nav-item"><a class="nav-link" href="projects_create.php">Novo Projeto</a></li>
        <li class="nav-item"><a class="nav-link" href="statuses_list.php">Status</a></li>
        <li class="nav-item"><a class="nav-link" href="reports.php">Relatórios</a></li>
        <li class="nav-item"><a class="nav-link" href="stakeholders_list.php">Partes Interessadas</a></li>
  	<li class="nav-item"><a class="nav-link" href="<?= $base_url ?>reportar.html">Reportar problema</a></li>
  	<li class="nav-item"><a class="nav-link" href="<?= $base_url ?>consultar.php">Consultar</a></li>
  	<li class="nav-item"><a class="nav-link" href="<?= $base_url ?>meus_chamados.php">Meus chamados</a></li>
      </ul>
      <ul class="navbar-nav">
        <?php if (current_user()): ?>
          <li class="nav-item">
            <span class="navbar-text me-3">
              Olá, <?= htmlspecialchars(current_user()['name']) ?>
            </span>
          </li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Sair</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Entrar</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container">
<?php
require_once __DIR__ . '/flash.php';
if ($all = get_flash()) {
  foreach ($all as $f) {
    $type = htmlspecialchars($f['type']); // success, danger, warning, info, primary...
    $msg  = htmlspecialchars($f['msg']);
    echo "<div class=\"alert alert-$type alert-dismissible fade show\" role=\"alert\">$msg
            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
          </div>";
  }
}
?>

