<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth.php';
require_login();
require_once __DIR__ . '/../classes/Transacao.php';

$transacao = new Transacao($pdo);

$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('n');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');

try {
    $despesasPagasPorDay = $transacao->despesasPorDia($mes, $ano, true);
    $despesasAllPorDay = $transacao->despesasPorDia($mes, $ano, false);

    echo json_encode([
        'success' => true,
        'mes' => $mes,
        'ano' => $ano,
        'pagas' => $despesasPagasPorDay,
        'all' => $despesasAllPorDay
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

?>
