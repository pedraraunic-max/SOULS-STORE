<?php

require_once __DIR__ . '/../config/conexao.php';

class Game
{
    public $id;
    public $titulo;
    public $descricao;
    public $preco;
    public $estoque;
    public $categoria;
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
        $this->categoria = $atributos['categoria'] ?? '';
        $this->imagem = $atributos['imagem'] ?? '';
    }

    public function listarGames()
    {
        $stmt = $this->pdo->query(
            'SELECT * FROM games ORDER BY titulo ASC'
        );

        return $stmt->fetchAll();
    }

    public function listarCatalogo($nome = '', $categoria = '')
    {
        $condicoes = [];
        $parametros = [];

        if ($nome !== '') {
            $condicoes[] = 'titulo LIKE :nome';
            $parametros['nome'] = '%' . $nome . '%';
        }

        if ($categoria !== '') {
            $condicoes[] = 'categoria = :categoria';
            $parametros['categoria'] = $categoria;
        }

        $sql = 'SELECT * FROM games';

        if ($condicoes) {
            $sql .= ' WHERE ' . implode(' AND ', $condicoes);
        }

        $sql .= ' ORDER BY titulo ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    public function listarCategorias()
    {
        $stmt = $this->pdo->query(
            "SELECT DISTINCT categoria FROM games
             WHERE categoria IS NOT NULL AND categoria <> ''
             ORDER BY categoria ASC"
        );

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function buscarPorNome($nome)
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM games
             WHERE titulo LIKE :nome
             ORDER BY titulo ASC'
        );

        $stmt->execute([
            'nome' => '%' . $nome . '%',
        ]);

        return $stmt->fetchAll();
    }

    public function buscarGamePorId($id)
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM games WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->fetch();
    }

    public function cadastrarGame()
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO games
                (titulo, descricao, preco, estoque, categoria, imagem)
             VALUES
                (:titulo, :descricao, :preco, :estoque, :categoria, :imagem)'
        );

        return $stmt->execute([
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'preco' => $this->preco,
            'estoque' => $this->estoque,
            'categoria' => $this->categoria,
            'imagem' => $this->imagem,
        ]);
    }

    public function editarGame()
    {
        $stmt = $this->pdo->prepare(
            'UPDATE games SET
                titulo = :titulo,
                descricao = :descricao,
                preco = :preco,
                estoque = :estoque,
                categoria = :categoria,
                imagem = :imagem
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'preco' => $this->preco,
            'estoque' => $this->estoque,
            'categoria' => $this->categoria,
            'imagem' => $this->imagem,
        ]);
    }

    public function deletarGame()
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM games WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $this->id,
        ]);
    }
}
