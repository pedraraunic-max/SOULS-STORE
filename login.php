<?php

require_once __DIR__ . '/admin/config/funcoes.php';
require_once __DIR__ . '/admin/models/usuarios.php';

if (usuarioAutenticado() !== null) {
    redirecionar(urlApp(
        usuarioEhAdmin() ? 'admin/views/ver_games.php' : 'index.php'
    ));
}

$erro = '';
$avisoLogin = $_SESSION['aviso_login'] ?? '';
unset($_SESSION['aviso_login']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigirPostValido();

    $emailRecebido = $_POST['email'] ?? '';
    $senhaRecebida = $_POST['senha'] ?? '';

    if (!is_string($emailRecebido) || !is_string($senhaRecebida)) {
        $erro = 'Confira seu e-mail e sua senha.';
    } else {
        $email = strtolower(trim($emailRecebido));
        $model = new Usuario();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        if (strlen($email) > 150 || strlen($senhaRecebida) > 4096) {
            $erro = 'E-mail ou senha incorretos.';
        } elseif ($model->tentativaLoginBloqueada($email, $ip)) {
            $erro = 'Muitas tentativas. Aguarde 15 minutos e tente novamente.';
        } else {
            $registro = $model->buscarParaLogin($email);

            if (
                $registro
                && in_array($registro['tipo'], ['admin', 'user'], true)
                && password_verify($senhaRecebida, $registro['senha'])
            ) {
                $model->limparFalhasLogin($email, $ip);

                if (password_needs_rehash($registro['senha'], PASSWORD_DEFAULT)) {
                    $model->atualizarHashSenha(
                        (int) $registro['id'],
                        password_hash($senhaRecebida, PASSWORD_DEFAULT)
                    );
                }

                session_regenerate_id(true);
                $_SESSION['csrf'] = bin2hex(random_bytes(32));
                $_SESSION['ultima_atividade'] = time();
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

            $model->registrarFalhaLogin($email, $ip);
            $erro = 'E-mail ou senha incorretos.';
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
    <title>Entrar — Souls Store</title>
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
            <span class="sobretitulo">BEM-VINDO DE VOLTA</span>
            <h1>Seu próximo<br>jogo está <span>perto.</span></h1>
            <p>Entre para continuar sua jornada pelo acervo dos sem-luz.</p>
        </div>
        <div class="auth-lateral__arte" aria-hidden="true"><span>✦</span></div>
        <span class="auth-lateral__rodape">SOULS STORE <i>·</i> ACERVO DOS SEM-LUZ</span>
    </section>

    <section class="auth-conteudo">
        <a class="auth-voltar" href="<?= e(urlApp('index.php')) ?>">← Voltar à loja</a>
        <div class="auth-formulario-wrap">
            <span class="sobretitulo">SUA CONTA SOULS STORE</span>
            <h2>Entrar</h2>
            <p class="auth-intro">Acesse sua conta para continuar.</p>

            <?php if ($avisoLogin !== ''): ?>
                <div class="mensagem-info" role="status"><?= e($avisoLogin) ?></div>
            <?php endif; ?>

            <?php if ($erro !== ''): ?>
                <div class="mensagem-erro" role="alert"><?= e($erro) ?></div>
            <?php endif; ?>

            <form class="formulario-auth" method="post" action="<?= e(urlApp('login.php')) ?>">
                <input type="hidden" name="csrf" value="<?= e(tokenCsrf()) ?>">
                <label for="email">E-mail</label>
                <input id="email" type="email" name="email" maxlength="150" autocomplete="email" required value="<?= e(is_string($_POST['email'] ?? null) ? $_POST['email'] : '') ?>">

                <div class="campo-senha-linha"><label for="senha">Senha</label><a href="<?= e(urlApp('registrar.php')) ?>">Criar uma conta</a></div>
                <input id="senha" type="password" name="senha" autocomplete="current-password" required>

                <button class="botao-principal botao-principal--largo" type="submit">Continuar a jornada <span aria-hidden="true">→</span></button>
            </form>
            <p class="auth-seguranca"><span aria-hidden="true">◈</span> Senha protegida e acesso monitorado contra tentativas repetidas.</p>
        </div>
    </section>
</main>
</body>
</html>
