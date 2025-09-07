<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/flash.php';
require_login();

$id = (int)($_GET['id'] ?? 0);

// Impede excluir status em uso por algum projeto
$check = $pdo->prepare("SELECT COUNT(*) AS c FROM projects WHERE status_id = ?");
$check->execute([$id]);
$inUse = (int)$check->fetch()['c'] > 0;

if ($id <= 0) {
    set_flash('status_delete_err', 'ID inválido para exclusão.', 'danger');
} elseif ($inUse) {
    set_flash('status_delete_err', 'Não é possível excluir: existem projetos usando este status.', 'warning');
} else {
    $del = $pdo->prepare("DELETE FROM statuses WHERE id = ?");
    $del->execute([$id]);
    set_flash('status_delete_ok', 'Status excluído com sucesso.', 'success');
}

header("Location: statuses_list.php");
exit;
