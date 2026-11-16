# Souls Store

Loja acadêmica em PHP 8 para jogos soulslike, com autenticação, painel administrativo, cadastro de clientes, categorias, jogos e pedidos simulados.

## Requisitos técnicos

- PHP 8.0 ou superior;
- MySQL ou MariaDB;
- PDO com `pdo_mysql` habilitado;
- Modelos orientados a objetos em `admin/models/`;
- Controllers em `admin/controllers/` e páginas da loja/painel em `admin/views/`;
- Banco relacional com chaves estrangeiras entre jogos/categorias e pedidos/clientes/jogos.

## Executar com XAMPP

1. Copie a pasta para `htdocs/loja_games`.
2. Inicie Apache e MySQL.
3. No phpMyAdmin, crie o banco `loja_games`.
4. Para uma instalação nova, importe [`database/schema.sql`](database/schema.sql). Esse arquivo cria as tabelas relacionais e inclui os seis jogos soulslike.
5. Abra `http://localhost/loja_games/`.

Sem variáveis de ambiente, a conexão usa `localhost:3306`, banco `loja_games`, usuário `root` e senha vazia. Se sua instalação for diferente, configure `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASSWORD`.

### Atualizar um banco de uma versão anterior

Faça uma exportação de segurança no phpMyAdmin antes de alterar o banco. Em uma instalação antiga que ainda usa `compras` e `games.categoria` como texto, importe [`database/migrations/academico_crud.sql`](database/migrations/academico_crud.sql) uma vez. A migração cria categorias e pedidos relacionais, adiciona telefone aos usuários e copia compras antigas para pedidos, mantendo a tabela antiga como cópia.

O arquivo [`database/migrations/souls_store.sql`](database/migrations/souls_store.sql) é legado para substituir o catálogo de uma versão muito antiga; ele apaga o catálogo existente e não deve ser executado depois da migração acadêmica. [`database/migrations/biblioteca.sql`](database/migrations/biblioteca.sql) também é legado para a tabela `compras`; instalações novas usam `pedidos` desde o `schema.sql`.

## Funcionalidades

- Cadastro de clientes com nome, e-mail, telefone e senha;
- login, logout, perfis de cliente/administrador, sessão protegida, CSRF e senhas com `password_hash()`;
- CRUD administrativo de usuários, clientes, jogos, categorias e pedidos;
- busca de jogos, filtros por coleção/categoria, lista de desejos e detalhes interativos;
- capas/imagens de jogos e cadastro de estoque;
- compra acadêmica sem pagamento real, com biblioteca por cliente;
- relatório/resumo de pedidos no painel.

## Primeiro administrador

1. Crie uma conta pela tela **Criar conta**.
2. No phpMyAdmin, selecione `loja_games` e execute, substituindo o e-mail:

```sql
UPDATE usuarios SET tipo = 'admin' WHERE email = 'seu-email@exemplo.com';
```

3. Saia e entre novamente para acessar o painel administrativo.

Mais detalhes em [`CONFIGURAR_ACESSOS.md`](CONFIGURAR_ACESSOS.md).

## GitHub

O projeto é publicado em [pedraraunic-max/SOULS-STORE](https://github.com/pedraraunic-max/SOULS-STORE). Lembre-se de enviar commits locais ao repositório para que as mudanças mais recentes apareçam para outras pessoas.
