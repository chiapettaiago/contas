<?php
require_once __DIR__ . '/../config/database.php';

try {
    // Verificar se a coluna já existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'transacoes' AND COLUMN_NAME = 'pago'");
    $stmt->execute([DB_NAME]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        echo "Coluna 'pago' já existe na tabela 'transacoes'.\n";
        exit(0);
    }

    // Adicionar coluna 'pago'
    $sql = "ALTER TABLE transacoes ADD COLUMN pago TINYINT(1) NOT NULL DEFAULT 0";
    $pdo->exec($sql);
    echo "Coluna 'pago' adicionada com sucesso à tabela 'transacoes'.\n";
} catch (PDOException $e) {
    echo "Erro ao adicionar coluna 'pago': " . $e->getMessage() . "\n";
    exit(1);
}
?>