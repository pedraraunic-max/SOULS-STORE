# SOULS STORE

Loja de games em PHP com catálogo, cadastro e login de usuários e área administrativa. O banco usa MySQL/MariaDB.

## Rodar localmente

1. Crie o banco `loja_games` no MySQL.
2. Importe [`database/schema.sql`](database/schema.sql).
3. Se necessário, ajuste as variáveis no ambiente. Sem elas, a aplicação usa `localhost:3306`, banco `loja_games`, usuário `root` e senha vazia.
4. Abra o projeto pelo Apache do XAMPP ou rode `php -S 127.0.0.1:8000 -t .` na pasta do projeto, com a extensão PDO MySQL habilitada.

## Publicar no Render

O projeto inclui um `Dockerfile` para o serviço PHP/Apache. O Apache usa a porta definida por `PORT` (padrão `10000` no Render).

1. Crie um serviço MySQL privado com armazenamento persistente e escolha `loja_games` como banco. O Render oferece MySQL como serviço personalizado com disco; confira os custos e o tamanho do disco antes de criar.
2. Crie um **Web Service** a partir deste repositório e selecione o runtime **Docker**.
3. Nas variáveis de ambiente do serviço PHP, configure `DB_HOST`, `DB_PORT` (`3306`), `DB_NAME`, `DB_USER` e `DB_PASSWORD`, usando os dados internos do serviço MySQL. Guarde a senha somente nas variáveis do painel do Render.
4. Importe `database/schema.sql` no banco de produção. O arquivo cria as tabelas para uma instalação nova; se já houver dados, faça backup e confira a estrutura antes de importar.
5. Cadastre uma conta pela tela de registro e transforme-a em administradora seguindo [`CONFIGURAR_ACESSOS.md`](CONFIGURAR_ACESSOS.md). Depois, cadastre os jogos no painel.

O catálogo começa vazio quando o banco é novo. As imagens cadastradas no painel devem estar disponíveis por uma URL acessível pelo navegador.

## Acessos

- Vitrine: `/`
- Entrar: `/login.php`
- Criar conta: `/registrar.php`
- Painel de jogos e usuários: links **Painel ADM** após entrar com uma conta administradora.
