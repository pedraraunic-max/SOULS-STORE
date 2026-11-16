<?php

require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
exigirPostValido();
require_once __DIR__ . '/../models/categorias.php';

function erroCategoria($mensagem, $status = 422)
{
    http_response_code($status);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<h1>Não foi possível concluir</h1><p>' . e($mensagem) . '</p>';
    echo '<p><a href="../views/ver_categorias.php">Voltar para categorias</a></p>';
    exit;
}

foreach (['acao', 'id', 'nome', 'descricao'] as $campo) {
    if (isset($_POST[$campo]) && !is_string($_POST[$campo])) {
        erroCategoria('Formato de campo inválido.', 400);
    }
}
$acao = $_POST['acao'] ?? '';
$idTexto = trim($_POST['id'] ?? '');
$id = $idTexto === '' ? null : filter_var($idTexto, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    erroCategoria('ID inválido.', 400);
}
$model = new Categoria();

try {
    if ($acao === 'excluir') {
        if ($id === null || !$model->buscarPorId($id)) {
            erroCategoria('Categoria não encontrada.', 404);
        }
        $model->deletar($id);
        redirecionar('../views/ver_categorias.php');
    }
    if ($acao !== 'salvar') {
        erroCategoria('Ação inválida.', 400);
    }
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $tamanhoNome = preg_match_all('/./us', $nome);
    $tamanhoDescricao = preg_match_all('/./us', $descricao);
    if ($nome === '' || $tamanhoNome === false || $tamanhoNome > 50) {
        erroCategoria('O nome é obrigatório e deve ter até 50 caracteres.');
    }
    if ($tamanhoDescricao === false || $tamanhoDescricao > 255) {
        erroCategoria('A descrição deve ter até 255 caracteres.');
    }
    if ($id !== null && !$model->buscarPorId($id)) {
        erroCategoria('Categoria não encontrada.', 404);
    }
    if ($id === null) {
        $model->cadastrar($nome, $descricao);
    } else {
        $model->editar($id, $nome, $descricao);
    }
    redirecionar('../views/ver_categorias.php');
} catch (PDOException $erro) {
    $codigo = (int) ($erro->errorInfo[1] ?? 0);
    if ($codigo === 1062) {
        erroCategoria('Já existe uma categoria com esse nome.', 409);
    }
    if ($codigo === 1451) {
        erroCategoria('Essa categoria ainda está associada a um ou mais jogos. Reclassifique os jogos antes de excluí-la.', 409);
    }
    error_log($erro->getMessage());
    erroCategoria('Erro ao salvar a categoria no banco de dados.', 500);
}
