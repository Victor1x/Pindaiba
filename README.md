# Pindaíba — API

API em Laravel 12 com autenticação via Sanctum.

## Requisitos

- PHP 8.2+ (CI usa 8.5)
- Composer 2
- Node.js 20+ e npm
- SQLite (padrão) ou outro banco suportado pelo Laravel

## Instalação

```bash
git clone https://github.com/Victor1x/Pindaiba.git
cd Pindaiba

composer install
npm install            # instala o Husky e ativa os git hooks (script "prepare")

cp .env.example .env
php artisan key:generate

touch database/database.sqlite   # se usar SQLite
php artisan migrate
php artisan storage:link
```

> Para usar outro banco, ajuste `DB_CONNECTION`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD` no `.env` antes do `migrate`.

### IDE Helper (opcional)

```bash
php artisan ide-helper:generate
php artisan ide-helper:meta
php artisan ide-helper:models -W
```

Os arquivos gerados (`_ide_helper.php`, `.phpstorm.meta.php`) estão no `.gitignore`.

## Rodando

```bash
php artisan serve
php artisan route:list
```

## Arquitetura

Arquitetura em camadas, cada camada só conversa com a de baixo:

```
HTTP (Controller, Request, Resource)
        ↓
Aplicação (Actions = casos de uso)
        ↓
Domínio (Services, Models, Enums, Policies, Exceptions)
        ↓
Infraestrutura (Banco, Filas, APIs externas)
```

```
app/
├── Actions/              # Casos de uso (1 classe = 1 ação)       → Actions/{Entidade}/{Verbo}{Entidade}Action
├── DTOs/                 # Dados tipados (readonly)               → DTOs/{Entidade}/{Entidade}Data
├── Enums/                # Status, papéis, tipos
├── Events/  Listeners/   # Efeitos colaterais
├── Exceptions/Domain/    # Violações de regra de negócio          → {Problema}Exception
├── Http/
│   ├── Controllers/Api/V1/   # Controllers magros (1–5 linhas por método)
│   ├── Middleware/           # ForceJsonResponse
│   ├── Requests/             # Validação + authorize()           → {Verbo}{Entidade}Request
│   └── Resources/            # Saída JSON (nunca retornar Model)   → {Entidade}Resource
├── Integrations/         # Clientes de APIs externas
├── Jobs/                 # Tarefas assíncronas
├── Models/  Policies/  Providers/
├── Repositories/         # (Opcional) consultas complexas
└── Services/             # Regras reutilizáveis                   → {Regra}Service
```

Regras principais: validação no FormRequest, regra de negócio na Action, nada de `$request` fora da camada HTTP (converta para DTO), sem strings mágicas (use Enums).
O `tests/ArchTest.php` (Pest Arch) quebra o build se essas regras forem violadas.

### Rotas

Sem prefixo `/api`, versionadas em `/v1`, todas atrás de `auth:sanctum`:

| Método | Rota | Descrição |
| --- | --- | --- |
| GET | `/up` | Health check |
| GET | `/v1/user` | Usuário autenticado (`UserResource`) |

Toda requisição de API recebe `Accept: application/json` (middleware `ForceJsonResponse`), então erros sempre voltam em JSON.

## Qualidade de código

| Comando | O que faz |
| --- | --- |
| `composer test` / `php artisan test --parallel` | Testes com Pest + Paratest |
| `composer analyse` | Larastan (PHPStan, nível 8) |
| `composer lint` | Pint em modo verificação |
| `composer lint:fix` | Formata o código com Pint |

## CORS

As origens permitidas vêm do `.env`:

```dotenv
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://localhost:3000
CORS_SUPPORTS_CREDENTIALS=true
```

## Git hooks (Husky)

Os hooks são ativados pelo `npm install`. Se não estiverem ativos depois de clonar, rode `npx husky`.

- **pre-commit** — roda PHPStan e os testes; formata com Pint os arquivos PHP do stage.
- **commit-msg** — exige branch no padrão `<ID>-<número>-descricao` (ex.: `API-14-minha-feature`), prefixa a mensagem com o ID (`API-14: mensagem`) e adiciona `Closes #14`.

## Fluxo de branches

- `main` — produção
- `develop` — integração
- Branches de trabalho saem da `develop` no padrão `API-<issue>-descricao`

## CI

O workflow `.github/workflows/laravel.yml` roda Pest, Larastan e `pint --test` em todo Pull Request para `main` ou `develop`.
