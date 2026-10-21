<?php

require_once __DIR__ . '/admin/config/funcoes.php';
require_once __DIR__ . '/admin/models/usuarios.php';

if (usuarioAutenticado() !== null) {
    redirecionar(urlApp(
        usuarioEhAdmin() ? 'admin/views/ver_games.php' : 'index.php'
    ));
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigirPostValido();

    $emailRecebido = $_POST['email'] ?? '';
    $senhaRecebida = $_POST['senha'] ?? '';

    if (!is_string($emailRecebido) || !is_string($senhaRecebida)) {
        $erro = 'Confira seu e-mail e sua senha.';
    } else {
        $email = strtolower(trim($emailRecebido));
        $model = new Usuario();
        $registro = $model->buscarParaLogin($email);

        if (
            $registro
            && in_array($registro['tipo'], ['admin', 'user'], true)
            && password_verify($senhaRecebida, $registro['senha'])
        ) {
            session_regenerate_id(true);
            $_SESSION['usuario'] = [
                'id' => (int) $registro['id'],
                'nome' => $registro['nome'],
                'email' => $registro['email'],
                'tipo' => $registro['tipo'],
            ];

            redirecionar(urlApp(
                $registro['tipo'] === 'admin'
                    ? 'admin/views/ver_games.php'
                    : 'index.php'
            ));
        }

        $erro = 'E-mail ou senha incorretos.';
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#07111d">
    <title>Entrar — GameStock</title>
    <link rel="stylesheet" href="<?= e(urlApp('admin/public/css/style.css')) ?>">
</head>
<body class="pagina-auth">
<main class="auth-layout">
    <section class="auth-lateral">
        <a class="marca-loja" href="<?= e(urlApp('index.php')) ?>">
            <span class="marca-loja__icone">G</span>
            <span>GAME<span class="marca-loja__claro">STOCK</span></span>
        </a>
        <div class="auth-lateral__mensagem">
            <span class="sobretitulo">BEM-VINDO DE VOLTA</span>
            <h1>Seu próximo<br>jogo está <span>perto.</span></h1>
            <p>Entre para continuar explorando o catálogo GameStock.</p>
        </div>
        <div class="auth-lateral__arte" aria-hidden="true"><span>✦</span></div>
        <span class="auth-lateral__rodape">GAMESTOCK <i>·</i> PLAY YOUR WAY</span>
    </section>

    <section class="auth-conteudo">
        <a class="auth-voltar" href="<?= e(urlApp('index.php')) ?>">← Voltar à loja</a>
        <div class="auth-formulario-wrap">
            <span class="sobretitulo">SUA CONTA GAMESTOCK</span>
            <h2>Entrar</h2>
            <p class="auth-intro">Acesse sua conta para continuar.</p>

            <?php if ($erro !== ''): ?>
                <div class="mensagem-erro" role="alert"><?= e($erro) ?></div>
            <?php endif; ?>

            <form class="formulario-auth" method="post" action="<?= e(urlApp('login.php')) ?>">
                <input type="hidden" name="csrf" value="<?= e(tokenCsrf()) ?>">
                <label for="email">E-mail</label>
                <input id="email" type="email" name="email" maxlength="150" autocomplete="email" required value="<?= e(is_string($_POST['email'] ?? null) ? $_POST['email'] : '') ?>">

                <div class="campo-senha-linha"><label for="senha">Senha</label><a href="<?= e(urlApp('registrar.php')) ?>">Criar uma conta</a></div>
                <input id="senha" type="password" name="senha" autocomplete="current-password" required>

                <button class="botao-principal botao-principal--largo" type="submit">Entrar na GameStock <span aria-hidden="true">→</span></button>
            </form>
            <p class="auth-seguranca"><span aria-hidden="true">◈</span> Seus dados são protegidos durante o acesso.</p>
        </div>
    </section>
</main>
</body>
</html>
