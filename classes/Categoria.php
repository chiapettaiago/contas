<?php
/**
 * Classe para gerenciar Categorias
 */

class Categoria {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Adiciona uma nova categoria
     */
    public function adicionar($nome, $descricao, $tipo) {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1;
        }

        $sql = "INSERT INTO categorias (user_id, nome, descricao, tipo) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId, $nome, $descricao, $tipo]);
    }

    /**
     * Lista todas as categorias
     */
    public function listar($tipo = null) {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1;
        }

        if ($tipo) {
            $sql = "SELECT * FROM categorias WHERE tipo = ? AND ativo = TRUE AND user_id = ? ORDER BY nome";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$tipo, $userId]);
        } else {
            $sql = "SELECT * FROM categorias WHERE ativo = TRUE AND user_id = ? ORDER BY tipo, nome";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
        }
        return $stmt->fetchAll();
    }

    /**
     * Obtém uma categoria por ID
     */
    public function obter($id) {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1;
        }

        $sql = "SELECT * FROM categorias WHERE id = ? AND user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id, $userId]);
        return $stmt->fetch();
    }

    /**
     * Atualiza uma categoria
     */
    public function atualizar($id, $nome, $descricao, $tipo) {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1;
        }

        $sql = "UPDATE categorias SET nome = ?, descricao = ?, tipo = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nome, $descricao, $tipo, $id, $userId]);
    }

    /**
     * Desativa uma categoria
     */
    public function desativar($id) {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1;
        }

        $sql = "UPDATE categorias SET ativo = FALSE WHERE id = ? AND user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id, $userId]);
    }

    /**
     * Ativa uma categoria
     */
    public function ativar($id) {
        $userId = null;
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        } else {
            $userId = 1;
        }

        $sql = "UPDATE categorias SET ativo = TRUE WHERE id = ? AND user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id, $userId]);
    }
}
?>
