<?php
/**
 * Gera PDF a partir das páginas internas (despesas, receitas, relatorios).
 * Usa wkhtmltopdf se disponível; caso contrário faz fallback para download do HTML.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth.php';
require_login();

// Preparar ambiente de saída: evitar que warnings/deprecations corrompam o PDF
@ini_set('display_errors', '0');
@ini_set('display_startup_errors', '0');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_DEPRECATED);

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
// Garantir buffer limpo antes de incluir a página
while (ob_get_level() > 0) { ob_end_clean(); }
ob_start();
$page = $page;
$mes = $mes;
$ano = $ano;
include __DIR__ . '/pdf_template.php';
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

// Executar comando de forma segura (evita erro quando funções shell estão desabilitadas)
function run_command_safe($cmd, &$output = null, &$return_var = null) {
    $output = [];
    $return_var = 1;
    if (function_exists('exec')) {
        @exec($cmd . ' 2>&1', $output, $return_var);
        return true;
    }
    if (function_exists('shell_exec')) {
        $res = @shell_exec($cmd . ' 2>&1');
        if ($res !== null) {
            $output = preg_split('/\r?\n/', trim($res));
            $return_var = 0;
            return true;
        }
    }
    if (function_exists('proc_open')) {
        $descriptorspec = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = @proc_open($cmd, $descriptorspec, $pipes);
        if (is_resource($process)) {
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $return_var = proc_close($process);
            $out = trim($stdout . "\n" . $stderr);
            $output = $out === '' ? [] : preg_split('/\r?\n/', $out);
            return true;
        }
    }
    return false;
}

// Tentar carregar o autoloader do Composer se existir, para habilitar Dompdf
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    @require_once $composerAutoload;
}

// Se a biblioteca Dompdf estiver disponível, usá-la (gera PDF sem chamadas shell)
if (class_exists('Dompdf\\Dompdf')) {
    try {
        $dompdf = new Dompdf\Dompdf();
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();
        // Obter bytes do PDF
        $pdfBytes = $dompdf->output();
        // Limpar qualquer saída anterior
        while (ob_get_level() > 0) { ob_end_clean(); }
        // Enviar headers e PDF
        if (!headers_sent()) {
            header('Content-Type: application/pdf');
            header('Content-Length: ' . strlen($pdfBytes));
            header('Content-Disposition: attachment; filename="relatorio_' . $page . '_' . $mes . '_' . $ano . '.pdf"');
        }
        echo $pdfBytes;
        @unlink($tmpHtml);
        @unlink($tmpPdf);
        exit;
    } catch (Exception $e) {
        // Se Dompdf falhar, continuar para tentativa com wkhtmltopdf/fallback
    }
}

// Localizar wkhtmltopdf (sem chamar exec() diretamente)
$whichCmd = (stripos(PHP_OS, 'WIN') === 0) ? 'where wkhtmltopdf' : 'which wkhtmltopdf';
$out = null; $rc = null;
if (run_command_safe($whichCmd, $out, $rc) && $rc === 0 && !empty($out[0])) {
    $wk = trim($out[0]);
    $cmd = escapeshellcmd($wk) . ' --enable-local-file-access --disable-smart-shrinking ' . escapeshellarg($tmpHtml) . ' ' . escapeshellarg($tmpPdf);
    $out2 = null; $rc2 = null;
    if (run_command_safe($cmd, $out2, $rc2) && $rc2 === 0 && file_exists($tmpPdf)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="relatorio_' . $page . '_' . $mes . '_' . $ano . '.pdf"');
        readfile($tmpPdf);
        @unlink($tmpHtml);
        @unlink($tmpPdf);
        exit;
    } else {
        // wkhtmltopdf presente, mas falhou ao gerar o PDF
        header('Content-Type: text/html; charset=utf-8');
        echo "<h2>Falha ao gerar PDF via wkhtmltopdf</h2><pre>" . htmlspecialchars(implode("\n", (array)$out2)) . "</pre>";
        echo $html;
        @unlink($tmpHtml);
        @unlink($tmpPdf);
        exit;
    }
} else {
    // wkhtmltopdf não disponível ou sem permissão de executar comandos: gerar PDF por bibliotecas PHP seria alternativa
    // Por enquanto, retornar HTML como fallback, mas Preferimos PDF — instruções de instalação abaixo.
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="relatorio_' . $page . '_' . $mes . '_' . $ano . '.html"');
    echo $html;
    @unlink($tmpHtml);
    exit;
}

?>
