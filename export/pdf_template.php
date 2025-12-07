<?php
// Template minimalista e bonito para PDF
// Espera as variáveis: $page, $mes, $ano, $pdo
require_once __DIR__ . '/../classes/Transacao.php';

$transacao = new Transacao($pdo);

function fmt($v) {
    return number_format((float)$v, 2, ',', '.');
}

?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body { font-family: DejaVu Sans, Arial, sans-serif; color: #222; margin: 20px; }
    .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; }
    .brand { font-size:20px; font-weight:700; color:#333; }
    .meta { text-align:right; font-size:12px; color:#666; }
    .cards { display:flex; gap:12px; margin-bottom:18px; }
    .card { flex:1; padding:12px; border-radius:8px; background:#f7f9fc; box-shadow:0 1px 0 rgba(0,0,0,0.04); }
    .card .label { font-size:12px; color:#666; }
    .card .value { font-size:18px; font-weight:700; margin-top:6px; }
    table { width:100%; border-collapse:collapse; margin-top:12px; }
    th, td { padding:8px 10px; border-bottom:1px solid #eaeaea; text-align:left; font-size:13px; }
    th { background:#fff; color:#444; font-weight:700; }
    td.numeric { text-align:right; }
    .footer { margin-top:20px; font-size:12px; color:#666; }
    .title { font-size:16px; font-weight:700; margin:6px 0 12px 0; }
  </style>
</head>
<body>
  <div class="header">
    <div class="brand">Contas — Relatório <?php echo htmlspecialchars(ucfirst($page)); ?></div>
    <div class="meta">Mês: <?php echo htmlspecialchars($mes); ?> / Ano: <?php echo htmlspecialchars($ano); ?><br>Gerado: <?php echo date('d/m/Y H:i'); ?></div>
  </div>

  <?php
  // Resumo geral: receitas, despesas pagas e saldo
  $receitas = $transacao->totalPorTipo('receita', $mes, $ano, false);
  $despesas = $transacao->totalPorTipo('despesa', $mes, $ano, true); // só despesas pagas impactam saldo
  $saldo = $receitas - $despesas;
  ?>

  <div class="cards">
    <div class="card">
      <div class="label">Receitas</div>
      <div class="value">R$ <?php echo fmt($receitas); ?></div>
    </div>
    <div class="card">
      <div class="label">Despesas (pagas)</div>
      <div class="value">R$ <?php echo fmt($despesas); ?></div>
    </div>
    <div class="card">
      <div class="label">Saldo</div>
      <div class="value">R$ <?php echo fmt($saldo); ?></div>
    </div>
  </div>

  <?php if ($page === 'despesas' || $page === 'receitas'): ?>
    <div class="title"><?php echo htmlspecialchars(ucfirst($page)); ?> — Lista</div>
    <?php
      $tipo = ($page === 'despesas') ? 'despesa' : 'receita';
      $rows = $transacao->listar(['mes'=>$mes,'ano'=>$ano,'tipo'=>$tipo], 1000, 0);
    ?>
    <table>
      <thead>
        <tr>
          <th>Data</th>
          <th>Categoria</th>
          <th>Descrição</th>
          <th class="numeric">Valor (R$)</th>
          <?php if ($page === 'despesas'): ?>
            <th style="text-align:center">Pago</th>
            <th style="text-align:center">Data Pagamento</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="4" style="text-align:center;color:#888;padding:18px">Nenhuma transação encontrada.</td></tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?php echo date('d/m/Y', strtotime($r['data_transacao'])); ?></td>
              <td><?php echo htmlspecialchars($r['categoria_nome']); ?></td>
              <td><?php echo htmlspecialchars($r['descricao']); ?></td>
              <td class="numeric"><?php echo fmt($r['valor']); ?></td>
              <?php if ($page === 'despesas'): ?>
                <td style="text-align:center"><?php echo (!empty($r['pago']) && (int)$r['pago'] === 1) ? 'Sim' : 'Não'; ?></td>
                <td style="text-align:center"><?php echo (!empty($r['data_pago']) && $r['data_pago'] !== '0000-00-00 00:00:00') ? date('d/m/Y H:i', strtotime($r['data_pago'])) : '-'; ?></td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="title">Relatórios</div>
    <div style="margin-top:8px">
      <strong>Resumo por tipo</strong>
      <?php $res = $transacao->resumoMensal($mes,$ano); ?>
      <table>
        <thead><tr><th>Tipo</th><th class="numeric">Quantidade</th><th class="numeric">Total (R$)</th></tr></thead>
        <tbody>
          <?php foreach ($res as $r): ?>
            <tr>
              <td><?php echo htmlspecialchars(ucfirst($r['tipo'])); ?></td>
              <td class="numeric"><?php echo (int)$r['quantidade']; ?></td>
              <td class="numeric"><?php echo fmt($r['total']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div style="margin-top:12px"><strong>Resumo por categoria</strong>
      <?php $bycat = $transacao->resumoPorCategoria($mes,$ano); ?>
      <table>
        <thead><tr><th>Categoria</th><th>Tipo</th><th class="numeric">Quantidade</th><th class="numeric">Total (R$)</th></tr></thead>
        <tbody>
          <?php foreach ($bycat as $b): ?>
            <tr>
              <td><?php echo htmlspecialchars($b['categoria']); ?></td>
              <td><?php echo htmlspecialchars($b['tipo']); ?></td>
              <td class="numeric"><?php echo (int)$b['quantidade']; ?></td>
              <td class="numeric"><?php echo fmt($b['total']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
  <?php endif; ?>

  <div class="footer">Relatório gerado pelo sistema Contas — exija precisão e mantenha seus registros atualizados.</div>
</body>
</html>
