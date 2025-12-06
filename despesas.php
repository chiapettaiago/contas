<?php
/**
 * Página para registrar Despesas (contas a pagar)
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/classes/Transacao.php';
require_once __DIR__ . '/classes/Categoria.php';

$transacao = new Transacao($pdo);
$categoria = new Categoria($pdo);

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['acao']) && $_POST['acao'] === 'adicionar') {
            $transacao->adicionar(
                $_POST['categoria_id'],
                $_POST['descricao'],
                str_replace(',', '.', $_POST['valor']),
                $_POST['data_transacao'],
                'despesa',
                $_POST['observacoes'] ?? ''
            );
            $mensagem = 'Despesa adicionada com sucesso!';
            $tipo_mensagem = 'success';
        } elseif (isset($_POST['acao']) && $_POST['acao'] === 'deletar') {
            $transacao->deletar($_POST['id']);
            $mensagem = 'Despesa removida.';
            $tipo_mensagem = 'success';
        } elseif (isset($_POST['acao']) && $_POST['acao'] === 'pagar') {
            $id = $_POST['id'];
            $pagar = isset($_POST['pagar']) && $_POST['pagar'] == '1' ? 1 : 0;
            $transacao->marcarPago($id, $pagar);
            $mensagem = $pagar ? 'Despesa marcada como paga.' : 'Despesa marcada como pendente.';
            $tipo_mensagem = 'success';
        }
    } catch (Exception $e) {
        $mensagem = 'Erro: ' . $e->getMessage();
        $tipo_mensagem = 'danger';
    }
}

$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');

$categorias = $categoria->listar('despesa');
$despesas = $transacao->listar(['mes' => $mes, 'ano' => $ano, 'tipo' => 'despesa'], 1000);

// Totais: despesas pagas, despesas totais e pendentes
$totalDespesasAll = $transacao->totalPorTipo('despesa', $mes, $ano, false);
$totalDespesasPagas = $transacao->totalPorTipo('despesa', $mes, $ano, true);
$totalDespesasPendentes = $totalDespesasAll - $totalDespesasPagas;

// Contar itens pendentes na listagem
$pendentesCount = 0;
foreach ($despesas as $dd) {
    if (empty($dd['pago']) || $dd['pago'] == 0) $pendentesCount++;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Despesas - Contas Domésticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
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
                    <li class="nav-item"><a href="despesas.php" class="nav-link active">Despesas</a></li>
                    <li class="nav-item"><a href="transacoes.php" class="nav-link">Transações</a></li>
                    <li class="nav-item"><a href="categorias.php" class="nav-link">Categorias</a></li>
                    <li class="nav-item"><a href="relatorios.php" class="nav-link">Relatórios</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <?php if ($mensagem): ?>
            <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show">
                <?= $mensagem ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-6">
                <h3>Registrar Despesa</h3>
                <form method="POST">
                    <input type="hidden" name="acao" value="adicionar">

                    <div class="mb-3">
                        <label class="form-label">Categoria</label>
                        <select name="categoria_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <input type="text" name="descricao" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Valor (R$)</label>
                        <input type="number" name="valor" step="0.01" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Data de Vencimento / Pagamento</label>
                        <input type="date" name="data_transacao" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <textarea name="observacoes" class="form-control" rows="3"></textarea>
                    </div>

                    <button class="btn btn-danger">Salvar Despesa</button>
                </form>
            </div>

            <div class="col-md-6">
                <h3>Despesas no Período</h3>
                <div class="mb-3">
                    <div class="card border-warning bg-light">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Total pendente:</strong>
                                    <div class="h5 mb-0">R$ <?= number_format($totalDespesasPendentes, 2, ',', '.') ?></div>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">Itens pendentes</small>
                                    <div class="h5 mb-0"><?= $pendentesCount ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if (empty($despesas)): ?>
                    <p class="text-muted">Nenhuma despesa neste período</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Data</th>
                                    <th>Descrição</th>
                                    <th class="text-end">Valor</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($despesas as $d): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($d['data_transacao'])) ?></td>
                                            <td><?= htmlspecialchars($d['descricao']) ?></td>
                                            <td class="text-end">R$ <?= number_format($d['valor'], 2, ',', '.') ?></td>
                                            <td class="text-center">
                                                <?php if (!empty($d['pago']) && $d['pago'] == 1): ?>
                                                    <span class="badge bg-success me-2">Pago</span>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Marcar como pendente?')">
                                                        <input type="hidden" name="acao" value="pagar">
                                                        <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                                        <input type="hidden" name="pagar" value="0">
                                                        <button class="btn btn-sm btn-outline-warning">Desmarcar</button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Confirmar pagamento?')">
                                                        <input type="hidden" name="acao" value="pagar">
                                                        <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                                        <input type="hidden" name="pagar" value="1">
                                                        <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Pagar</button>
                                                    </form>
                                                <?php endif; ?>

                                                <form method="POST" style="display:inline; margin-left:4px;" onsubmit="return confirm('Remover?')">
                                                    <input type="hidden" name="acao" value="deletar">
                                                    <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                                </form>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
