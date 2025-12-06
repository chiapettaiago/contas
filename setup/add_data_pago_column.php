<?php
require_once __DIR__ . '/../config/database.php';

try {
    // Verificar se a coluna já existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'transacoes' AND COLUMN_NAME = 'data_pago'");
    $stmt->execute([DB_NAME]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        echo "Coluna 'data_pago' já existe na tabela 'transacoes'.\n";
        exit(0);
    }

    // Adicionar coluna 'data_pago'
    $sql = "ALTER TABLE transacoes ADD COLUMN data_pago DATETIME NULL DEFAULT NULL";
    $pdo->exec($sql);
    echo "Coluna 'data_pago' adicionada com sucesso à tabela 'transacoes'.\n";
} catch (PDOException $e) {
    echo "Erro ao adicionar coluna 'data_pago': " . $e->getMessage() . "\n";
    exit(1);
}
?>