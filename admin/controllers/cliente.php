<?php

require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
exigirPostValido();
require_once __DIR__ . '/../models/clientes.php';

function erroCliente($mensagem, $status = 422)
{
    http_response_code($status);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<h1>Não foi possível concluir</h1><p>' . e($mensagem) . '</p>';
    echo '<p><a href="../views/ver_clientes.php">Voltar para clientes</a></p>';
    exit;
}

foreach (['acao', 'id', 'nome', 'email', 'telefone', 'senha'] as $campo) {
    if (isset($_POST[$campo]) && !is_string($_POST[$campo])) {
        erroCliente('Formato de campo inválido.', 400);
    }
}

$acao = $_POST['acao'] ?? '';
$idTexto = trim($_POST['id'] ?? '');
$id = $idTexto === '' ? null : filter_var(
    $idTexto,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
if ($id === false) {
    erroCliente('ID inválido.', 400);
}
$model = new Cliente();

try {
    if ($acao === 'excluir') {
        if ($id === null || !$model->buscarPorId($id)) {
            erroCliente('Cliente não encontrado.', 404);
        }
        $model->deletar($id);
        redirecionar('../views/ver_clientes.php');
    }
    if ($acao !== 'salvar') {
        erroCliente('Ação inválida.', 400);
    }

    $nome = trim($_POST['nome'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $telefone = trim($_POST['telefone'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $tamanhoNome = preg_match_all('/./us', $nome);
    $digitosTelefone = preg_replace('/\D+/', '', $telefone);
    $tamanhoSenha = preg_match_all('/./us', $senha);

    if ($nome === '' || $tamanhoNome === false || $tamanhoNome > 100) {
        erroCliente('Informe um nome com até 100 caracteres.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        erroCliente('Informe um e-mail válido.');
    }
    if (
        strlen($telefone) > 30 || strlen($digitosTelefone) < 8
        || strlen($digitosTelefone) > 15 || !preg_match('/^[+0-9 ()-]+$/D', $telefone)
    ) {
        erroCliente('Informe um telefone válido, com DDD.');
    }
    if (
        ($id === null && ($tamanhoSenha === false || $tamanhoSenha < 8))
        || ($senha !== '' && ($tamanhoSenha === false || $tamanhoSenha < 8 || strlen($senha) > 72 || strpos($senha, "\0") !== false))
    ) {
        erroCliente('A senha deve ter pelo menos 8 caracteres e até 72 bytes.');
    }
    if ($id !== null && !$model->buscarPorId($id)) {
        erroCliente('Cliente não encontrado.', 404);
    }

    if ($id === null) {
        $model->cadastrar($nome, $email, $telefone, $senha);
    } else {
        $model->editar($id, $nome, $email, $telefone, $senha);
    }
    redirecionar('../views/ver_clientes.php');
} catch (PDOException $erro) {
    if ((int) ($erro->errorInfo[1] ?? 0) === 1062) {
        erroCliente('Já existe uma conta com esse e-mail.', 409);
    }
    if ((int) ($erro->errorInfo[1] ?? 0) === 1451) {
        erroCliente('Este cliente possui pedidos. Exclua os pedidos antes de remover o cadastro.', 409);
    }
    error_log($erro->getMessage());
    erroCliente('Erro ao salvar o cliente no banco de dados.', 500);
}
