# Configurar acessos da GameStock

O sistema usa a tabela `usuarios` já existente. As contas têm o campo `tipo`, com os valores `user` e `admin`.

## Criar a primeira conta de administrador

1. Inicie o PHP e o MySQL e confira a conexão em `admin/config/conexao.php`.
2. Acesse `registrar.php` e crie uma conta normalmente.
3. No phpMyAdmin ou no MySQL, promova essa conta a administradora:

```sql
UPDATE usuarios
SET tipo = 'admin'
WHERE email = 'seu-email@exemplo.com';
```

4. Saia da conta e entre novamente em `login.php`. A conta ADM será direcionada ao painel e verá o link **Painel ADM** na loja.

Novos cadastros públicos sempre recebem o perfil `user`. Esse perfil navega pelo catálogo; somente `admin` pode abrir, alterar ou excluir dados do painel. O painel também impede excluir a própria conta ADM ou remover o último administrador.

## Endereços

- Vitrine: `index.php`
- Entrar: `login.php`
- Criar conta: `registrar.php`
- Administração de jogos: `admin/views/ver_games.php`
- Administração de usuários: `admin/views/ver_usuarios.php`

O projeto recebido não inclui um arquivo para criar o banco e as tabelas. A instalação precisa ter as tabelas `games` e `usuarios` preparadas antes de abrir as páginas.
