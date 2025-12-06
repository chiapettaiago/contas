<?php
/**
 * Configuração do Banco de Dados
 */

define('DB_HOST', '159.203.188.0');
define('DB_USER', 'contas');
define('DB_PASS', 'YaDXBjcTfNsYmc42');
define('DB_NAME', 'contas');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Erro na conexão: ' . $e->getMessage());
}
?>
