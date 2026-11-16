<?php

require_once __DIR__ . '/../config/conexao.php';

class Pedido
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function listar()
    {
        return $this->pdo->query(
            'SELECT p.*, u.nome AS cliente, u.email,
                    g.titulo AS jogo
             FROM pedidos p
             INNER JOIN usuarios u ON u.id = p.usuario_id
             INNER JOIN games g ON g.id = p.game_id
             ORDER BY p.criado_em DESC, p.id DESC'
        )->fetchAll();
    }

    public function buscarPorId($id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM pedidos WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    public function buscarPorClienteJogo($usuarioId, $gameId)
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM pedidos
             WHERE usuario_id = :usuario_id AND game_id = :game_id'
        );
        $stmt->execute(['usuario_id' => $usuarioId, 'game_id' => $gameId]);

        return $stmt->fetch();
    }

    public function cadastrar($usuarioId, $gameId, $preco, $status = 'concluido')
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO pedidos (usuario_id, game_id, preco_total, status)
             VALUES (:usuario_id, :game_id, :preco_total, :status)'
        );

        return $stmt->execute([
            'usuario_id' => $usuarioId,
            'game_id' => $gameId,
            'preco_total' => $preco,
            'status' => $status,
        ]);
    }

    public function editar($id, $usuarioId, $gameId, $preco, $status)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE pedidos SET
                usuario_id = :usuario_id,
                game_id = :game_id,
                preco_total = :preco_total,
                status = :status
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'usuario_id' => $usuarioId,
            'game_id' => $gameId,
            'preco_total' => $preco,
            'status' => $status,
        ]);
    }

    public function reativar($id, $preco)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE pedidos SET status = 'concluido', preco_total = :preco,
                criado_em = CURRENT_TIMESTAMP WHERE id = :id"
        );

        return $stmt->execute(['id' => $id, 'preco' => $preco]);
    }

    public function deletar($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM pedidos WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }
}
