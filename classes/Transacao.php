<?php
/**
 * Classe para gerenciar Transações
 */

class Transacao {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Adiciona uma nova transação
     */
    public function adicionar($categoria_id, $descricao, $valor, $data_transacao, $tipo, $observacoes = '') {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1; // compatibilidade se não houver sessão
        }

        $sql = "INSERT INTO transacoes (user_id, categoria_id, descricao, valor, data_transacao, tipo, observacoes) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId, $categoria_id, $descricao, $valor, $data_transacao, $tipo, $observacoes]);
    }

    /**
     * Lista todas as transações
     */
    public function listar($filtro = [], $limite = 100, $offset = 0) {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1;
        }

        $sql = "SELECT t.*, c.nome as categoria_nome FROM transacoes t 
            JOIN categorias c ON t.categoria_id = c.id WHERE 1=1";
        $params = [];

        if (!empty($filtro['mes']) && !empty($filtro['ano'])) {
            $sql .= " AND MONTH(t.data_transacao) = ? AND YEAR(t.data_transacao) = ?";
            $params[] = $filtro['mes'];
            $params[] = $filtro['ano'];
        }

        if (!empty($filtro['tipo'])) {
            $sql .= " AND t.tipo = ?";
            $params[] = $filtro['tipo'];
        }

        if (!empty($filtro['categoria_id'])) {
            $sql .= " AND t.categoria_id = ?";
            $params[] = $filtro['categoria_id'];
        }

        // aplicar escopo por usuário
        $sql .= " AND t.user_id = ?";
        $params[] = $userId;

        $sql .= " ORDER BY t.data_transacao DESC, t.id DESC LIMIT ? OFFSET ?";
        $params[] = $limite;
        $params[] = $offset;

        $stmt = $this->pdo->prepare($sql);

        // Bind parameters. Ensure LIMIT and OFFSET are bound as integers
        $paramCount = count($params);
        for ($i = 0; $i < $paramCount; $i++) {
            $index = $i + 1; // PDO positional params are 1-based
            $value = $params[$i];

            // The last two parameters correspond to LIMIT and OFFSET
            if ($i >= $paramCount - 2) {
                $stmt->bindValue($index, (int)$value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($index, $value);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtém uma transação por ID
     */
    public function obter($id) {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1;
        }

        $sql = "SELECT t.*, c.nome as categoria_nome FROM transacoes t 
                JOIN categorias c ON t.categoria_id = c.id WHERE t.id = ? AND t.user_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id, $userId]);
        return $stmt->fetch();
    }

    /**
     * Atualiza uma transação
     */
    public function atualizar($id, $categoria_id, $descricao, $valor, $data_transacao, $tipo, $observacoes = '') {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1;
        }

        $sql = "UPDATE transacoes SET categoria_id = ?, descricao = ?, valor = ?, 
                data_transacao = ?, tipo = ?, observacoes = ? WHERE id = ? AND user_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$categoria_id, $descricao, $valor, $data_transacao, $tipo, $observacoes, $id, $userId]);
    }

    /**
     * Deleta uma transação
     */
    public function deletar($id) {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1;
        }

        $sql = "DELETE FROM transacoes WHERE id = ? AND user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id, $userId]);
    }

    /**
     * Obtém resumo mensal
     */
    public function resumoMensal($mes, $ano) {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1;
        }

        $sql = "SELECT 
                    tipo,
                    COUNT(*) as quantidade,
                    SUM(valor) as total
                FROM transacoes
                WHERE MONTH(data_transacao) = ? AND YEAR(data_transacao) = ? AND user_id = ?
                GROUP BY tipo";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$mes, $ano, $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtém resumo por categoria
     */
    public function resumoPorCategoria($mes, $ano) {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1;
        }

        $sql = "SELECT 
                    c.nome as categoria,
                    t.tipo,
                    COUNT(*) as quantidade,
                    SUM(t.valor) as total
                FROM transacoes t
                JOIN categorias c ON t.categoria_id = c.id
                WHERE MONTH(t.data_transacao) = ? AND YEAR(t.data_transacao) = ? AND t.user_id = ?
                GROUP BY c.id, c.nome, t.tipo
                ORDER BY total DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$mes, $ano, $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Marca/Desmarca uma transação como paga
     */
    public function marcarPago($id, $pago = 1) {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1;
        }

        $sql = "UPDATE transacoes SET pago = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([(int)$pago, $id, $userId]);
    }

    /**
     * Retorna o total de valores por tipo (receita/despesa) num período.
     * Se $apenasPagos for true, considera apenas transações com pago = 1.
     */
    public function totalPorTipo($tipo, $mes, $ano, $apenasPagos = false) {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1;
        }

        $sql = "SELECT COALESCE(SUM(valor), 0) as total FROM transacoes WHERE tipo = ? AND MONTH(data_transacao) = ? AND YEAR(data_transacao) = ? AND user_id = ?";
        $params = [$tipo, $mes, $ano, $userId];
        if ($apenasPagos) {
            $sql .= " AND pago = 1";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch();
        return (float) ($res['total'] ?? 0);
    }
}
?>
