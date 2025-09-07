<?php
require __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/flash.php';
require_login();

$id = (int)($_GET['id'] ?? 0);

// Impede excluir se existir projeto referenciando
$cnt = $pdo->prepare("SELECT COUNT(*) c FROM projects WHERE stakeholder_id = ?");
$cnt->execute([$id]);
$inUse = (int)$cnt->fetch()['c'] > 0;

if ($id <= 0) {
    set_flash('st_del_err', 'ID inválido.', 'danger');
} elseif ($inUse) {
    set_flash('st_del_err', 'Não é possível excluir: há projetos usando esta parte interessada.', 'warning');
} else {
    $pdo->prepare("DELETE FROM stakeholders WHERE id = ?")->execute([$id]);
    set_flash('st_del_ok', 'Parte interessada excluída.', 'success');
}

header('Location: stakeholders_list.php');
exit;
