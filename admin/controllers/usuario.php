<?php

require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
exigirPostValido();
require_once __DIR__ . '/../models/usuarios.php';

function mostrarErroUsuario($mensagem, $status = 422)
{
    http_response_code($status);
    header('Content-Type: text/html; charset=UTF-8');

    echo '<h1>Não foi possível concluir</h1>';
    echo '<p>' . e($mensagem) . '</p>';
    echo '<p>Use Voltar no navegador para revisar os dados.</p>';
    echo '<a href="../views/ver_usuarios.php">Voltar para usuários</a>';

    exit;
}

// Os campos deste formulário devem ser textos, nunca arrays.
foreach (['acao', 'id', 'nome', 'email', 'senha', 'tipo'] as $campo) {
    if (isset($_POST[$campo]) && !is_string($_POST[$campo])) {
        mostrarErroUsuario('Formato de campo inválido.', 400);
    }
}

$acao = $_POST['acao'] ?? '';

if (!in_array($acao, ['salvar', 'excluir'], true)) {
    mostrarErroUsuario('Ação inválida.', 400);
}

$idTexto = trim($_POST['id'] ?? '');
$id = null;

if ($idTexto !== '') {
    $id = filter_var(
        $idTexto,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($id === false) {
        mostrarErroUsuario('ID inválido.', 400);
    }
}

$model = new Usuario();

try {
    $usuarioExistente = null;

    if ($id !== null) {
        $usuarioExistente = $model->buscarPorId($id);

        if (!$usuarioExistente) {
            mostrarErroUsuario('Usuário não encontrado.', 404);
        }
    }

    if ($acao === 'excluir') {
        if ($id === null) {
            mostrarErroUsuario('Informe o usuário para excluir.', 400);
        }

        if ($id === (int) (usuarioAutenticado()['id'] ?? 0)) {
            mostrarErroUsuario('Você não pode excluir a própria conta enquanto está conectado.', 409);
        }

        if (
            $usuarioExistente['tipo'] === 'admin'
            && $model->contarAdministradores() <= 1
        ) {
            mostrarErroUsuario('A loja precisa manter pelo menos um administrador.', 409);
        }

        $model->deletar($id);

        redirecionar('../views/ver_usuarios.php');
    }

    $nome = trim($_POST['nome'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));

    // Não usamos trim na senha: espaços podem fazer parte dela.
    $senha = $_POST['senha'] ?? '';

    $tipo = $_POST['tipo'] ?? '';

    $tamanhoNome = preg_match_all('/./us', $nome);

    if (
        $nome === ''
        || $tamanhoNome === false
        || $tamanhoNome > 100
    ) {
        mostrarErroUsuario('Informe um nome com até 100 caracteres.');
    }

    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL)
        || strlen($email) > 150
    ) {
        mostrarErroUsuario('Informe um e-mail válido com até 150 caracteres.');
    }

    if (!in_array($tipo, ['admin', 'user'], true)) {
        mostrarErroUsuario('Tipo de usuário inválido.');
    }

    if (
        $id === (int) (usuarioAutenticado()['id'] ?? 0)
        && $tipo !== 'admin'
    ) {
        mostrarErroUsuario('Você não pode remover o próprio acesso de administrador.', 409);
    }

    if (
        $usuarioExistente
        && $usuarioExistente['tipo'] === 'admin'
        && $tipo === 'user'
        && $model->contarAdministradores() <= 1
    ) {
        mostrarErroUsuario('A loja precisa manter pelo menos um administrador.', 409);
    }

    if ($id === null && $senha === '') {
        mostrarErroUsuario('A senha é obrigatória no cadastro.');
    }

    if ($senha !== '') {
        $tamanhoSenha = preg_match_all('/./us', $senha);

        if (
            $tamanhoSenha === false
            || $tamanhoSenha < 8
            || strlen($senha) > 72
            || strpos($senha, "\0") !== false
        ) {
            mostrarErroUsuario(
                'A senha deve ter pelo menos 8 caracteres, '
                . 'no máximo 72 bytes e não pode conter caractere nulo.'
            );
        }
    }

    if ($id === null) {
        $model->cadastrar($nome, $email, $senha, $tipo);
    } else {
        $model->editar($id, $nome, $email, $senha, $tipo);
    }

    redirecionar('../views/ver_usuarios.php');

} catch (PDOException $erro) {
    // Código de entrada duplicada no MySQL/MariaDB.
    if ((int) ($erro->errorInfo[1] ?? 0) === 1062) {
        mostrarErroUsuario('Esse e-mail já está cadastrado.', 409);
    }

    error_log($erro->getMessage());

    mostrarErroUsuario(
        'Ocorreu um erro no banco. Consulte o log do PHP.',
        500
    );
}
