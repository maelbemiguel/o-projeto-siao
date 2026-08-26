# Sião Cartórios — Sistema de Gestão Cartorial

API RESTful + SPA Vue.js para gestão de cartórios, imóveis, proprietários e usuários, desenvolvida como resposta ao Desafio Técnico Sião.

---

## Tecnologias

| Camada    | Stack                                                        |
|-----------|--------------------------------------------------------------|
| Backend   | PHP 8.4, Laravel 13, Laravel Sanctum 4                       |
| Frontend  | Vue 3, Vue Router 4, Axios, Tailwind CSS 4, Vite 8           |
| Banco     | MySQL 8.4                                                    |
| Testes    | Pest 5 (SQLite in-memory)                                    |
| Docs API  | Swagger / OpenAPI 3 (L5-Swagger 11)                          |
| Docker    | PHP-FPM 8.4, Nginx 1.27, MySQL 8.4                           |
| CI        | GitHub Actions                                               |

---

## Funcionalidades

- Autenticação via token Bearer (Sanctum)
- CRUD completo: Cartórios, Imóveis, Proprietários, Usuários
- Soft Delete em todas as entidades
- Módulo de Relatórios (resumo geral, por cartório, por status, filtros avançados)
- Documentação interativa Swagger em `/api/documentation`
- SPA com login, dashboard e páginas de gestão para cada entidade

---

## Sobre Usuários e Administradores

O sistema **não possui diferenciação de perfil de acesso** entre usuários. O usuário `admin@siao.com.br` criado pelo seed não tem nenhum papel ou permissão especial, é apenas uma convenção de nomenclatura para facilitar os testes. Todos os usuários autenticados têm exatamente o mesmo nível de acesso à API.

---

## Integração Contínua (CI)

O projeto usa **GitHub Actions** para rodar a suíte de testes automaticamente a cada `push` e `pull_request` para qualquer branch.

O workflow (`.github/workflows/tests.yml`) executa os seguintes passos:

1. Checkout do código
2. Configura PHP 8.4 com as extensões necessárias (`mbstring`, `pdo_sqlite`, `sqlite3`, `xml`)
3. Instala dependências Composer
4. Prepara o ambiente (copia `.env.example` e gera a `APP_KEY`)
5. Roda o suite de testes com `php artisan test`

Os testes rodam contra **SQLite in-memory** — sem necessidade de banco de dados externo no CI. A configuração fica em `phpunit.xml`.

---

## Docker em Testes (docker-test)

Para validar o ambiente Docker antes de subir para produção, é possível reproduzir o mesmo fluxo do CI localmente usando Docker. O `docker-compose.yml` já inclui o serviço `app` com todas as variáveis de ambiente necessárias. O fluxo equivalente ao CI seria:

```bash
# Sobe os containers
docker compose up -d --build

# Roda o suite de testes dentro do container (SQLite in-memory, mesmo do CI)
docker compose exec app php artisan test
```

Isso garante que o ambiente Docker está saudável e que os testes passam no mesmo contexto em que a aplicação vai rodar.

---

## Configuração e Execução — Ambiente Local (sem Docker)

### Pré-requisitos

- **PHP 8.4+** com extensões: `pdo_mysql`, `mbstring`, `zip`, `bcmath`, `intl`, `gd`, `pcntl`
- **Composer 2**
- **Node.js 20+** e **npm**
- **MySQL 8.4+**

### 1. Clone o repositório

```bash
git clone <url-do-repositorio>
cd siao-cartorios
```

### 2. Instale as dependências

```bash
composer install
npm install
```

### 3. Configure o ambiente

```bash
cp .env.example .env
php artisan key:generate
```

Edite o `.env` com suas credenciais MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_sistema
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

### 4. Execute as migrations e popule o banco

```bash
php artisan migrate
php artisan db:seed
```

O seed cria 3 cartórios, 4 usuários, 6 proprietários e 9 imóveis de exemplo.

**Usuários criados pelo seed:**

| E-mail                       | Senha    | Observação                    |
|------------------------------|----------|-------------------------------|
| admin@siao.com.br            | password | Convencional — sem privilégio especial |
| mariana.costa@siao.test      | password | Responsável pelo cartório SP  |
| rafael.nunes@siao.test       | password | Responsável pelo cartório RJ  |
| beatriz.almeida@siao.test    | password | Responsável pelo cartório MG  |

> Todos os usuários têm o mesmo nível de acesso. O prefixo "admin" é apenas convencional.

### 5. Compile os assets do frontend

Para uso simples (gera os assets uma vez e encerra):

```bash
npm run build
```

Para desenvolvimento com HMR (Vite em modo watch — rodar em paralelo com `php artisan serve`):

```bash
npm run dev
```

### 6. Inicie o servidor

```bash
php artisan serve
```

Acesse: **http://localhost:8000**

---

## Configuração e Execução — Docker

### Pré-requisitos

- **Docker Desktop** (Windows/macOS) ou **Docker Engine + Compose** (Linux)

### 1. Suba os containers

```bash
docker compose up -d --build
```

Isso inicia três serviços:

| Container     | Descrição                     | Porta externa |
|---------------|-------------------------------|---------------|
| `siao_app`    | PHP-FPM 8.4                   | —             |
| `siao_nginx`  | Nginx 1.27 (proxy reverso)    | **8080**      |
| `siao_db`     | MySQL 8.4                     | **3307**      |

### 2. Execute as migrations e popule o banco

Aguarde o healthcheck do MySQL passar (alguns segundos) e então:

```bash
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
```

### 3. Acesse a aplicação

| Serviço        | URL                                         |
|----------------|---------------------------------------------|
| Aplicação      | http://localhost:8080                       |
| Swagger UI     | http://localhost:8080/api/documentation     |
| MySQL (externo)| `localhost:3307` — banco `db_sistema`       |

**Credenciais MySQL (Docker):**

| Campo   | Valor          |
|---------|----------------|
| Host    | localhost      |
| Porta   | 3307           |
| Banco   | db_sistema     |
| Usuário | siao           |
| Senha   | siao_password  |

### 4. Rodar os testes dentro do Docker

```bash
docker compose exec app php artisan test
```

Os testes usam SQLite in-memory e não afetam o banco MySQL.

### Parar os containers

```bash
docker compose down
# Para remover também o volume do banco:
docker compose down -v
```

---

## Documentação da API (Swagger)

Após iniciar a aplicação, acesse:

- **Local:** http://localhost:8000/api/documentation
- **Docker:** http://localhost:8080/api/documentation

Para forçar a regeneração da documentação:

```bash
php artisan l5-swagger:generate
```

### Autenticação no Swagger

1. Chame `POST /api/auth/login` com e-mail e senha
2. Copie o `token` da resposta
3. Clique em **Authorize** (canto superior direito) e cole o token no campo Bearer
4. Todos os endpoints protegidos ficam disponíveis

ps: ficar atento se rodar a endpoint de logout, pois vai precisar fazer a autenticação novamente...

---

## Endpoints da API

Todos os endpoints abaixo (exceto `/api/auth/login`) exigem autenticação Bearer.

### Autenticação

| Método | Endpoint         | Descrição                        | Auth |
|--------|------------------|----------------------------------|------|
| POST   | /api/auth/login  | Login — retorna token Bearer     | ✗    |
| POST   | /api/auth/logout | Logout — revoga o token atual    | ✓    |
| GET    | /api/auth/me     | Dados do usuário autenticado     | ✓    |

### Cartórios `/api/cartorios`

| Método | Endpoint               | Descrição                          |
|--------|------------------------|------------------------------------|
| GET    | /api/cartorios         | Lista paginada (busca e filtros)   |
| POST   | /api/cartorios         | Criar cartório                     |
| GET    | /api/cartorios/{id}    | Exibir cartório                    |
| PUT    | /api/cartorios/{id}    | Atualizar cartório                 |
| DELETE | /api/cartorios/{id}    | Remover (soft delete)              |

### Imóveis `/api/imoveis`

| Método | Endpoint             | Descrição                                   |
|--------|----------------------|---------------------------------------------|
| GET    | /api/imoveis         | Lista paginada (busca, status, cartório)    |
| POST   | /api/imoveis         | Criar imóvel                                |
| GET    | /api/imoveis/{id}    | Exibir imóvel                               |
| PUT    | /api/imoveis/{id}    | Atualizar imóvel                            |
| DELETE | /api/imoveis/{id}    | Remover (soft delete)                       |

### Proprietários `/api/proprietarios`

| Método | Endpoint                    | Descrição                        |
|--------|-----------------------------|----------------------------------|
| GET    | /api/proprietarios          | Lista paginada                   |
| GET    | /api/proprietarios/busca    | Busca rápida por nome/CPF        |
| POST   | /api/proprietarios          | Criar proprietário               |
| GET    | /api/proprietarios/{id}     | Exibir proprietário              |
| PUT    | /api/proprietarios/{id}     | Atualizar proprietário           |
| DELETE | /api/proprietarios/{id}     | Remover (soft delete)            |

### Usuários `/api/usuarios`

| Método | Endpoint              | Descrição                          |
|--------|-----------------------|------------------------------------|
| GET    | /api/usuarios         | Lista paginada (busca, cartório)   |
| POST   | /api/usuarios         | Criar usuário                      |
| GET    | /api/usuarios/{id}    | Exibir usuário                     |
| PUT    | /api/usuarios/{id}    | Atualizar usuário                  |
| DELETE | /api/usuarios/{id}    | Remover (soft delete)              |

### Relatórios `/api/relatorios`

| Método | Endpoint                              | Descrição                          |
|--------|---------------------------------------|------------------------------------|
| GET    | /api/relatorios/resumo                | Totais gerais do sistema           |
| GET    | /api/relatorios/imoveis-por-cartorio  | Imóveis agrupados por cartório     |
| GET    | /api/relatorios/imoveis-por-status    | Imóveis agrupados por status       |
| GET    | /api/relatorios/usuarios-por-cartorio | Usuários agrupados por cartório    |
| GET    | /api/relatorios/imoveis               | Relatório detalhado com filtros    |

---

## Estrutura do Projeto

```
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/        # AuthController, CartorioController, ...
│   │   └── Requests/               # FormRequests de validação
│   └── Models/                     # User, Cartorio, Imovel, Proprietario
├── database/
│   ├── migrations/                 # Migrations do domínio
│   ├── factories/                  # Factories para testes
│   └── seeders/                    # DatabaseSeeder com dados de exemplo
├── resources/
│   ├── css/app.css                 # Estilos globais (Tailwind CSS 4)
│   └── js/
│       ├── app.js                  # Entry point Vue
│       ├── App.vue                 # Componente raiz
│       ├── api.js                  # Axios com interceptors
│       ├── router.js               # Vue Router 4
│       ├── actions/                # Wayfinder — funções tipadas para rotas
│       ├── composables/            # useAuth, useNotify
│       ├── layouts/                # AppLayout (sidebar)
│       ├── components/             # DataTable, Pagination, ModalDialog, ...
│       ├── lib/                    # Utilitários compartilhados
│       └── pages/                  # LoginPage, DashboardPage, CartoriosPage, ...
├── routes/
│   ├── api.php                     # Rotas da API (protegidas por Sanctum)
│   └── web.php                     # Rota raiz → SPA
├── tests/
│   ├── Feature/                    # Testes de integração (Pest)
│   └── Unit/                       # Testes unitários (Pest)
├── docker/
│   ├── nginx/default.conf          # Configuração Nginx
│   └── php/local.ini               # Configuração PHP (memory_limit, timezone, OPcache)
├── .github/workflows/tests.yml     # Pipeline CI GitHub Actions
├── docker-compose.yml
├── Dockerfile
├── .dockerignore
└── phpunit.xml
```

---

## Variáveis de Ambiente Relevantes

```env
APP_NAME="Sião Cartórios"
APP_URL=http://localhost:8000

APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_sistema
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=file

SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173,localhost:8000,127.0.0.1,127.0.0.1:8000

L5_SWAGGER_GENERATE_ALWAYS=true
L5_SWAGGER_UI_DOC_EXPANSION=list
L5_SWAGGER_UI_PERSIST_AUTHORIZATION=true
L5_SWAGGER_CONST_HOST=http://localhost:8000
```

---

## Comandos Úteis

```bash
# Migrations
php artisan migrate
php artisan migrate:status
php artisan migrate:fresh --seed   # Recria tudo com seed

# Seed
php artisan db:seed

# Gerar documentação Swagger
php artisan l5-swagger:generate

# Testes
php artisan test
php artisan test --compact
php artisan test --filter=NomeDoTeste

# Limpar caches
php artisan optimize:clear

# Build do frontend
npm run build    # produção
npm run dev      # modo watch

# Logs em tempo real (Pail)
php artisan pail
```
