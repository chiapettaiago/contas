<?php
/**
 * Configuração do Banco de Dados
 */

// Ajuste de timezone do sistema para garantir horário correto em PDFs
// Altere se necessário para outra timezone válida (ex: 'America/Sao_Paulo')
@date_default_timezone_set('America/Sao_Paulo');


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
