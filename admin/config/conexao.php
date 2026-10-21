<?php

$servidor = 'localhost';
$porta = '3306';
$banco = 'loja_games';
$usuario = 'root';
$senha = '';

try {
    $pdo = new PDO(
        "mysql:host=$servidor;port=$porta;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $erro) {
    error_log($erro->getMessage());

    http_response_code(500);

    exit(
        'Não foi possível conectar ao banco. '
        . 'Confira o MySQL e as configurações de conexao.php.'
    );
}