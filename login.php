<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Buscar usuário no banco
    $stmt = $pdo->prepare("SELECT id, name, email, password_hash, is_admin FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        do_login($user);
        redirect_after_login();
    } else {
        $error = "E-mail ou senha inválidos.";
    }
}

include __DIR__ . '/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-4">
    <h3 class="mb-3">Login</h3>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
      <div class="mb-3">
        <label for="email" class="form-label">E-mail</label>
        <input type="email" name="email" id="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Senha</label>
        <input type="password" name="password" id="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>
  </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
