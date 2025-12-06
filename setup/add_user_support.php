<?php
/**
 * Script de migração: cria tabela `users` e adiciona coluna `user_id` em tabelas existentes.
 * Execute manualmente: php setup/add_user_support.php
 */
require_once __DIR__ . '/../config/database.php';

try {
    $pdo->beginTransaction();

    // Criar tabela users
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fullname VARCHAR(255) DEFAULT NULL,
        email VARCHAR(255) DEFAULT NULL,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql);

    // Adicionar coluna user_id onde falta
    $tables = ['categorias', 'contas', 'transacoes'];
    foreach ($tables as $t) {
        try {
            $pdo->exec("ALTER TABLE $t ADD COLUMN user_id INT NOT NULL DEFAULT 1");
            echo "Coluna user_id adicionada em $t\n";
        } catch (Exception $e) {
            // possivelmente já existe
            echo "Ignorado (ou já existe) em $t: " . $e->getMessage() . "\n";
        }
    }

    $pdo->commit();
    echo "Migração concluída.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Erro: " . $e->getMessage() . "\n";
}
