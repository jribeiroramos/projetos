<?php
// reportar.php — recebe o formulário e cria Issue no GitHub + gera URL de rastreio
require __DIR__ . '/db.php'; // usa $pdo

// Permitir apenas POST (ou GET mostra form simples p/ teste)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  echo '<form method="post">
          <input name="contato" placeholder="Seu e-mail" required><br>
          <input name="titulo" placeholder="Título" required><br>
          <select name="severidade"><option>baixa</option><option>média</option><option>alta</option></select><br>
          <textarea name="descricao" placeholder="Descreva..." required></textarea><br>
          <button>Enviar</button>
        </form>';
  exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Método não permitido');
}

// Sanitizar entradas
$contato    = trim($_POST['contato'] ?? '');
$titulo     = trim($_POST['titulo'] ?? '');
$descricao  = trim($_POST['descricao'] ?? '');
$severidade = trim($_POST['severidade'] ?? 'baixa');

if ($titulo === '' || $descricao === '') {
  http_response_code(400);
  exit('Campos obrigatórios ausentes');
}

// Corpo da issue
$body = "**Contato:** {$contato}\n**Severidade:** {$severidade}\n\n---\n\n{$descricao}\n";

// Token e repo vindos de env (Apache)
$token = getenv('GITHUB_TOKEN') ?: ($_SERVER['GITHUB_TOKEN'] ?? null);
$repo  = getenv('GITHUB_REPO')  ?: ($_SERVER['GITHUB_REPO']  ?? null);
if (!$token || !$repo) {
  http_response_code(500);
  exit('Configuração ausente no servidor');
}

// Criar issue no GitHub
$ch = curl_init("https://api.github.com/repos/{$repo}/issues");
$data = json_encode([
  'title'  => $titulo,
  'body'   => $body,
  'labels' => ['cliente', "sev:{$severidade}"]
]);

curl_setopt_array($ch, [
  CURLOPT_POST           => true,
  CURLOPT_HTTPHEADER     => [
    "Authorization: Bearer {$token}",
    "Accept: application/vnd.github+json",
    "User-Agent: projetos-app"
  ],
  CURLOPT_POSTFIELDS     => $data,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT        => 15
]);

$res  = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code < 200 || $code >= 300) {
  http_response_code(502);
  echo "<p style='color:red'>❌ Falha ao abrir issue no GitHub (HTTP {$code}).</p>";
  if ($res) echo "<pre>".htmlspecialchars($res)."</pre>";
  exit;
}

// Pegar número da issue
$payload = json_decode($res, true);
$issue_number = (int)($payload['number'] ?? 0);
if ($issue_number <= 0) {
  http_response_code(502);
  exit('Issue criada, mas não foi possível obter o número.');
}

// Gerar código público (32 hex)
$codePub = bin2hex(random_bytes(16)); // 32 chars

// Salvar ticket
$st = $pdo->prepare("INSERT INTO issue_tickets (code, contact, issue_number) VALUES (?,?,?)");
$st->execute([$codePub, $contato, $issue_number]);

// Montar URL de rastreio
$base = rtrim((getenv('APP_BASE_URL') ?: ($_SERVER['APP_BASE_URL'] ?? '/projetos/')), '/') . '/';
$trackUrl = $base . 'track.php?code=' . urlencode($codePub);

// Resposta amigável
echo "<p style='color:green'>✅ Obrigado! Seu problema foi registrado como issue #{$issue_number}.</p>";
echo "<p>Você pode acompanhar por aqui: <a href='{$trackUrl}'>{$trackUrl}</a></p>";
echo "<p>Guarde este link para consultar o andamento e comentar.</p>";
