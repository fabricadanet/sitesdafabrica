# Sites da Fábrica — Projeto SaaS de criação de sites

Este repositório contém uma aplicação PHP para criação e gerenciamento de sites (SaaS). O projeto usa roteamento manual, SQLite por padrão e um conjunto de migrations para criar o esquema do banco.

**Resumo rápido**:
- **Tecnologia:** PHP (CLI + servidor embutido), PDO (SQLite)
- **Ponto de entrada HTTP:** `public/index.php`
- **Migrations:** `database/migrations/`

**Pré-requisitos**
- **PHP** >= 8.0 com extensões: `pdo`, `pdo_sqlite`, `sqlite3`, `mbstring`, `xml` (pelo menos as usadas no container de desenvolvimento).
- **Composer** para instalar dependências PHP.

**Instalação local (rápido)**
1. Instale dependências do sistema (Ubuntu/debian):

```bash
sudo apt update
sudo apt install -y php php-cli php-sqlite3 php-xml php-mbstring unzip curl
```

2. Na raiz do projeto, instale dependências PHP:

```bash
composer install
```

3. Garanta permissão para a pasta do banco de dados:

```bash
mkdir -p database
chmod -R 0777 database
```

4. Execute as migrations para criar as tabelas (usa `config/database.php` — SQLite por padrão):

```bash
php database/migrate.php migrate
```

5. (Opcional) Popular dados iniciais:

```bash
php database/migrate.php seed
```

**Rodando o servidor de desenvolvimento**

Use o servidor embutido do PHP apontando para a pasta `public`:

```bash
php -S 0.0.0.0:8000 -t public
```

Abra no navegador: `http://localhost:8000/` (rotas descritas abaixo).

**Rotas principais (visão geral)**
- `/` ou `/login` — tela de login (GET/POST)
- `/register` — cadastro (GET/POST)
- `/editor` — editor visual
- `/projects` — lista de projetos (página HTML)
- `/projects/*` — várias rotas API para salvar/obter/excluir projetos (veja `config/routes.php`)
- `/admin` e `/admin/*` — painel administrativo
- `/deploy/*` — endpoints de deploy e gestão de domínios

As rotas completas estão implementadas em `config/routes.php` e os controladores em `app/Controllers/`.

**Banco de dados**
- Arquivo SQLite padrão: `database/app.db` (criado automaticamente por `config/database.php`).
- O projeto inclui um utilitário de migrations: `database/migrate.php` e a classe `Database/Migrator.php`.
- Para inspecionar o banco, você pode usar o cliente `sqlite3`:

```bash
sqlite3 database/app.db 
sqlite> .tables
```

**Estrutura do projeto (resumo)**
- `app/Controllers/` — controladores HTTP
- `app/Models/` — modelos simples
- `app/Views/` — views (HTML/PHP)
- `config/` — rotas e configuração do banco
- `database/` — migrations, migrator e scripts CLI
- `public/` — arquivos públicos, ponto de entrada `index.php` e assets

**Como testar**
- Verifique se o servidor rodando responde em `http://localhost:8000/`.
- Teste endpoints JSON usando `curl` ou Postman. Exemplo: listar templates

```bash
curl -s http://localhost:8000/projects/templates
```

- Teste login/registro via navegador e verifique criação de registros em `database/app.db`.

**Notas de desenvolvimento**
- O autoload PSR-4 está definido em `composer.json` (namespace `App\\` para `app/`).
- O projeto foi codificado com um roteador manual em `config/routes.php` — ao adicionar rotas, siga o padrão de `switch` já implementado.
- `database/Migrator.php` suporta SQLite e MySQL (depende do driver PDO fornecido).

**Problemas comuns**
- Permissões em `database/` — ajuste `chmod` se ocorrer erro ao gravar `app.db`.
- Falta de extensões PHP — instale `php-sqlite3` e `php-mbstring` quando necessário.

**Próximos passos recomendados**
- Executar `composer install` e `php database/migrate.php migrate` localmente ou no container.
- (Opcional) Implementar testes automatizados e um script `Makefile`/`composer script` para simplificar comandos comuns.

**Autor / Contato**
- Fábrica da Net — repositório `fabricadanet/sitesdafabrica`

---
