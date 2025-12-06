<?php
/**
 * Gera PDF a partir das páginas internas (despesas, receitas, relatorios).
 * Usa wkhtmltopdf se disponível; caso contrário faz fallback para download do HTML.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth.php';
require_login();

$allowed = [
    'despesas' => __DIR__ . '/../despesas.php',
    'receitas' => __DIR__ . '/../receitas.php',
    'relatorios' => __DIR__ . '/../relatorios.php',
];

$page = isset($_GET['page']) ? $_GET['page'] : '';
if (!isset($allowed[$page])) {
    http_response_code(400);
    echo 'Página inválida';
    exit;
}

$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');

// Preparar ambiente para capturar a saída da página
$_GET['mes'] = $mes;
$_GET['ano'] = $ano;
$_GET['pdf'] = 1; // sinal para a página imprimir sem elementos interativos, se necessário

// Capturar saída da página
ob_start();
include $allowed[$page];
$html = ob_get_clean();

// Remover scripts para evitar JS no output
$html = preg_replace('#<script\b[^>]*>(.*?)</script>#is', '', $html);

// Remover eventuais tags <link rel="stylesheet"> e inline o CSS principal
$cssFile = __DIR__ . '/../assets/css/style.css';
$css = '';
if (file_exists($cssFile)) {
    $css = file_get_contents($cssFile);
}
$html = preg_replace('#<link[^>]+rel=["\']?stylesheet["\']?[^>]*>#i', '', $html);

// Injetar CSS antes do </head>
if (!empty($css)) {
    if (stripos($html, '</head>') !== false) {
        $html = str_ireplace('</head>', "<style>\n" . $css . "\n</style>\n</head>", $html);
    } else {
        $html = "<head><style>\n" . $css . "\n</style></head>" . $html;
    }
}

// Ajuste de caminhos relativos (transformar links src/href que comecem com / em caminhos absolutos locais)
// Não tentamos baixar assets externos.

$tmpDir = sys_get_temp_dir();
$tmpHtml = tempnam($tmpDir, 'conta_pdf_') . '.html';
$tmpPdf = tempnam($tmpDir, 'conta_pdf_') . '.pdf';
file_put_contents($tmpHtml, $html);

// Verificar wkhtmltopdf
exec('which wkhtmltopdf 2>/dev/null', $out, $rc);
if ($rc === 0 && !empty($out[0])) {
    $wk = $out[0];
    // Gerar PDF
    $cmd = escapeshellcmd($wk) . ' --enable-local-file-access --disable-smart-shrinking ' . escapeshellarg($tmpHtml) . ' ' . escapeshellarg($tmpPdf) . ' 2>&1';
    exec($cmd, $out2, $rc2);
    if ($rc2 === 0 && file_exists($tmpPdf)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="relatorio_' . $page . '_' . $mes . '_' . $ano . '.pdf"');
        readfile($tmpPdf);
        @unlink($tmpHtml);
        @unlink($tmpPdf);
        exit;
    } else {
        // fallback para HTML se wkhtmltopdf falhar
        header('Content-Type: text/html; charset=utf-8');
        echo "<h2>Falha ao gerar PDF via wkhtmltopdf</h2><pre>" . htmlspecialchars(implode("\n", $out2)) . "</pre>";
        echo $html;
        @unlink($tmpHtml);
        @unlink($tmpPdf);
        exit;
    }
} else {
    // wkhtmltopdf não disponível: oferecer download do HTML renderizado
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="relatorio_' . $page . '_' . $mes . '_' . $ano . '.html"');
    echo $html;
    @unlink($tmpHtml);
    exit;
}

?>
