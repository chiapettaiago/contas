<?php
/**
 * Página para gerenciar Transações
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/classes/Transacao.php';
require_once __DIR__ . '/classes/Categoria.php';

$transacao = new Transacao($pdo);
$categoria = new Categoria($pdo);

// Processar submissão de formulário
$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['acao'])) {
            if ($_POST['acao'] === 'adicionar') {
                $transacao->adicionar(
                    $_POST['categoria_id'],
                    $_POST['descricao'],
                    str_replace(',', '.', $_POST['valor']),
                    $_POST['data_transacao'],
                    $_POST['tipo'],
                    $_POST['observacoes'] ?? ''
                );
                $mensagem = 'Transação adicionada com sucesso!';
                $tipo_mensagem = 'success';
            } elseif ($_POST['acao'] === 'atualizar') {
                $transacao->atualizar(
                    $_POST['id'],
                    $_POST['categoria_id'],
                    $_POST['descricao'],
                    str_replace(',', '.', $_POST['valor']),
                    $_POST['data_transacao'],
                    $_POST['tipo'],
                    $_POST['observacoes'] ?? ''
                );
                $mensagem = 'Transação atualizada com sucesso!';
                $tipo_mensagem = 'success';
            } elseif ($_POST['acao'] === 'deletar') {
                $transacao->deletar($_POST['id']);
                $mensagem = 'Transação deletada com sucesso!';
                $tipo_mensagem = 'success';
            }
        }
    } catch (Exception $e) {
        $mensagem = 'Erro: ' . $e->getMessage();
        $tipo_mensagem = 'danger';
    }
}

$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');

$filtro = ['mes' => $mes, 'ano' => $ano];
$transacoes = $transacao->listar($filtro, 1000);
$categorias = $categoria->listar();

$categoriasPorTipo = ['receita' => [], 'despesa' => []];
foreach ($categorias as $cat) {
    $categoriasPorTipo[$cat['tipo']][] = $cat;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transações - Sistema de Contas Domésticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/assets/css/style.css'); ?>">
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
                    <li class="nav-item"><a href="index.php" class="nav-link"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li class="nav-item"><a href="receitas.php" class="nav-link"><i class="fas fa-plus-circle"></i> Receitas</a></li>
                    <li class="nav-item"><a href="despesas.php" class="nav-link"><i class="fas fa-minus-circle"></i> Despesas</a></li>
                    <li class="nav-item"><a href="transacoes.php" class="nav-link active"><i class="fas fa-exchange-alt"></i> Transações</a></li>
                    <li class="nav-item"><a href="categorias.php" class="nav-link"><i class="fas fa-tags"></i> Categorias</a></li>
                    <li class="nav-item"><a href="relatorios.php" class="nav-link"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <?php if ($mensagem): ?>
            <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show">
                <?= $mensagem ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Transações</h2>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTransacao">
                    <i class="fas fa-plus"></i> Nova Transação
                </button>
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

        <div class="card">
            <div class="card-body">
                <?php if (empty($transacoes)): ?>
                    <p class="text-muted">Nenhuma transação neste período</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Data</th>
                                    <th>Descrição</th>
                                    <th>Categoria</th>
                                    <th>Tipo</th>
                                    <th class="text-end">Valor</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transacoes as $t): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($t['data_transacao'])) ?></td>
                                        <td><?= htmlspecialchars($t['descricao']) ?></td>
                                        <td><?= htmlspecialchars($t['categoria_nome']) ?></td>
                                        <td>
                                            <span class="badge <?= $t['tipo'] === 'receita' ? 'bg-success' : 'bg-danger' ?>">
                                                <?= ucfirst($t['tipo']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <strong>R$ <?= number_format($t['valor'], 2, ',', '.') ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-warning" onclick="editarTransacao(<?= htmlspecialchars(json_encode($t)) ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza?')">
                                                <input type="hidden" name="acao" value="deletar">
                                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
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

    <!-- Modal para Transação -->
    <div class="modal fade" id="modalTransacao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Transação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="transacao_id">
                        <input type="hidden" name="acao" id="acao" value="adicionar">

                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select name="tipo" id="tipo" class="form-select" required onchange="carregarCategorias()">
                                <option value="">Selecione...</option>
                                <option value="receita">Receita</option>
                                <option value="despesa">Despesa</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="categoria_id" class="form-label">Categoria</label>
                            <select name="categoria_id" id="categoria_id" class="form-select" required>
                                <option value="">Selecione uma categoria</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <input type="text" name="descricao" id="descricao" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="valor" class="form-label">Valor</label>
                            <input type="number" name="valor" id="valor" class="form-control" step="0.01" required>
                        </div>

                        <div class="mb-3">
                            <label for="data_transacao" class="form-label">Data</label>
                            <input type="date" name="data_transacao" id="data_transacao" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="observacoes" class="form-label">Observações</label>
                            <textarea name="observacoes" id="observacoes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const categoriasPorTipo = <?= json_encode($categoriasPorTipo) ?>;
        const modal = new bootstrap.Modal(document.getElementById('modalTransacao'));

        function carregarCategorias() {
            const tipo = document.getElementById('tipo').value;
            const select = document.getElementById('categoria_id');
            select.innerHTML = '<option value="">Selecione uma categoria</option>';

            if (tipo && categoriasPorTipo[tipo]) {
                categoriasPorTipo[tipo].forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.nome;
                    select.appendChild(option);
                });
            }
        }

        function editarTransacao(t) {
            document.getElementById('transacao_id').value = t.id;
            document.getElementById('acao').value = 'atualizar';
            document.getElementById('tipo').value = t.tipo;
            carregarCategorias();
            document.getElementById('categoria_id').value = t.categoria_id;
            document.getElementById('descricao').value = t.descricao;
            document.getElementById('valor').value = t.valor;
            document.getElementById('data_transacao').value = t.data_transacao;
            document.getElementById('observacoes').value = t.observacoes || '';
            document.querySelector('.modal-title').textContent = 'Editar Transação';
            modal.show();
        }

        document.getElementById('modalTransacao').addEventListener('hidden.bs.modal', function() {
            document.getElementById('acao').value = 'adicionar';
            document.querySelector('.modal-title').textContent = 'Nova Transação';
            document.querySelector('form').reset();
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

        // Definir data padrão como hoje
        document.getElementById('data_transacao').valueAsDate = new Date();
    </script>
    <script src="assets/js/mobile-nav.js?v=<?php echo filemtime(__DIR__ . '/assets/js/mobile-nav.js'); ?>"></script>
</body>
</html>
