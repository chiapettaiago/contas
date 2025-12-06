<?php
/**
 * Página para gerenciar Contas
 */


require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/classes/Conta.php';

$conta = new Conta($pdo);

// Processar submissão de formulário
$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['acao'])) {
            if ($_POST['acao'] === 'adicionar') {
                $conta->adicionar(
                    $_POST['nome'],
                    $_POST['descricao'],
                    str_replace(',', '.', $_POST['saldo_inicial'])
                );
                $mensagem = 'Conta adicionada com sucesso!';
                $tipo_mensagem = 'success';
            } elseif ($_POST['acao'] === 'atualizar') {
                $conta->atualizar(
                    $_POST['id'],
                    $_POST['nome'],
                    $_POST['descricao'],
                    str_replace(',', '.', $_POST['saldo_atual'])
                );
                $mensagem = 'Conta atualizada com sucesso!';
                $tipo_mensagem = 'success';
            } elseif ($_POST['acao'] === 'desativar') {
                $conta->desativar($_POST['id']);
                $mensagem = 'Conta desativada com sucesso!';
                $tipo_mensagem = 'success';
            }
        }
    } catch (Exception $e) {
        $mensagem = 'Erro: ' . $e->getMessage();
        $tipo_mensagem = 'danger';
    }
}

$contas = $conta->listar();
$saldoTotal = $conta->saldoTotal();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contas - Sistema de Contas Domésticas</title>
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
                    <li class="nav-item"><a href="despesas.php" class="nav-link">Despesas</a></li>
                    <li class="nav-item"><a href="transacoes.php" class="nav-link">Transações</a></li>
                    <li class="nav-item"><a href="categorias.php" class="nav-link">Categorias</a></li>
                    <li class="nav-item"><a href="contas.php" class="nav-link active">Minhas Contas</a></li>
                    <li class="nav-item"><a href="relatorios.php" class="nav-link">Relatórios</a></li>
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
                <h2>Minhas Contas</h2>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalConta">
                    <i class="fas fa-plus"></i> Nova Conta
                </button>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Saldo Total</h5>
                        <h2>R$ <?= number_format($saldoTotal, 2, ',', '.') ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <?php if (empty($contas)): ?>
                    <p class="text-muted">Nenhuma conta cadastrada</p>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($contas as $c): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card border-left border-info">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($c['nome']) ?></h5>
                                        <p class="card-text text-muted small">
                                            <?= htmlspecialchars($c['descricao'] ?? '') ?>
                                        </p>
                                        <div class="mb-3">
                                            <small class="d-block text-muted">Saldo Inicial</small>
                                            <strong>R$ <?= number_format($c['saldo_inicial'], 2, ',', '.') ?></strong>
                                        </div>
                                        <div class="mb-3">
                                            <small class="d-block text-muted">Saldo Atual</small>
                                            <h4>R$ <?= number_format($c['saldo_atual'], 2, ',', '.') ?></h4>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-warning flex-grow-1" onclick="editarConta(<?= htmlspecialchars(json_encode($c)) ?>)">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza?')">
                                                <input type="hidden" name="acao" value="desativar">
                                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para Conta -->
    <div class="modal fade" id="modalConta" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Conta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="conta_id">
                        <input type="hidden" name="acao" id="acao" value="adicionar">

                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" name="nome" id="nome" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea name="descricao" id="descricao" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="mb-3" id="saldo_inicial_container">
                            <label for="saldo_inicial" class="form-label">Saldo Inicial</label>
                            <input type="number" name="saldo_inicial" id="saldo_inicial" class="form-control" step="0.01" required>
                        </div>

                        <div class="mb-3" id="saldo_atual_container" style="display:none;">
                            <label for="saldo_atual" class="form-label">Saldo Atual</label>
                            <input type="number" name="saldo_atual" id="saldo_atual" class="form-control" step="0.01" required>
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
        const modal = new bootstrap.Modal(document.getElementById('modalConta'));

        function editarConta(c) {
            document.getElementById('conta_id').value = c.id;
            document.getElementById('acao').value = 'atualizar';
            document.getElementById('nome').value = c.nome;
            document.getElementById('descricao').value = c.descricao || '';
            document.getElementById('saldo_atual').value = c.saldo_atual;
            document.getElementById('saldo_inicial_container').style.display = 'none';
            document.getElementById('saldo_atual_container').style.display = 'block';
            document.querySelector('.modal-title').textContent = 'Editar Conta';
            modal.show();
        }

        document.getElementById('modalConta').addEventListener('hidden.bs.modal', function() {
            document.getElementById('acao').value = 'adicionar';
            document.querySelector('.modal-title').textContent = 'Nova Conta';
            document.getElementById('saldo_inicial_container').style.display = 'block';
            document.getElementById('saldo_atual_container').style.display = 'none';
            document.querySelector('form').reset();
        });
    </script>
</body>
</html>
