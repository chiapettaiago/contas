<?php
/**
 * Página principal - Dashboard
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/classes/Transacao.php';

require_once __DIR__ . '/classes/Categoria.php';

$transacao = new Transacao($pdo);
$categoria = new Categoria($pdo);

$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');

// Receitas: soma de todas as receitas no período
$receita = $transacao->totalPorTipo('receita', $mes, $ano, false);
// Despesas: soma apenas das despesas marcadas como pagas (usadas para calcular o saldo)
$despesa = $transacao->totalPorTipo('despesa', $mes, $ano, true);
// Despesas totais (pagas + pendentes) e pendentes
$despesaTotalAll = $transacao->totalPorTipo('despesa', $mes, $ano, false);
$despesaPendentes = $despesaTotalAll - $despesa;

$resumoPorCategoria = $transacao->resumoPorCategoria($mes, $ano);

$saldo = $receita - $despesa; // saldo = receitas - despesas pagas

// Dados diários de despesas para o gráfico
$despesasPagasPorDia = $transacao->despesasPorDia($mes, $ano, true);
$despesasAllPorDia = $transacao->despesasPorDia($mes, $ano, false);
$diasNoMes = (int) (new DateTime(sprintf('%04d-%02d-01', $ano, $mes)))->format('t');
$labelsDias = [];
$dadosPagas = [];
$dadosPendentes = [];
for ($d = 1; $d <= $diasNoMes; $d++) {
    $labelsDias[] = $d;
    $p = isset($despesasPagasPorDia[$d]) ? $despesasPagasPorDia[$d] : 0;
    $a = isset($despesasAllPorDia[$d]) ? $despesasAllPorDia[$d] : 0;
    $dadosPagas[] = $p;
    $dadosPendentes[] = max(0, $a - $p);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Contas Domésticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/assets/css/style.css'); ?>">
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
                    <li class="nav-item"><a href="index.php" class="nav-link active"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li class="nav-item"><a href="receitas.php" class="nav-link"><i class="fas fa-plus-circle"></i> Receitas</a></li>
                    <li class="nav-item"><a href="despesas.php" class="nav-link"><i class="fas fa-minus-circle"></i> Despesas</a></li>
                    <li class="nav-item"><a href="transacoes.php" class="nav-link"><i class="fas fa-exchange-alt"></i> Transações</a></li>
                    <li class="nav-item"><a href="categorias.php" class="nav-link"><i class="fas fa-tags"></i> Categorias</a></li>
                    <li class="nav-item"><a href="relatorios.php" class="nav-link"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <h2>Dashboard</h2>
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

        <div class="row mb-4 dashboard-row">
            <div class="col-12 col-md-4 d-flex align-items-stretch">
                <div class="card text-white bg-success h-100">
                    <div class="card-body d-flex flex-column justify-content-center text-center">
                        <h6 class="card-title">Receitas</h6>
                        <h2 class="display-6">R$ <?= number_format($receita, 2, ',', '.') ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 d-flex align-items-stretch">
                <div class="card text-white bg-danger h-100">
                    <div class="card-body d-flex flex-column justify-content-center text-center">
                        <h6 class="card-title">Despesas</h6>
                        <div class="mb-1 small">Pagas: <strong>R$ <?= number_format($despesa, 2, ',', '.') ?></strong></div>
                        <div class="mb-0 small">Pendentes: <strong>R$ <?= number_format($despesaPendentes, 2, ',', '.') ?></strong></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 d-flex align-items-stretch">
                <div class="card text-white <?= $saldo >= 0 ? 'bg-primary' : 'bg-warning' ?> h-100">
                    <div class="card-body d-flex flex-column justify-content-center text-center">
                        <h6 class="card-title">Saldo</h6>
                        <h2 class="display-6">R$ <?= number_format($saldo, 2, ',', '.') ?></h2>
                    </div>
                </div>
            </div>
        </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Despesas por Dia (mês selecionado)</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="graficoDespesasDiarias" height="80"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Resumo por Categoria</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($resumoPorCategoria)): ?>
                            <p class="text-muted">Nenhuma transação neste período</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Categoria</th>
                                            <th>Tipo</th>
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

            <!-- Área de 'Minhas Contas' removida do dashboard -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
    <script>
        // Dados vindos do PHP
        const labelsDias = <?= json_encode($labelsDias) ?>;
        const dataPagas = <?= json_encode($dadosPagas) ?>;
        const dataPendentes = <?= json_encode($dadosPendentes) ?>;

        const canvas = document.getElementById('graficoDespesasDiarias');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            window.graficoDespesasDiarias = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labelsDias,
                    datasets: [
                        {
                            label: 'Despesas Pagas',
                            data: dataPagas,
                            backgroundColor: 'rgba(220,53,69,0.85)',
                            borderColor: 'rgba(220,53,69,1)'
                        },
                        {
                            label: 'Despesas Pendentes',
                            data: dataPendentes,
                            backgroundColor: 'rgba(255,193,7,0.85)',
                            borderColor: 'rgba(255,193,7,1)'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { callback: function(v){ return 'R$ ' + v.toFixed(2).replace('.', ','); } }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const v = ctx.raw || 0;
                                    return ctx.dataset.label + ': R$ ' + v.toFixed(2).replace('.', ',');
                                }
                            }
                        }
                    }
                }
            });

            // Função para atualizar o gráfico buscando dados via AJAX
            async function atualizarGraficoDespesas(mesParam, anoParam) {
                try {
                    const params = new URLSearchParams({ mes: mesParam || <?= $mes ?>, ano: anoParam || <?= $ano ?> });
                    const res = await fetch('ajax/despesas_por_dia.php?' + params.toString());
                    const json = await res.json();
                    if (!json.success) return;

                    const dias = [];
                    const pagas = [];
                    const pendentes = [];
                    const diasNoMesLocal = labelsDias.length;
                    for (let d = 1; d <= diasNoMesLocal; d++) {
                        dias.push(d);
                        const p = json.pagas[d] ? Number(json.pagas[d]) : 0;
                        const a = json.all[d] ? Number(json.all[d]) : 0;
                        pagas.push(p);
                        pendentes.push(Math.max(0, a - p));
                    }

                    window.graficoDespesasDiarias.data.labels = dias;
                    window.graficoDespesasDiarias.data.datasets[0].data = pagas;
                    window.graficoDespesasDiarias.data.datasets[1].data = pendentes;
                    window.graficoDespesasDiarias.update();
                } catch (e) {
                    console.error('Erro ao atualizar gráfico:', e);
                }
            }

            // Ouvir eventos de storage para atualizações em outras abas
            window.addEventListener('storage', function(e) {
                if (e.key === 'contas:despesa:update') {
                    atualizarGraficoDespesas();
                }
            });
        }
    </script>
    <script src="assets/js/mobile-nav.js?v=<?php echo filemtime(__DIR__ . '/assets/js/mobile-nav.js'); ?>"></script>
</body>
</html>
