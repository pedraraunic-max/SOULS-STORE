<?php

require_once __DIR__ . '/../config/conexao.php';

class Usuario
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function listar()
    {
        // Não precisamos buscar as senhas para exibir a lista.
        $stmt = $this->pdo->query(
            'SELECT id, nome, email, tipo
             FROM usuarios
             ORDER BY nome ASC'
        );

        return $stmt->fetchAll();
    }

    public function contarAdministradores()
    {
        $stmt = $this->pdo->query(
            "SELECT COUNT(*) FROM usuarios WHERE tipo = 'admin'"
        );

        return (int) $stmt->fetchColumn();
    }

    public function buscarPorId($id)
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nome, email, tipo
             FROM usuarios
             WHERE id = :id'
        );

        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    public function buscarParaLogin($email)
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nome, email, senha, tipo
             FROM usuarios
             WHERE email = :email'
        );

        $stmt->execute(['email' => $email]);

        return $stmt->fetch();
    }

    public function cadastrar($nome, $email, $senha, $tipo)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (nome, email, senha, tipo)
             VALUES (:nome, :email, :senha, :tipo)'
        );

        return $stmt->execute([
            'nome' => $nome,
            'email' => $email,
            'senha' => password_hash($senha, PASSWORD_DEFAULT),
            'tipo' => $tipo,
        ]);
    }

    public function editar($id, $nome, $email, $senha, $tipo)
    {
        $parametros = [
            'id' => $id,
            'nome' => $nome,
            'email' => $email,
            'tipo' => $tipo,
        ];

        $sql = 'UPDATE usuarios
                SET nome = :nome, email = :email, tipo = :tipo';

        // Só altera a senha quando uma nova é informada.
        if ($senha !== '') {
            $sql .= ', senha = :senha';

            $parametros['senha'] = password_hash(
                $senha,
                PASSWORD_DEFAULT
            );
        }

        $sql .= ' WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($parametros);
    }

    public function deletar($id)
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM usuarios WHERE id = :id'
        );

        return $stmt->execute(['id' => $id]);
    }
}
