<?php
/**
 * Classe para gerenciar Contas
 */

class Conta {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Adiciona uma nova conta
     */
    public function adicionar($nome, $descricao, $saldo_inicial) {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1;
        }

        $sql = "INSERT INTO contas (user_id, nome, descricao, saldo_inicial, saldo_atual) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId, $nome, $descricao, $saldo_inicial, $saldo_inicial]);
    }

    /**
     * Lista todas as contas
     */
    public function listar() {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1;
        }

        $sql = "SELECT * FROM contas WHERE ativa = TRUE AND user_id = ? ORDER BY nome";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtém uma conta por ID
     */
    public function obter($id) {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1;
        }

        $sql = "SELECT * FROM contas WHERE id = ? AND ativa = TRUE AND user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id, $userId]);
        return $stmt->fetch();
    }

    /**
     * Atualiza uma conta
     */
    public function atualizar($id, $nome, $descricao, $saldo_atual) {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1;
        }

        $sql = "UPDATE contas SET nome = ?, descricao = ?, saldo_atual = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nome, $descricao, $saldo_atual, $id, $userId]);
    }

    /**
     * Desativa uma conta
     */
    public function desativar($id) {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1;
        }

        $sql = "UPDATE contas SET ativa = FALSE WHERE id = ? AND user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id, $userId]);
    }

    /**
     * Obtém saldo total
     */
    public function saldoTotal() {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1;
        }

        $sql = "SELECT SUM(saldo_atual) as total FROM contas WHERE ativa = TRUE AND user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        $resultado = $stmt->fetch();
        return $resultado['total'] ?? 0;
    }
}
?>
