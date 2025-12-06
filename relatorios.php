<?php
/**
 * Página de Relatórios
 */


require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/classes/Transacao.php';

$transacao = new Transacao($pdo);

$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');

$resumo = $transacao->resumoMensal($mes, $ano);
$resumoPorCategoria = $transacao->resumoPorCategoria($mes, $ano);

$receita = 0;
$despesa = 0;

foreach ($resumo as $item) {
    if ($item['tipo'] === 'receita') {
        $receita = $item['total'];
    } else {
        $despesa = $item['total'];
    }
}

$saldo = $receita - $despesa;

// Dados para gráfico
$meses = [];
$totaisReceita = [];
$totaisDespesa = [];
$anoAtual = date('Y');

for ($m = 1; $m <= 12; $m++) {
    $resumoMes = $transacao->resumoMensal($m, $anoAtual);
    $meses[] = date('M', mktime(0, 0, 0, $m, 1));
    
    $receitaMes = 0;
    $despesaMes = 0;
    
    foreach ($resumoMes as $item) {
        if ($item['tipo'] === 'receita') {
            $receitaMes = $item['total'];
        } else {
            $despesaMes = $item['total'];
        }
    }
    
    $totaisReceita[] = $receitaMes;
    $totaisDespesa[] = $despesaMes;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Sistema de Contas Domésticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-wallet"></i> Contas Domésticas
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a href="index.php" class="nav-link">Dashboard</a></li>
                    <li class="nav-item"><a href="receitas.php" class="nav-link">Receitas</a></li>
                    <li class="nav-item"><a href="despesas.php" class="nav-link">Despesas</a></li>
                    <li class="nav-item"><a href="transacoes.php" class="nav-link">Transações</a></li>
                    <li class="nav-item"><a href="categorias.php" class="nav-link">Categorias</a></li>
                    <li class="nav-item"><a href="relatorios.php" class="nav-link active">Relatórios</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Relatórios</h2>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <p class="text-muted">
                    Período: 
                    <select id="mes" class="form-select d-inline w-auto">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $m == $mes ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <select id="ano" class="form-select d-inline w-auto">
                        <?php for ($a = date('Y') - 5; $a <= date('Y') + 1; $a++): ?>
                            <option value="<?= $a ?>" <?= $a == $ano ? 'selected' : '' ?>><?= $a ?></option>
                        <?php endfor; ?>
                    </select>
                </p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Receitas</h5>
                        <h2>R$ <?= number_format($receita, 2, ',', '.') ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <h5 class="card-title">Despesas</h5>
                        <h2>R$ <?= number_format($despesa, 2, ',', '.') ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white <?= $saldo >= 0 ? 'bg-primary' : 'bg-warning' ?>">
                    <div class="card-body">
                        <h5 class="card-title">Saldo</h5>
                        <h2>R$ <?= number_format($saldo, 2, ',', '.') ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Gráfico Mensal</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="graficoMensal"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Composição do Período</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="graficoComposicao"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Resumo por Categoria</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($resumoPorCategoria)): ?>
                            <p class="text-muted">Nenhuma transação neste período</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Categoria</th>
                                            <th>Tipo</th>
                                            <th class="text-end">Quantidade</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($resumoPorCategoria as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['categoria']) ?></td>
                                                <td>
                                                    <span class="badge <?= $item['tipo'] === 'receita' ? 'bg-success' : 'bg-danger' ?>">
                                                        <?= ucfirst($item['tipo']) ?>
                                                    </span>
                                                </td>
                                                <td class="text-end"><?= $item['quantidade'] ?></td>
                                                <td class="text-end">
                                                    <strong>R$ <?= number_format($item['total'], 2, ',', '.') ?></strong>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gráfico Mensal
        const ctxMensal = document.getElementById('graficoMensal').getContext('2d');
        new Chart(ctxMensal, {
            type: 'bar',
            data: {
                labels: <?= json_encode($meses) ?>,
                datasets: [
                    {
                        label: 'Receitas',
                        data: <?= json_encode($totaisReceita) ?>,
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Despesas',
                        data: <?= json_encode($totaisDespesa) ?>,
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toFixed(2).replace('.', ',');
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });

        // Gráfico de Composição
        const ctxComposicao = document.getElementById('graficoComposicao').getContext('2d');
        new Chart(ctxComposicao, {
            type: 'doughnut',
            data: {
                labels: ['Receitas', 'Despesas'],
                datasets: [{
                    data: [<?= $receita ?>, <?= $despesa ?>],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(220, 53, 69, 0.7)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        document.getElementById('mes').addEventListener('change', function() {
            const mes = this.value;
            const ano = document.getElementById('ano').value;
            window.location.href = `?mes=${mes}&ano=${ano}`;
        });

        document.getElementById('ano').addEventListener('change', function() {
            const ano = this.value;
            const mes = document.getElementById('mes').value;
            window.location.href = `?mes=${mes}&ano=${ano}`;
        });
    </script>
</body>
</html>
