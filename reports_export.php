<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();

// parâmetros
$type  = $_GET['type']  ?? 'status'; // 'status' ou 'monthly'
$start = isset($_GET['start']) && $_GET['start'] !== '' ? $_GET['start'] : null;
$end   = isset($_GET['end'])   && $_GET['end']   !== '' ? $_GET['end']   : null;

// validação simples
$validDate = function($d) {
  return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
};
if ($start && !$validDate($start)) $start = null;
if ($end   && !$validDate($end))   $end   = null;

// onde/filtros
$where = [];
$params = [];
if ($start) { $where[] = "DATE(p.created_at) >= ?"; $params[] = $start; }
if ($end)   { $where[] = "DATE(p.created_at) <= ?"; $params[] = $end; }
$whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

header('Content-Type: text/csv; charset=utf-8');
$fname = 'relatorio_' . $type . '_' . date('Ymd_His') . '.csv';
header('Content-Disposition: attachment; filename="'.$fname.'"');

$fp = fopen('php://output', 'w');

// BOM UTF-8 para Excel
fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));

if ($type === 'monthly') {
    // por mês
    fputcsv($fp, ['Mes', 'Total']);
    $sql = "
      SELECT DATE_FORMAT(p.created_at, '%Y-%m-01') AS month, COUNT(*) AS total
      FROM projects p
      $whereClause
      GROUP BY DATE_FORMAT(p.created_at, '%Y-%m-01')
      ORDER BY month ASC
    ";
    $st = $pdo->prepare($sql);
    $st->execute($params);
    while ($row = $st->fetch()) {
        fputcsv($fp, [date('m/Y', strtotime($row['month'])), (int)$row['total']]);
    }
} else {
    // por status
    fputcsv($fp, ['Status', 'Total']);
    // nota: precisamos aplicar filtros à junção também
    $sql = "
      SELECT s.name, s.sort_order, COUNT(p.id) AS total
      FROM statuses s
      LEFT JOIN projects p ON p.status_id = s.id
      " . ($where ? ' AND ' . implode(' AND ', $where) : '') . "
      GROUP BY s.id
      ORDER BY s.sort_order, s.name
    ";
    $st = $pdo->prepare($sql);
    $st->execute($params);
    while ($row = $st->fetch()) {
        fputcsv($fp, [$row['name'], (int)$row['total']]);
    }
}

fclose($fp);
exit;
