<?php

require_once __DIR__ . '/../config/conexao.php';

class Categoria
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
            'SELECT c.id, c.nome, c.descricao, COUNT(g.id) AS total_games
             FROM categorias c
             LEFT JOIN games g ON g.categoria_id = c.id
             GROUP BY c.id, c.nome, c.descricao
             ORDER BY c.nome ASC'
        )->fetchAll();
    }

    public function buscarPorId($id)
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nome, descricao FROM categorias WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    public function cadastrar($nome, $descricao)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO categorias (nome, descricao)
             VALUES (:nome, :descricao)'
        );

        return $stmt->execute(['nome' => $nome, 'descricao' => $descricao]);
    }

    public function editar($id, $nome, $descricao)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE categorias SET nome = :nome, descricao = :descricao
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'nome' => $nome,
            'descricao' => $descricao,
        ]);
    }

    public function deletar($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM categorias WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }
}
