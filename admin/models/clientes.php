<?php

require_once __DIR__ . '/../config/conexao.php';

class Cliente
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
            "SELECT id, nome, email, telefone
             FROM usuarios WHERE tipo = 'user'
             ORDER BY nome ASC"
        )->fetchAll();
    }

    public function buscarPorId($id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, nome, email, telefone
             FROM usuarios WHERE id = :id AND tipo = 'user'"
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    public function cadastrar($nome, $email, $telefone, $senha)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO usuarios (nome, email, telefone, senha, tipo)
             VALUES (:nome, :email, :telefone, :senha, 'user')"
        );
        $stmt->execute([
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'senha' => password_hash($senha, PASSWORD_DEFAULT),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function editar($id, $nome, $email, $telefone, $senha = '')
    {
        $parametros = [
            'id' => $id,
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
        ];
        $sql = "UPDATE usuarios
                SET nome = :nome, email = :email, telefone = :telefone
                WHERE id = :id AND tipo = 'user'";

        if ($senha !== '') {
            $sql = "UPDATE usuarios
                    SET nome = :nome, email = :email, telefone = :telefone,
                        senha = :senha
                    WHERE id = :id AND tipo = 'user'";
            $parametros['senha'] = password_hash($senha, PASSWORD_DEFAULT);
        }

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($parametros);
    }

    public function deletar($id)
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM usuarios WHERE id = :id AND tipo = 'user'"
        );

        return $stmt->execute(['id' => $id]);
    }
}
