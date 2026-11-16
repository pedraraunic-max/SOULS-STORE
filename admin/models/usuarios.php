<?php

require_once __DIR__ . '/../config/conexao.php';

class Usuario
{
    private const LIMITE_TENTATIVAS_LOGIN = 5;

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
            'SELECT id, nome, email, telefone, tipo
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
            'SELECT id, nome, email, telefone, tipo
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

    private function chavesTentativaLogin($email, $ip)
    {
        $chaveAplicacao = getenv('APP_KEY') ?: 'souls-store-local-login-key';

        return [
            'email_hash' => hash_hmac(
                'sha256',
                strtolower(trim($email)),
                $chaveAplicacao
            ),
            'ip_hash' => hash_hmac(
                'sha256',
                trim((string) $ip) ?: 'desconhecido',
                $chaveAplicacao
            ),
        ];
    }

    public function tentativaLoginBloqueada($email, $ip)
    {
        $chaves = $this->chavesTentativaLogin($email, $ip);

        $limpeza = $this->pdo->prepare(
            'DELETE FROM tentativas_login
             WHERE atualizado_em < DATE_SUB(NOW(), INTERVAL 1 DAY)'
        );
        $limpeza->execute();

        $stmt = $this->pdo->prepare(
            'SELECT tentativas
             FROM tentativas_login
             WHERE email_hash = :email_hash
               AND ip_hash = :ip_hash
               AND atualizado_em >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
        );
        $stmt->execute($chaves);

        return (int) $stmt->fetchColumn() >= self::LIMITE_TENTATIVAS_LOGIN;
    }

    public function registrarFalhaLogin($email, $ip)
    {
        $chaves = $this->chavesTentativaLogin($email, $ip);
        $stmt = $this->pdo->prepare(
            'INSERT INTO tentativas_login
                (email_hash, ip_hash, tentativas, atualizado_em)
             VALUES
                (:email_hash, :ip_hash, 1, NOW())
             ON DUPLICATE KEY UPDATE
                tentativas = IF(
                    atualizado_em < DATE_SUB(NOW(), INTERVAL 15 MINUTE),
                    1,
                    LEAST(tentativas + 1, 255)
                ),
                atualizado_em = NOW()'
        );

        return $stmt->execute($chaves);
    }

    public function limparFalhasLogin($email, $ip)
    {
        $chaves = $this->chavesTentativaLogin($email, $ip);
        $stmt = $this->pdo->prepare(
            'DELETE FROM tentativas_login
             WHERE email_hash = :email_hash AND ip_hash = :ip_hash'
        );

        return $stmt->execute($chaves);
    }

    public function atualizarHashSenha($id, $hash)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE usuarios SET senha = :senha WHERE id = :id'
        );

        return $stmt->execute(['id' => $id, 'senha' => $hash]);
    }

    public function cadastrar($nome, $email, $senha, $tipo, $telefone = '')
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (nome, email, telefone, senha, tipo)
             VALUES (:nome, :email, :telefone, :senha, :tipo)'
        );

        return $stmt->execute([
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'senha' => password_hash($senha, PASSWORD_DEFAULT),
            'tipo' => $tipo,
        ]);
    }

    public function editar($id, $nome, $email, $senha, $tipo, $telefone = '')
    {
        $parametros = [
            'id' => $id,
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'tipo' => $tipo,
        ];

        $sql = 'UPDATE usuarios
                SET nome = :nome, email = :email,
                    telefone = :telefone, tipo = :tipo';

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
