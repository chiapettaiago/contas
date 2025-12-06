<?php
/**
 * Script para inserir dados de exemplo
 * Execute após a criação das tabelas
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Categoria.php';
require_once __DIR__ . '/../classes/Conta.php';
require_once __DIR__ . '/../classes/Transacao.php';

$categoria = new Categoria($pdo);
$conta = new Conta($pdo);
$transacao = new Transacao($pdo);

try {
    echo "Inserindo categorias de exemplo...\n";

    // Categorias de Receita
    $categoria->adicionar('Salário', 'Renda mensal do trabalho', 'receita');
    $categoria->adicionar('Freelance', 'Trabalhos freelance', 'receita');
    $categoria->adicionar('Investimentos', 'Retorno de investimentos', 'receita');
    $categoria->adicionar('Outros', 'Outras receitas', 'receita');

    // Categorias de Despesa
    $categoria->adicionar('Alimentação', 'Supermercado, restaurantes', 'despesa');
    $categoria->adicionar('Moradia', 'Aluguel, condomínio, água, luz', 'despesa');
    $categoria->adicionar('Transporte', 'Combustível, combustível, uber', 'despesa');
    $categoria->adicionar('Saúde', 'Farmácia, médico, dentista', 'despesa');
    $categoria->adicionar('Educação', 'Cursos, livros, material escolar', 'despesa');
    $categoria->adicionar('Lazer', 'Cinema, viagens, hobbies', 'despesa');
    $categoria->adicionar('Telefone/Internet', 'Telefone e internet', 'despesa');
    $categoria->adicionar('Outras Despesas', 'Despesas diversas', 'despesa');

    echo "✓ Categorias criadas!\n";

    echo "\nInserindo contas de exemplo...\n";

    // Contas
    $conta->adicionar('Banco do Brasil', 'Conta corrente principal', 1500.00);
    $conta->adicionar('Poupança', 'Fundo de emergência', 3000.00);
    $conta->adicionar('Dinheiro Físico', 'Dinheiro em casa', 200.00);

    echo "✓ Contas criadas!\n";

    echo "\nInserindo transações de exemplo...\n";

    // Obter categorias para usar nos IDs
    $categorias = $categoria->listar();
    $catMap = [];
    foreach ($categorias as $cat) {
        $catMap[$cat['nome']] = $cat['id'];
    }

    // Transações de exemplo para este mês
    $hoje = date('Y-m-d');
    $ano = date('Y');
    $mes = date('n');
    $primeiro = date('Y-m-01');

    // Receitas
    $transacao->adicionar(
        $catMap['Salário'],
        'Salário mensal',
        3000.00,
        $primeiro,
        'receita',
        'Pagamento mensal'
    );

    $transacao->adicionar(
        $catMap['Freelance'],
        'Projeto de design',
        500.00,
        date('Y-m-d', strtotime('-5 days')),
        'receita',
        'Trabalho freelance completado'
    );

    // Despesas
    $transacao->adicionar(
        $catMap['Alimentação'],
        'Compra no supermercado',
        150.50,
        date('Y-m-d', strtotime('-10 days')),
        'despesa',
        'Produtos variados'
    );

    $transacao->adicionar(
        $catMap['Moradia'],
        'Aluguel',
        1200.00,
        date('Y-m-d', strtotime('-20 days')),
        'despesa',
        'Aluguel do mês'
    );

    $transacao->adicionar(
        $catMap['Transporte'],
        'Combustível',
        120.00,
        date('Y-m-d', strtotime('-7 days')),
        'despesa',
        'Abastecimento carro'
    );

    $transacao->adicionar(
        $catMap['Telefone/Internet'],
        'Internet residencial',
        79.90,
        date('Y-m-d', strtotime('-15 days')),
        'despesa',
        'Serviço mensal'
    );

    $transacao->adicionar(
        $catMap['Lazer'],
        'Cinema',
        45.00,
        date('Y-m-d', strtotime('-3 days')),
        'despesa',
        'Ingresso cinema'
    );

    $transacao->adicionar(
        $catMap['Saúde'],
        'Farmácia',
        89.50,
        date('Y-m-d', strtotime('-2 days')),
        'despesa',
        'Medicamentos'
    );

    echo "✓ Transações criadas!\n";

    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Dados de exemplo inseridos com sucesso!\n";
    echo str_repeat("=", 50) . "\n\n";

    echo "Resumo do mês atual:\n";
    $resumo = $transacao->resumoMensal($mes, $ano);
    foreach ($resumo as $item) {
        echo "  {$item['tipo']}: R$ " . number_format($item['total'], 2, ',', '.') . "\n";
    }

} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
?>
