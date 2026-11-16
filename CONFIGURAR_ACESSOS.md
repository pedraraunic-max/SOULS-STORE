# Configurar acessos da Souls Store

As contas ficam na tabela `usuarios`, com os perfis `user` e `admin`. O cadastro público sempre cria um usuário comum; somente administradores podem abrir o painel.

## Criar a primeira conta administradora

1. Importe `database/schema.sql` em uma instalação nova ou execute `database/migrations/souls_store.sql` em uma base existente.
2. Acesse `registrar.php` e crie uma conta normalmente.
3. No phpMyAdmin, selecione o banco `loja_games`, abra a aba **SQL** e promova a conta:

```sql
UPDATE usuarios
SET tipo = 'admin'
WHERE email = 'seu-email@exemplo.com';
```

4. Saia da conta e entre novamente. O administrador será direcionado ao painel.

## Proteções de acesso

- As senhas são armazenadas com hash e nunca aparecem na lista de usuários.
- O login bloqueia novas tentativas por 15 minutos após cinco falhas para o mesmo e-mail e endereço de rede.
- Sessões expiram após 30 minutos sem atividade, usam cookies `HttpOnly` e regeneram o identificador ao autenticar.
- Formulários que alteram dados exigem token CSRF.
- O painel confere no banco o perfil atual antes de permitir ações administrativas.

Em produção, configure um `APP_KEY` aleatório e habilite `APP_FORCE_SECURE_COOKIE=1` quando o site usar HTTPS.

## Endereços

- Vitrine: `index.php`
- Entrar: `login.php`
- Criar conta: `registrar.php`
- Administração de jogos: `admin/views/ver_games.php`
- Administração de usuários: `admin/views/ver_usuarios.php`
