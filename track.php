<?php
// track.php — consulta status da issue e permite comentar
require __DIR__ . '/db.php';

$token = getenv('GITHUB_TOKEN') ?: ($_SERVER['GITHUB_TOKEN'] ?? null);
$repo  = getenv('GITHUB_REPO')  ?: ($_SERVER['GITHUB_REPO']  ?? null);
if (!$token || !$repo) { http_response_code(500); exit('Configuração ausente.'); }

$code = $_GET['code'] ?? '';
if (!preg_match('/^[a-f0-9]{32}$/', $code)) { http_response_code(400); exit('Código inválido.'); }

// Buscar issue_number
$st = $pdo->prepare("SELECT issue_number, contact FROM issue_tickets WHERE code = ?");
$st->execute([$code]);
$ticket = $st->fetch();
if (!$ticket) { http_response_code(404); exit('Ticket não encontrado.'); }
$issue = (int)$ticket['issue_number'];

// Se POST: adicionar comentário
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $comment = trim($_POST['comment'] ?? '');
  if ($comment !== '') {
    $ch = curl_init("https://api.github.com/repos/{$repo}/issues/{$issue}/comments");
    $payload = json_encode(['body' => $comment]);
    curl_setopt_array($ch, [
      CURLOPT_POST           => true,
      CURLOPT_HTTPHEADER     => [
        "Authorization: Bearer {$token}",
        "Accept: application/vnd.github+json",
        "User-Agent: projetos-app"
      ],
      CURLOPT_POSTFIELDS     => $payload,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT        => 15
    ]);
    $res = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $msg = ($http >= 200 && $http < 300) ? 'Comentário enviado com sucesso.' : 'Falha ao enviar comentário.';
  }
}

// Buscar dados da issue
function gh_get($url, $token) {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER     => [
      "Authorization: Bearer {$token}",
      "Accept: application/vnd.github+json",
      "User-Agent: projetos-app"
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15
  ]);
  $res = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return [$code, $res];
}

list($c1, $issueJson) = gh_get("https://api.github.com/repos/{$repo}/issues/{$issue}", $token);
if ($c1 < 200 || $c1 >= 300) { http_response_code(502); exit('Falha ao consultar a issue.'); }
$issueData = json_decode($issueJson, true);

// Comentários
list($c2, $commentsJson) = gh_get("https://api.github.com/repos/{$repo}/issues/{$issue}/comments?per_page=50", $token);
$comments = ($c2 >= 200 && $c2 < 300) ? json_decode($commentsJson, true) : [];

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Acompanhar chamado #<?= (int)$issue ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Arial,sans-serif;max-width:900px;margin:24px auto;padding:0 16px}
    .card{border:1px solid #ddd;border-radius:12px;padding:16px;margin-bottom:16px}
    .muted{color:#666}
    .badge{display:inline-block;padding:2px 8px;border-radius:999px;border:1px solid #ddd;margin-right:6px;font-size:12px}
    .ok{color:#0a7}
    .err{color:#c00}
    textarea{width:100%;min-height:120px}
    button{padding:10px 16px;border-radius:8px;border:1px solid #222;background:#222;color:#fff;cursor:pointer}
    button:hover{opacity:.9}
    .comment{border-top:1px dashed #ddd;padding-top:8px;margin-top:8px}
  </style>
</head>
<body>
  <h2>Chamado #<?= (int)$issue ?> — <?= h($issueData['title'] ?? '') ?></h2>
  <div class="card">
    <div><strong>Status:</strong> <?= h($issueData['state'] ?? '') ?> <?= !empty($issueData['state_reason']) ? '(' . h($issueData['state_reason']) . ')' : '' ?></div>
    <div><strong>Criado em:</strong> <?= h($issueData['created_at'] ?? '') ?></div>
    <div><strong>Atualizado em:</strong> <?= h($issueData['updated_at'] ?? '') ?></div>
    <div>
      <strong>Labels:</strong>
      <?php foreach (($issueData['labels'] ?? []) as $lb): ?>
        <span class="badge"><?= h(is_array($lb)?($lb['name']??''): $lb) ?></span>
      <?php endforeach; ?>
    </div>
    <?php if (!empty($issueData['body'])): ?>
      <div style="margin-top:8px;"><strong>Descrição:</strong><br><?= nl2br(h($issueData['body'])) ?></div>
    <?php endif; ?>
  </div>

  <?php if ($msg): ?>
    <p class="<?= strpos($msg,'sucesso')!==false ? 'ok':'err' ?>"><?= h($msg) ?></p>
  <?php endif; ?>

  <div class="card">
    <h3>Adicionar comentário</h3>
    <form method="post">
      <textarea name="comment" placeholder="Escreva seu comentário..." required></textarea>
      <div style="margin-top:8px;">
        <button type="submit">Enviar comentário</button>
      </div>
    </form>
  </div>

  <div class="card">
    <h3>Comentários</h3>
    <?php if (!$comments): ?>
      <div class="muted">Ainda não há comentários.</div>
    <?php else: foreach ($comments as $cm): ?>
      <div class="comment">
        <div><strong><?= h($cm['user']['login'] ?? 'autor') ?></strong> — <span class="muted"><?= h($cm['created_at'] ?? '') ?></span></div>
        <div><?= nl2br(h($cm['body'] ?? '')) ?></div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</body>
</html>
