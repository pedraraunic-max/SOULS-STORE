<?php

require_once __DIR__ . '/../config/conexao.php';

class Game
{
    public $id;
    public $titulo;
    public $descricao;
    public $preco;
    public $estoque;
    public $categoria_id;
    public $imagem;

    private $pdo;

    public function __construct(array $atributos = [])
    {
        global $pdo;

        $this->pdo = $pdo;
        $this->id = $atributos['id'] ?? null;
        $this->titulo = $atributos['titulo'] ?? '';
        $this->descricao = $atributos['descricao'] ?? '';
        $this->preco = $atributos['preco'] ?? '0.00';
        $this->estoque = $atributos['estoque'] ?? 0;
        $this->categoria_id = $atributos['categoria_id'] ?? null;
        $this->imagem = $atributos['imagem'] ?? '';
    }

    public function listarGames()
    {
        return $this->pdo->query(
            'SELECT g.*, c.nome AS categoria
             FROM games g
             INNER JOIN categorias c ON c.id = g.categoria_id
             ORDER BY g.titulo ASC'
        )->fetchAll();
    }

    public function listarCatalogo($nome = '', $categoriaId = '')
    {
        $condicoes = [];
        $parametros = [];

        if ($nome !== '') {
            $condicoes[] = 'g.titulo LIKE :nome';
            $parametros['nome'] = '%' . $nome . '%';
        }

        if ($categoriaId !== '') {
            $condicoes[] = 'g.categoria_id = :categoria_id';
            $parametros['categoria_id'] = $categoriaId;
        }

        $sql = 'SELECT g.*, c.nome AS categoria
                FROM games g
                INNER JOIN categorias c ON c.id = g.categoria_id';
        if ($condicoes) {
            $sql .= ' WHERE ' . implode(' AND ', $condicoes);
        }
        $sql .= ' ORDER BY g.titulo ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    public function buscarPorNome($nome)
    {
        $stmt = $this->pdo->prepare(
            'SELECT g.*, c.nome AS categoria
             FROM games g
             INNER JOIN categorias c ON c.id = g.categoria_id
             WHERE g.titulo LIKE :nome
             ORDER BY g.titulo ASC'
        );
        $stmt->execute(['nome' => '%' . $nome . '%']);

        return $stmt->fetchAll();
    }

    public function buscarGamePorId($id)
    {
        $stmt = $this->pdo->prepare(
            'SELECT g.*, c.nome AS categoria
             FROM games g
             INNER JOIN categorias c ON c.id = g.categoria_id
             WHERE g.id = :id'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    public function cadastrarGame()
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO games
                (titulo, descricao, preco, estoque, categoria_id, imagem)
             VALUES
                (:titulo, :descricao, :preco, :estoque, :categoria_id, :imagem)'
        );

        return $stmt->execute($this->atributosSql());
    }

    public function editarGame()
    {
        $stmt = $this->pdo->prepare(
            'UPDATE games SET
                titulo = :titulo,
                descricao = :descricao,
                preco = :preco,
                estoque = :estoque,
                categoria_id = :categoria_id,
                imagem = :imagem
             WHERE id = :id'
        );
        $parametros = $this->atributosSql();
        $parametros['id'] = $this->id;

        return $stmt->execute($parametros);
    }

    public function deletarGame()
    {
        $stmt = $this->pdo->prepare('DELETE FROM games WHERE id = :id');

        return $stmt->execute(['id' => $this->id]);
    }

    private function atributosSql()
    {
        return [
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'preco' => $this->preco,
            'estoque' => $this->estoque,
            'categoria_id' => $this->categoria_id,
            'imagem' => $this->imagem,
        ];
    }
}
