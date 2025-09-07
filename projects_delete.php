<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_once __DIR__ . '/flash.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    set_flash('project_deleted', 'Projeto excluído com sucesso.', 'success');
} else {
    set_flash('project_deleted_err', 'ID inválido para exclusão.', 'danger');
}
header("Location: index.php");
exit;
