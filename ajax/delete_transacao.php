<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth.php';
require_login();
require_once __DIR__ . '/../classes/Transacao.php';

$transacao = new Transacao($pdo);

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$mes = isset($_POST['mes']) ? (int)$_POST['mes'] : (int)date('n');
$ano = isset($_POST['ano']) ? (int)$_POST['ano'] : (int)date('Y');

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}

try {
    $transacao->deletar($id);

    // Totais atualizados
    $totalDespesasAll = $transacao->totalPorTipo('despesa', $mes, $ano, false);
    $totalDespesasPagas = $transacao->totalPorTipo('despesa', $mes, $ano, true);
    $totalDespesasPendentes = $totalDespesasAll - $totalDespesasPagas;

    // Dados por dia
    $despesasPagasPorDia = $transacao->despesasPorDia($mes, $ano, true);
    $despesasAllPorDia = $transacao->despesasPorDia($mes, $ano, false);

    echo json_encode([
        'success' => true,
        'id' => $id,
        'totals' => [
            'all' => $totalDespesasAll,
            'pagas' => $totalDespesasPagas,
            'pendentes' => $totalDespesasPendentes
        ],
        'despesas_por_dia' => [
            'pagas' => $despesasPagasPorDia,
            'all' => $despesasAllPorDia
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

?>