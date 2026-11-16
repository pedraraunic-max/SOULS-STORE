<?php

require_once __DIR__ . '/admin/config/funcoes.php';
require_once __DIR__ . '/admin/models/clientes.php';

if (usuarioAutenticado() !== null) {
    redirecionar(urlApp('index.php'));
}

$erro = '';
$nome = '';
$email = '';
$telefone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigirPostValido();

    $nomeRecebido = $_POST['nome'] ?? '';
    $emailRecebido = $_POST['email'] ?? '';
    $telefoneRecebido = $_POST['telefone'] ?? '';
    $senhaRecebida = $_POST['senha'] ?? '';

    if (
        !is_string($nomeRecebido)
        || !is_string($emailRecebido)
        || !is_string($telefoneRecebido)
        || !is_string($senhaRecebida)
    ) {
        $erro = 'Confira os campos e tente novamente.';
    } else {
        $nome = trim($nomeRecebido);
        $email = strtolower(trim($emailRecebido));
        $telefone = trim($telefoneRecebido);
        $tamanhoNome = preg_match_all('/./us', $nome);
        $tamanhoSenha = preg_match_all('/./us', $senhaRecebida);
        $digitosTelefone = preg_replace('/\D+/', '', $telefone);

        if (
            $nome === ''
            || $tamanhoNome === false
            || $tamanhoNome > 100
        ) {
            $erro = 'Informe seu nome, com até 100 caracteres.';
        } elseif (
            !filter_var($email, FILTER_VALIDATE_EMAIL)
            || strlen($email) > 150
        ) {
            $erro = 'Informe um e-mail válido, com até 150 caracteres.';
        } elseif (
            strlen($telefone) > 30
            || strlen($digitosTelefone) < 8
            || strlen($digitosTelefone) > 15
            || !preg_match('/^[+0-9 ()-]+$/D', $telefone)
        ) {
            $erro = 'Informe um telefone válido, com DDD.';
        } elseif (
            $tamanhoSenha === false
            || $tamanhoSenha < 8
            || strlen($senhaRecebida) > 72
            || strpos($senhaRecebida, "\0") !== false
        ) {
            $erro = 'A senha deve ter pelo menos 8 caracteres e até 72 bytes.';
        } else {
            $model = new Cliente();

            try {
                // O cadastro público sempre cria uma conta comum.
                $id = $model->cadastrar($nome, $email, $telefone, $senhaRecebida);

                session_regenerate_id(true);
                $_SESSION['csrf'] = bin2hex(random_bytes(32));
                $_SESSION['ultima_atividade'] = time();
                $_SESSION['usuario'] = [
                    'id' => $id,
                    'nome' => $nome,
                    'email' => $email,
                    'tipo' => 'user',
                ];

                redirecionar(urlApp('index.php'));
            } catch (PDOException $exception) {
                if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                    $erro = 'Já existe uma conta cadastrada com esse e-mail.';
                } else {
                    error_log($exception->getMessage());
                    $erro = 'Não foi possível criar sua conta agora.';
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#07111d">
    <title>Despertar — Souls Store</title>
    <link rel="icon" type="image/svg+xml" href="<?= e(urlApp('admin/public/images/souls-store-icon.svg')) ?>">
    <link rel="stylesheet" href="<?= e(urlApp('admin/public/css/style.css?v=20261022')) ?>">
</head>
<body class="pagina-auth">
<main class="auth-layout">
    <section class="auth-lateral">
        <a class="marca-loja" href="<?= e(urlApp('index.php')) ?>">
            <span class="marca-loja__icone" aria-hidden="true">✦</span>
            <span>SOULS<span class="marca-loja__claro"> STORE</span></span>
        </a>
        <div class="auth-lateral__mensagem">
            <span class="sobretitulo">BORA JOGAR?</span>
            <h1>Encontre sua<br>próxima <span>aventura.</span></h1>
            <p>Crie sua conta e faça parte do acervo dos sem-luz.</p>
        </div>
        <div class="auth-lateral__arte" aria-hidden="true"><span>✦</span></div>
        <span class="auth-lateral__rodape">SOULS STORE <i>·</i> ACERVO DOS SEM-LUZ</span>
    </section>

    <section class="auth-conteudo">
        <a class="auth-voltar" href="<?= e(urlApp('index.php')) ?>">← Voltar à loja</a>
        <div class="auth-formulario-wrap">
            <span class="sobretitulo">DESPERTE SUA JORNADA</span>
            <h2>Criar conta</h2>
            <p class="auth-intro">Leva só um minuto para entrar no jogo.</p>

            <?php if ($erro !== ''): ?>
                <div class="mensagem-erro" role="alert"><?= e($erro) ?></div>
            <?php endif; ?>

            <form class="formulario-auth" method="post" action="<?= e(urlApp('registrar.php')) ?>">
                <input type="hidden" name="csrf" value="<?= e(tokenCsrf()) ?>">
                <label for="nome">Nome</label>
                <input id="nome" type="text" name="nome" maxlength="100" autocomplete="name" required value="<?= e($nome) ?>">

                <label for="email">E-mail</label>
                <input id="email" type="email" name="email" maxlength="150" autocomplete="email" required value="<?= e($email) ?>">

                <label for="telefone">Telefone</label>
                <input id="telefone" type="tel" name="telefone" maxlength="30" autocomplete="tel" required value="<?= e($telefone) ?>" placeholder="(00) 00000-0000">

                <label for="senha">Senha</label>
                <input id="senha" type="password" name="senha" minlength="8" autocomplete="new-password" required>
                <small>Use pelo menos 8 caracteres.</small>

                <button class="botao-principal botao-principal--largo" type="submit">Criar conta <span aria-hidden="true">→</span></button>
            </form>
            <p class="auth-ja-tem-conta">Já tem uma conta? <a href="<?= e(urlApp('login.php')) ?>">Entrar</a></p>
        </div>
    </section>
</main>
</body>
</html>
