<?php
/**
 * Exemplos de Queries SQL e Operações Comuns
 * Use este arquivo como referência para adicionar funcionalidades
 */

// ============================================================
// CONECTAR AO BANCO
// ============================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Transacao.php';
require_once __DIR__ . '/classes/Categoria.php';
require_once __DIR__ . '/classes/Conta.php';

$transacao = new Transacao($pdo);
$categoria = new Categoria($pdo);
$conta = new Conta($pdo);

// ============================================================
// EXEMPLOS DE OPERAÇÕES
// ============================================================

/**
 * EXEMPLO 1: Listar todas as transações de um mês
 */
function exemplo_transacoes_mes($pdo, $mes, $ano) {
    $sql = "SELECT t.*, c.nome as categoria_nome 
            FROM transacoes t 
            JOIN categorias c ON t.categoria_id = c.id 
            WHERE MONTH(t.data_transacao) = ? 
            AND YEAR(t.data_transacao) = ? 
            ORDER BY t.data_transacao DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mes, $ano]);
    return $stmt->fetchAll();
}

/**
 * EXEMPLO 2: Saldo de uma conta em uma data específica
 */
function exemplo_saldo_data($pdo, $conta_id, $data) {
    $sql = "SELECT 
                c.saldo_inicial,
                COALESCE(SUM(CASE 
                    WHEN t.tipo = 'receita' AND t.data_transacao <= ? THEN t.valor 
                    WHEN t.tipo = 'despesa' AND t.data_transacao <= ? THEN -t.valor 
                    ELSE 0 
                END), 0) as total_transacoes
            FROM contas c
            LEFT JOIN transacoes t ON 1=1
            WHERE c.id = ?
            GROUP BY c.id, c.saldo_inicial";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data, $data, $conta_id]);
    return $stmt->fetch();
}

/**
 * EXEMPLO 3: Maior gasto do mês
 */
function exemplo_maior_gasto($pdo, $mes, $ano) {
    $sql = "SELECT 
                c.nome as categoria,
                SUM(t.valor) as total,
                COUNT(*) as quantidade
            FROM transacoes t
            JOIN categorias c ON t.categoria_id = c.id
            WHERE t.tipo = 'despesa'
            AND MONTH(t.data_transacao) = ?
            AND YEAR(t.data_transacao) = ?
            GROUP BY t.categoria_id, c.nome
            ORDER BY total DESC
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mes, $ano]);
    return $stmt->fetch();
}

/**
 * EXEMPLO 4: Comparativo de 2 meses
 */
function exemplo_comparativo_meses($pdo, $mes1, $ano1, $mes2, $ano2) {
    $sql = "SELECT 
                t.tipo,
                CASE 
                    WHEN MONTH(t.data_transacao) = ? AND YEAR(t.data_transacao) = ? 
                    THEN 'Mês 1'
                    WHEN MONTH(t.data_transacao) = ? AND YEAR(t.data_transacao) = ?
                    THEN 'Mês 2'
                END as periodo,
                SUM(t.valor) as total,
                COUNT(*) as quantidade
            FROM transacoes t
            WHERE (MONTH(t.data_transacao) = ? AND YEAR(t.data_transacao) = ?)
            OR (MONTH(t.data_transacao) = ? AND YEAR(t.data_transacao) = ?)
            GROUP BY t.tipo, periodo
            ORDER BY periodo, t.tipo";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mes1, $ano1, $mes2, $ano2, $mes1, $ano1, $mes2, $ano2]);
    return $stmt->fetchAll();
}

/**
 * EXEMPLO 5: Gastos acumulados (trend)
 */
function exemplo_gastos_acumulados($pdo, $ano) {
    $sql = "SELECT 
                MONTH(t.data_transacao) as mes,
                MONTHNAME(t.data_transacao) as mes_nome,
                SUM(CASE WHEN t.tipo = 'receita' THEN t.valor ELSE 0 END) as receita,
                SUM(CASE WHEN t.tipo = 'despesa' THEN t.valor ELSE 0 END) as despesa
            FROM transacoes t
            WHERE YEAR(t.data_transacao) = ?
            GROUP BY mes, mes_nome
            ORDER BY mes";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$ano]);
    return $stmt->fetchAll();
}

/**
 * EXEMPLO 6: Categorias com melhor performance
 */
function exemplo_categorias_top($pdo, $mes, $ano, $limite = 5) {
    $sql = "SELECT 
                c.nome,
                c.tipo,
                COUNT(*) as quantidade,
                SUM(t.valor) as total,
                AVG(t.valor) as media
            FROM transacoes t
            JOIN categorias c ON t.categoria_id = c.id
            WHERE MONTH(t.data_transacao) = ?
            AND YEAR(t.data_transacao) = ?
            GROUP BY t.categoria_id, c.nome, c.tipo
            ORDER BY total DESC
            LIMIT ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mes, $ano, $limite]);
    return $stmt->fetchAll();
}

/**
 * EXEMPLO 7: Gerar relatório PDF (estrutura)
 */
function exemplo_gerar_relatorio_json($pdo, $mes, $ano) {
    $relatorio = [
        'periodo' => [
            'mes' => $mes,
            'ano' => $ano,
            'mes_nome' => strftime('%B', mktime(0, 0, 0, $mes, 1))
        ],
        'resumo' => $transacao->resumoMensal($mes, $ano),
        'por_categoria' => $transacao->resumoPorCategoria($mes, $ano),
        'data_geracao' => date('d/m/Y H:i:s')
    ];
    
    return $relatorio;
}

/**
 * EXEMPLO 8: Buscar transações por intervalo de data
 */
function exemplo_transacoes_intervalo($pdo, $data_inicio, $data_fim) {
    $sql = "SELECT t.*, c.nome as categoria_nome
            FROM transacoes t
            JOIN categorias c ON t.categoria_id = c.id
            WHERE t.data_transacao BETWEEN ? AND ?
            ORDER BY t.data_transacao DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data_inicio, $data_fim]);
    return $stmt->fetchAll();
}

/**
 * EXEMPLO 9: Contas com saldo baixo
 */
function exemplo_contas_alerta($pdo, $saldo_minimo = 500) {
    $sql = "SELECT 
                id,
                nome,
                saldo_atual,
                CASE 
                    WHEN saldo_atual < ? THEN 'CRÍTICO'
                    WHEN saldo_atual < ? THEN 'ATENÇÃO'
                    ELSE 'OK'
                END as status
            FROM contas
            WHERE ativa = TRUE
            AND saldo_atual < ?
            ORDER BY saldo_atual";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$saldo_minimo * 0.5, $saldo_minimo, $saldo_minimo]);
    return $stmt->fetchAll();
}

/**
 * EXEMPLO 10: Deletar transações antigas
 */
function exemplo_limpar_transacoes_antigas($pdo, $ano_limite) {
    $sql = "DELETE FROM transacoes 
            WHERE YEAR(data_transacao) < ? 
            AND status = 'concluido'";
    
    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute([$ano_limite]);
    return $resultado ? $stmt->rowCount() : 0;
}

// ============================================================
// COMO USAR OS EXEMPLOS
// ============================================================

/*

// Exemplo 1: Listar transações de dezembro de 2025
$transacoes = exemplo_transacoes_mes($pdo, 12, 2025);
foreach ($transacoes as $t) {
    echo $t['descricao'] . ": R$ " . $t['valor'] . "\n";
}

// Exemplo 3: Ver o maior gasto
$maior = exemplo_maior_gasto($pdo, 12, 2025);
echo "Maior gasto: " . $maior['categoria'] . " - R$ " . $maior['total'] . "\n";

// Exemplo 5: Ver gastos acumulados do ano
$gastos = exemplo_gastos_acumulados($pdo, 2025);
foreach ($gastos as $mes) {
    echo $mes['mes_nome'] . ": " . 
         "Receita: R$ " . $mes['receita'] . ", " .
         "Despesa: R$ " . $mes['despesa'] . "\n";
}

// Exemplo 8: Buscar transações de uma semana
$inicio = '2025-12-01';
$fim = '2025-12-07';
$semana = exemplo_transacoes_intervalo($pdo, $inicio, $fim);

// Exemplo 9: Ver contas com saldo baixo
$alertas = exemplo_contas_alerta($pdo, 1000);
foreach ($alertas as $conta) {
    echo "Conta: " . $conta['nome'] . " - " . $conta['status'] . "\n";
}

*/

// ============================================================
// ADICIONAR NOVOS EXEMPLOS
// ============================================================

/**
 * TEMPLATE: Criar nova função de query
 */
function novo_exemplo($pdo) {
    // 1. Preparar SQL
    $sql = "SELECT * FROM tabela WHERE condicao = ?";
    
    // 2. Preparar statement
    $stmt = $pdo->prepare($sql);
    
    // 3. Executar com parâmetros
    $stmt->execute([]);
    
    // 4. Retornar resultado
    return $stmt->fetchAll();
}

?>
