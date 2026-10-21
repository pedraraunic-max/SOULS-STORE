<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');

    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $https,
        'samesite' => 'Lax',
        'path' => '/',
    ]);

    session_start();
}

function e($valor)
{
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function inicial($texto)
{
    $correspondencia = [];

    return preg_match('/^./us', (string) $texto, $correspondencia)
        ? $correspondencia[0]
        : '?';
}

function tokenCsrf()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function usuarioAutenticado()
{
    return isset($_SESSION['usuario']) && is_array($_SESSION['usuario'])
        ? $_SESSION['usuario']
        : null;
}

function usuarioEhAdmin()
{
    $usuario = usuarioAutenticado();

    return $usuario !== null && ($usuario['tipo'] ?? '') === 'admin';
}

function urlApp($caminho = '')
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
    $posicaoAdmin = strpos($script, '/admin/');

    if ($posicaoAdmin !== false) {
        $base = substr($script, 0, $posicaoAdmin);
    } else {
        $base = rtrim(dirname($script), '/');
    }

    return $base . '/' . ltrim($caminho, '/');
}

function exigirLogin()
{
    if (usuarioAutenticado() === null) {
        redirecionar(urlApp('login.php'));
    }
}

function exigirAdmin()
{
    exigirLogin();

    require_once __DIR__ . '/../models/usuarios.php';

    $dadosSessao = usuarioAutenticado();
    $usuarioAtual = (new Usuario())->buscarPorId(
        (int) ($dadosSessao['id'] ?? 0)
    );

    if (!$usuarioAtual) {
        unset($_SESSION['usuario']);
    } else {
        $_SESSION['usuario']['nome'] = $usuarioAtual['nome'];
        $_SESSION['usuario']['email'] = $usuarioAtual['email'];
        $_SESSION['usuario']['tipo'] = $usuarioAtual['tipo'];
    }

    if (!usuarioEhAdmin()) {
        http_response_code(403);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html lang="pt-BR"><meta charset="UTF-8">';
        echo '<title>Acesso restrito</title><body>';
        echo '<h1>Área exclusiva para administradores</h1>';
        echo '<p>Seu perfil não tem permissão para acessar esta página.</p>';
        echo '<a href="' . e(urlApp('index.php')) . '">Voltar à loja</a>';
        echo '</body></html>';
        exit;
    }
}

function exigirPostValido()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Método não permitido.');
    }

    $recebido = $_POST['csrf'] ?? '';
    $esperado = $_SESSION['csrf'] ?? '';

    if (
        !is_string($recebido)
        || $esperado === ''
        || !hash_equals($esperado, $recebido)
    ) {
        http_response_code(403);
        exit('Formulário inválido. Volte, atualize a página e tente novamente.');
    }
}

function redirecionar($destino)
{
    header('Location: ' . $destino);
    exit;
}
