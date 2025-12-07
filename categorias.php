<?php
/**
 * Página para gerenciar Categorias
 */


require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/classes/Categoria.php';

$categoria = new Categoria($pdo);

// Processar submissão de formulário
$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['acao'])) {
            if ($_POST['acao'] === 'adicionar') {
                $categoria->adicionar(
                    $_POST['nome'],
                    $_POST['descricao'],
                    $_POST['tipo']
                );
                $mensagem = 'Categoria adicionada com sucesso!';
                $tipo_mensagem = 'success';
            } elseif ($_POST['acao'] === 'atualizar') {
                $categoria->atualizar(
                    $_POST['id'],
                    $_POST['nome'],
                    $_POST['descricao'],
                    $_POST['tipo']
                );
                $mensagem = 'Categoria atualizada com sucesso!';
                $tipo_mensagem = 'success';
            } elseif ($_POST['acao'] === 'desativar') {
                $categoria->desativar($_POST['id']);
                $mensagem = 'Categoria desativada com sucesso!';
                $tipo_mensagem = 'success';
            }
        }
    } catch (Exception $e) {
        $mensagem = 'Erro: ' . $e->getMessage();
        $tipo_mensagem = 'danger';
    }
}

$categorias = $categoria->listar();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorias - Sistema de Contas Domésticas</title>
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
                    <li class="nav-item"><a href="transacoes.php" class="nav-link"><i class="fas fa-exchange-alt"></i> Transações</a></li>
                    <li class="nav-item"><a href="categorias.php" class="nav-link active"><i class="fas fa-tags"></i> Categorias</a></li>
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
                <h2>Categorias</h2>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCategoria">
                    <i class="fas fa-plus"></i> Nova Categoria
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h4 class="mb-3">Receitas</h4>
                <div class="card">
                    <div class="card-body">
                        <?php
                        $receitas = array_filter($categorias, fn($c) => $c['tipo'] === 'receita');
                        if (empty($receitas)):
                        ?>
                            <p class="text-muted">Nenhuma categoria de receita</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($receitas as $cat): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($cat['nome']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($cat['descricao'] ?? '') ?></small>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-warning" onclick="editarCategoria(<?= htmlspecialchars(json_encode($cat)) ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza?')">
                                                <input type="hidden" name="acao" value="desativar">
                                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <h4 class="mb-3">Despesas</h4>
                <div class="card">
                    <div class="card-body">
                        <?php
                        $despesas = array_filter($categorias, fn($c) => $c['tipo'] === 'despesa');
                        if (empty($despesas)):
                        ?>
                            <p class="text-muted">Nenhuma categoria de despesa</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($despesas as $cat): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($cat['nome']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($cat['descricao'] ?? '') ?></small>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-warning" onclick="editarCategoria(<?= htmlspecialchars(json_encode($cat)) ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza?')">
                                                <input type="hidden" name="acao" value="desativar">
                                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Categoria -->
    <div class="modal fade" id="modalCategoria" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="categoria_id">
                        <input type="hidden" name="acao" id="acao" value="adicionar">

                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" name="nome" id="nome" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select name="tipo" id="tipo" class="form-select" required>
                                <option value="">Selecione...</option>
                                <option value="receita">Receita</option>
                                <option value="despesa">Despesa</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea name="descricao" id="descricao" class="form-control" rows="3"></textarea>
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
        const modal = new bootstrap.Modal(document.getElementById('modalCategoria'));

        function editarCategoria(c) {
            document.getElementById('categoria_id').value = c.id;
            document.getElementById('acao').value = 'atualizar';
            document.getElementById('nome').value = c.nome;
            document.getElementById('tipo').value = c.tipo;
            document.getElementById('descricao').value = c.descricao || '';
            document.querySelector('.modal-title').textContent = 'Editar Categoria';
            modal.show();
        }

        document.getElementById('modalCategoria').addEventListener('hidden.bs.modal', function() {
            document.getElementById('acao').value = 'adicionar';
            document.querySelector('.modal-title').textContent = 'Nova Categoria';
            document.querySelector('form').reset();
        });
    </script>
    <script src="assets/js/mobile-nav.js?v=<?php echo filemtime(__DIR__ . '/assets/js/mobile-nav.js'); ?>"></script>
</body>
</html>
