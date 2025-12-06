<?php
/**
 * Página para registrar Receitas (valores recebidos)
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
                'receita',
                $_POST['observacoes'] ?? ''
            );
            $mensagem = 'Receita adicionada com sucesso!';
            $tipo_mensagem = 'success';
        } elseif (isset($_POST['acao']) && $_POST['acao'] === 'deletar') {
            $transacao->deletar($_POST['id']);
            $mensagem = 'Receita removida.';
            $tipo_mensagem = 'success';
        }
    } catch (Exception $e) {
        $mensagem = 'Erro: ' . $e->getMessage();
        $tipo_mensagem = 'danger';
    }
}

$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');

$categorias = $categoria->listar('receita');
$receitas = $transacao->listar(['mes' => $mes, 'ano' => $ano, 'tipo' => 'receita'], 1000);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receitas - Contas Domésticas</title>
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
                    <li class="nav-item"><a href="receitas.php" class="nav-link active">Receitas</a></li>
                    <li class="nav-item"><a href="despesas.php" class="nav-link">Despesas</a></li>
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
                <h3>Registrar Receita</h3>
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
                        <label class="form-label">Data</label>
                        <input type="date" name="data_transacao" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <textarea name="observacoes" class="form-control" rows="3"></textarea>
                    </div>

                    <button class="btn btn-success">Salvar Receita</button>
                </form>
            </div>

            <div class="col-md-6">
                <h3>Receitas no Período</h3>
                <div class="text-end mb-3">
                    <a href="export/pdf.php?page=receitas&mes=<?= $mes ?>&ano=<?= $ano ?>" target="_blank" class="btn btn-primary">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </a>
                </div>
                <?php if (empty($receitas)): ?>
                    <p class="text-muted">Nenhuma receita neste período</p>
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
                                <?php foreach ($receitas as $r): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($r['data_transacao'])) ?></td>
                                        <td><?= htmlspecialchars($r['descricao']) ?></td>
                                        <td class="text-end">R$ <?= number_format($r['valor'], 2, ',', '.') ?></td>
                                        <td class="text-center">
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Remover?')">
                                                <input type="hidden" name="acao" value="deletar">
                                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
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
