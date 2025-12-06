<?php
/**
 * Script de Diagnóstico do Sistema
 * Execute para verificar se tudo está configurado corretamente
 */

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  DIAGNÓSTICO - Sistema de Contas Domésticas              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$erros = [];
$avisos = [];

// Verificar PHP
echo "📋 Verificando PHP...\n";
$php_version = phpversion();
echo "  Versão: $php_version\n";

if (version_compare($php_version, '7.4', '<')) {
    $erros[] = "PHP 7.4+ é requerido. Versão atual: $php_version";
} else {
    echo "  ✓ PHP compatível\n";
}

// Verificar extensões PHP
echo "\n📦 Verificando extensões PHP...\n";

$extensoes_requeridas = ['pdo', 'pdo_mysql', 'json'];
foreach ($extensoes_requeridas as $ext) {
    if (extension_loaded($ext)) {
        echo "  ✓ $ext\n";
    } else {
        $erros[] = "Extensão PHP '$ext' não encontrada";
    }
}

// Verificar permissões de arquivo
echo "\n🔐 Verificando permissões de arquivo...\n";

$arquivos = [
    'config/database.php',
    'classes/Transacao.php',
    'classes/Categoria.php',
    'classes/Conta.php',
    'setup/create_tables.php',
];

foreach ($arquivos as $arquivo) {
    $caminho = __DIR__ . '/' . $arquivo;
    if (file_exists($caminho)) {
        if (is_readable($caminho)) {
            echo "  ✓ $arquivo (legível)\n";
        } else {
            $erros[] = "Arquivo '$arquivo' não é legível";
        }
    } else {
        $erros[] = "Arquivo '$arquivo' não encontrado";
    }
}

// Verificar banco de dados
echo "\n🗄️  Testando conexão ao banco de dados...\n";

try {
    require_once __DIR__ . '/config/database.php';
    echo "  ✓ Conexão estabelecida\n";
    
    // Verificar se as tabelas existem
    echo "\n📊 Verificando tabelas...\n";
    
    $tabelas = ['categorias', 'transacoes', 'contas', 'relatorios'];
    foreach ($tabelas as $tabela) {
        try {
            $stmt = $pdo->query("SELECT 1 FROM $tabela LIMIT 1");
            echo "  ✓ Tabela '$tabela' existe\n";
        } catch (PDOException $e) {
            $avisos[] = "Tabela '$tabela' não encontrada. Execute: php setup/create_tables.php";
        }
    }
    
} catch (PDOException $e) {
    $erros[] = "Erro ao conectar ao banco de dados: " . $e->getMessage();
    $erros[] = "Configure corretamente o arquivo: config/database.php";
}

// Verificar pastas de escrita
echo "\n📂 Verificando pastas...\n";

$pastas = [
    'assets',
    'classes',
    'config',
    'setup',
];

foreach ($pastas as $pasta) {
    $caminho = __DIR__ . '/' . $pasta;
    if (is_dir($caminho)) {
        echo "  ✓ Pasta '/$pasta' existe\n";
    } else {
        $erros[] = "Pasta '/$pasta' não encontrada";
    }
}

// Resumo
echo "\n" . str_repeat("═", 60) . "\n";

if (empty($erros) && empty($avisos)) {
    echo "✅ TUDO OK! O sistema está pronto para usar.\n\n";
    echo "Próximo passo:\n";
    echo "  php -S localhost:8000\n\n";
    echo "Acesse: http://localhost:8000\n";
} else {
    if (!empty($erros)) {
        echo "❌ ERROS ENCONTRADOS:\n\n";
        foreach ($erros as $i => $erro) {
            echo "  " . ($i + 1) . ". $erro\n";
        }
        echo "\n";
    }
    
    if (!empty($avisos)) {
        echo "⚠️  AVISOS:\n\n";
        foreach ($avisos as $i => $aviso) {
            echo "  " . ($i + 1) . ". $aviso\n";
        }
        echo "\n";
    }
}

echo str_repeat("═", 60) . "\n\n";

// Informações de configuração
echo "ℹ️  CONFIGURAÇÃO ATUAL:\n\n";
echo "Database: " . (defined('DB_NAME') ? DB_NAME : 'não configurado') . "\n";
echo "Host: " . (defined('DB_HOST') ? DB_HOST : 'não configurado') . "\n";
echo "Usuário: " . (defined('DB_USER') ? DB_USER : 'não configurado') . "\n";
echo "PHP: $php_version\n";

echo "\n💡 Para ver a documentação completa, abra: GUIA_RAPIDO.md\n\n";

// Retornar código de saída apropriado
exit(empty($erros) ? 0 : 1);
?>
