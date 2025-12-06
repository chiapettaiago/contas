<?php
/**
 * Script para criar as tabelas do banco de dados
 */

require_once __DIR__ . '/../config/database.php';

$sql = <<<SQL
CREATE TABLE IF NOT EXISTS categorias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT,
    tipo ENUM('receita', 'despesa') NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transacoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    categoria_id INT NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data_transacao DATE NOT NULL,
    tipo ENUM('receita', 'despesa') NOT NULL,
    status ENUM('pendente', 'concluido') DEFAULT 'concluido',
    observacoes TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS contas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    saldo_inicial DECIMAL(10, 2) DEFAULT 0,
    saldo_atual DECIMAL(10, 2) DEFAULT 0,
    ativa BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS relatorios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mes INT NOT NULL,
    ano INT NOT NULL,
    receita_total DECIMAL(10, 2) DEFAULT 0,
    despesa_total DECIMAL(10, 2) DEFAULT 0,
    data_geracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (mes, ano)
);
SQL;

try {
    $pdo->exec($sql);
    echo "✓ Tabelas criadas com sucesso!\n";
} catch (PDOException $e) {
    echo "✗ Erro ao criar tabelas: " . $e->getMessage() . "\n";
}
?>
