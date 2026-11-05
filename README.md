# Asset Register API

REST API for registering **contracting parties**, **devices**, **contracts** and the devices placed on contracts.
Built with **Laravel 13**, **PHP 8.5** and **MySQL 8.4**, structured as **DDD** (an `Api` layer, a `Modules` layer
and a shared `Core`), following PSR-4 and PSR-12. There is no UI; the API is documented with OpenAPI.

* CRUD for parties, devices and contracts, plus placing devices on contracts and taking them off.
* A device type can be on many contracts (N:N); every placed unit has its own serial number.
* One unified JSON error format for every error.
* Every API request and response is logged to the database (secrets redacted).
* OpenAPI documentation behind HTTP Basic auth, a Postman collection, demo data and factories.

## Requirements

| To run with | You need |
|-------------|----------|
| **Docker** (recommended) | Docker with Compose v2 (e.g. Docker Desktop). Nothing else: PHP, MySQL and nginx run in containers. |
| **Local PHP** | PHP 8.5 with `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `curl`, `dom`, `fileinfo`; Composer 2; a MySQL 8 server (your own, or the one from this repository's compose file). |

Optional: `jq` for the examples below, Node.js (`npx`) to run the Postman collection from the command line.

## Setup

### Option A: Docker

```bash
git clone <repository-url> asset-register && cd asset-register
docker compose up -d --build
```

That is all. On the first start the app container creates the whole schema from the migrations. Check that it works:

```bash
curl http://localhost:8080/up                                  # health check -> 200
curl http://localhost:8080/api/v1/devices                      # empty list on a fresh install
```

Exactly three containers, nothing else:

| Service | Image                    | Role                                                        |
|---------|--------------------------|-------------------------------------------------------------|
| `web`   | nginx 1.27 (alpine)      | Public entry point, forwards every request to the app       |
| `app`   | PHP 8.5-FPM (alpine)     | The Laravel application (code and `vendor/` baked in)       |
| `db`    | MySQL 8.4                | Data, persisted in the `db-data` volume                     |

The app image is self-contained: Composer and git exist only in a build stage, the source is not mounted and
there is no `.env` inside. All configuration comes from the container environment (`docker-compose.yaml`).
**After changing the code run `docker compose up -d --build` again.**

| What          | URL                                          |
|---------------|----------------------------------------------|
| API           | http://localhost:8080/api/v1                 |
| This README   | http://localhost:8080/readme (public)        |
| Documentation | http://localhost:8080/docs (HTTP Basic auth) |

Documentation credentials are `DOCS_USERNAME` / `DOCS_PASSWORD` (compose defaults: `docs` / `change-me`).
**While either value is empty the documentation is locked for everyone.** This README is served publicly at `/readme`
(`/` redirects there), so it names these defaults: **set your own `DOCS_PASSWORD` before exposing the app.**

Useful commands:

```bash
docker compose ps                                                      # status of the three containers
docker compose logs -f app                                             # application log (stderr)
docker compose exec app php artisan test                               # run the test suite
docker compose exec app vendor/bin/pint --test                         # code style
docker compose exec app vendor/bin/phpstan analyse --memory-limit=1G   # static analysis
docker compose down                                                    # stop, keep the data
docker compose down -v                                                 # stop and delete the data (database volume)
```

### Option B: local PHP

Use this when you want to run `php artisan serve` and an IDE debugger on the host.

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Then point `.env` at a MySQL database. Either start only the database from this repository (published on
`127.0.0.1:3306`, and the `.env.example` defaults already match it):

```bash
docker compose up -d db
```

or use your own server and create a database and user for the app:

```sql
CREATE DATABASE asset_register CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'asset_register'@'%' IDENTIFIED BY 'secret';
GRANT ALL PRIVILEGES ON asset_register.* TO 'asset_register'@'%';
```

(and adjust `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` in `.env`). Finally:

```bash
php artisan migrate        # build the schema from the migrations
php artisan db:seed        # optional: demo data (see below)
php artisan serve          # http://127.0.0.1:8000
```

`composer setup` does the install, `.env`, key and migration steps in one go (it does not seed). Without Docker the
documentation credentials are `DOCS_USERNAME` / `DOCS_PASSWORD` from your `.env`.

### Demo data (seeder)

A fresh install is empty; the schema comes from the migrations only. To load demo data so you can list something
right away:

```bash
docker compose exec app php artisan db:seed --force     # Docker (the container runs in production mode, hence --force)
php artisan db:seed                                     # local PHP
SEED_DEMO_DATA=true docker compose up -d                # Docker: seed automatically when the app container starts
php artisan migrate:fresh --seed                        # local PHP: drop everything, rebuild, seed (deletes all data!)
```

`DemoDataSeeder` creates 4 contracting parties, 6 device types, 5 contracts and 16 placed units (for example the same
Verifone POS type on three contracts, each unit with its own serial number, and one expired and one open-ended
contract). It looks every record up by its natural key first (party name, manufacturer + model, contract number,
device + serial number), so running it again, or restarting with `SEED_DEMO_DATA=true`, never duplicates anything and
never touches existing data.

Random data for tests comes from the factories, see [Factories](#factories).

## Configuration

Docker reads these from the shell or from a `.env` next to `docker-compose.yaml` (compose uses it for substitution);
local PHP reads `.env`.

| Variable | Default | Meaning |
|----------|---------|---------|
| `APP_PORT` | `8080` | Host port of the API (Docker) |
| `DB_HOST_PORT` | `3306` | Host port of MySQL, bound to `127.0.0.1` only (Docker) |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | `asset_register` / `asset_register` / `secret` | Database and application user (Docker creates them) |
| `DB_ROOT_PASSWORD` | set in `docker-compose.yaml` | MySQL `root` password (Docker) |
| `DOCS_USERNAME` / `DOCS_PASSWORD` | `docs` / `change-me` | HTTP Basic credentials of `/docs`; empty = locked |
| `SEED_DEMO_DATA` | `false` | `true` seeds the demo data when the app container starts (Docker) |
| `APP_KEY` | generated on start | Laravel key; set it to keep it stable (the API does not use encryption) |
| `APP_URL` | `http://localhost:8080` | Base URL used in `Location` headers |
| `API_LOG_MAX_BODY_BYTES` | `65536` | Request/response bodies in the traffic log are cut at this size (local `.env` only) |
| `APP_FAKER_LOCALE` | `hu_HU` (`.env.example`) | Language of factory-generated data |

`APP_ENV=production` and `APP_DEBUG=false` are fixed in the compose file on purpose, so a developer's local `.env`
can never switch debug output on in the container. Other fixed container settings: logs go to stderr, cache is `file`,
sessions are `array`, queue is `sync`.

## Database

| Table | Content |
|-------|---------|
| `parties` | Contracting parties |
| `devices` | Device types (unique by manufacturer + model) |
| `contracts` | Contracts, each with a `party_id` |
| `contract_devices` | The N:N link: one row per placed unit, with its `serial_number` |
| `api_logs` | The traffic log |
| `migrations` | Laravel's migration bookkeeping |

The whole schema is built from the migrations in `database/migrations`:

```bash
php artisan migrate               # apply
php artisan migrate:status        # what has run
php artisan migrate:rollback      # undo the last batch
php artisan migrate:reset         # undo everything (every down() is implemented)
php artisan migrate:fresh         # drop all tables and migrate again (deletes all data!)
```

In Docker prefix the commands with `docker compose exec app` (add `--force` where Laravel asks for it).

### Connecting with DBeaver (or any SQL client)

MySQL is published on `127.0.0.1:3306` (localhost only, not reachable from the network).

| Setting  | Value                                        |
|----------|----------------------------------------------|
| Driver   | MySQL                                        |
| Host     | `127.0.0.1` (use this, not `localhost`)      |
| Port     | `3306` (`DB_HOST_PORT` changes it)           |
| Database | `asset_register`                             |
| Username | `asset_register`                             |
| Password | `secret`                                     |

If DBeaver reports *"Public Key Retrieval is not allowed"* (MySQL 8 uses `caching_sha2_password`), open the
connection's **Driver properties** and set `allowPublicKeyRetrieval` = `true` (and `useSSL` = `false`, as it
is a local development database). Administrative access: user `root`, password `DB_ROOT_PASSWORD` (see `docker-compose.yaml`).
If host port 3306 is already taken by a local MySQL, start with `DB_HOST_PORT=3307`.

## Domain model

```
Party 1 ──< Contract 1 ──< ContractDevice >── 1 Device (type)
                              serial_number
```

* A **party** (*szerződő fél*, the contracting party) has a name and an optional tax number (unique when set),
  address, e-mail and phone. One party can have many contracts.
* A **device** is a device *type* (e.g. Dell Latitude 5440), unique by manufacturer + model.
* A **contract** belongs to exactly one party and has a unique number, a period (`ends_on` optional) and notes.
* The N:N relation is the `contract_devices` table. Each row is one physical **unit**: it carries its own
  **serial number**. The same device type can therefore be on many contracts, and several times on one
  contract, always with different serial numbers.

Decisions where the brief left room (all enforced by the handlers *and* by database constraints):

* A physical unit (`device` + `serial_number`) can be on **one contract only** (`unique(device_id, serial_number)`).
  MySQL compares serial numbers case-insensitively, so `AAA` and `aaa` are the same unit.
* A device that is placed on a contract **cannot be deleted** (409 `device_in_use`, FK `RESTRICT`).
* A party that has contracts **cannot be deleted** (409 `party_in_use`, FK `RESTRICT`).
* Deleting a contract **releases** its devices (FK `CASCADE`).
* `PUT` is a full replacement; there is no `PATCH`.
* A device placed on a contract is identified by an `assignment_id`, returned in the contract's `devices`.

## API

Base path `/api/v1`. JSON in, JSON out (send `Content-Type: application/json`). Identifiers are UUIDv7, dates are
`YYYY-MM-DD`. Lists are paginated with `page` and `per_page` (1 to 100, default 15) and carry a `meta` object.

| Method | Path                                      | Description                                  |
|--------|-------------------------------------------|----------------------------------------------|
| GET    | `/parties`                                | List contracting parties                     |
| POST   | `/parties`                                | Create a party                               |
| GET    | `/parties/{party}`                        | Get a party                                  |
| PUT    | `/parties/{party}`                        | Replace a party                              |
| DELETE | `/parties/{party}`                        | Delete a party                               |
| GET    | `/devices`                                | List devices                                 |
| POST   | `/devices`                                | Create a device                              |
| GET    | `/devices/{device}`                       | Get a device                                 |
| PUT    | `/devices/{device}`                       | Replace a device                             |
| DELETE | `/devices/{device}`                       | Delete a device                              |
| GET    | `/contracts`                              | List contracts (with their party, **without** devices) |
| POST   | `/contracts`                              | Create a contract (`party_id` required)      |
| GET    | `/contracts/{contract}`                   | Get a contract **with** its devices          |
| PUT    | `/contracts/{contract}`                   | Replace contract data (devices are kept)     |
| DELETE | `/contracts/{contract}`                   | Delete a contract                            |
| POST   | `/contracts/{contract}/devices`           | Place a device on a contract (`device_id`, `serial_number`) |
| DELETE | `/contracts/{contract}/devices/{assignment}` | Take a device off a contract              |

### Example walkthrough

```bash
BASE=http://localhost:8080/api/v1

# 1. a contracting party, a device type and a contract
PARTY=$(curl -s -X POST $BASE/parties -H 'Content-Type: application/json' \
  -d '{"name":"Example Kft.","tax_number":"99999999-2-42","email":"info@example.com"}' | jq -r .data.id)
DEVICE=$(curl -s -X POST $BASE/devices -H 'Content-Type: application/json' \
  -d '{"name":"Latitude 5440","manufacturer":"Dell","model":"5440"}' | jq -r .data.id)
CONTRACT=$(curl -s -X POST $BASE/contracts -H 'Content-Type: application/json' \
  -d "{\"contract_number\":\"SZ-1\",\"party_id\":\"$PARTY\",\"starts_on\":\"2026-01-01\",\"ends_on\":\"2026-12-31\"}" | jq -r .data.id)

# 2. place a unit of the device on the contract (every unit has its own serial number)
curl -s -X POST $BASE/contracts/$CONTRACT/devices -H 'Content-Type: application/json' \
  -d "{\"device_id\":\"$DEVICE\",\"serial_number\":\"SN-0001\"}" | jq .data

# 3. the contract detail lists its devices, the contract list does not
curl -s $BASE/contracts/$CONTRACT | jq '.data.devices'
curl -s "$BASE/contracts?per_page=5" | jq '{meta, contracts: [.data[] | {contract_number, party: .party.name}]}'

# 4. the same unit again is refused with the unified error format (409)
curl -s -X POST $BASE/contracts/$CONTRACT/devices -H 'Content-Type: application/json' \
  -d "{\"device_id\":\"$DEVICE\",\"serial_number\":\"SN-0001\"}" | jq .error

# 5. take the unit off again (204), using the assignment_id from step 2
ASSIGNMENT=$(curl -s $BASE/contracts/$CONTRACT | jq -r '.data.devices[0].assignment_id')
curl -i -X DELETE $BASE/contracts/$CONTRACT/devices/$ASSIGNMENT
```

### Documentation and Postman

* **OpenAPI** (`docs/openapi.yaml`) is rendered as Swagger UI at `/docs` and served raw at `/docs/openapi.yaml`, both
  behind HTTP Basic auth: `curl -u docs:change-me http://localhost:8080/docs/openapi.yaml`. A test keeps the file in
  sync with the registered routes. The Swagger UI page loads its script from the jsDelivr CDN, so the browser needs
  internet access.
* **Postman:** `docs/postman/asset-register.postman_collection.json` contains a request (with tests) for every endpoint,
  the documentation and health routes, and examples of each error case. Import it into Postman and run it top to bottom
  in the Collection Runner: requests store `partyId`, `deviceId`, `contractId` and `assignmentId` in collection
  variables, and the cleanup folder removes what the run created (your own data is not touched). Variables to adjust:
  `baseUrl` (default `http://localhost:8080`), `docsUser`, `docsPassword`. From the command line:
  `npx newman run docs/postman/asset-register.postman_collection.json`.

### Unified error format

Every error under `/api` (validation, business rules, unknown route, wrong method, unexpected failure) has the
same shape; `code` is stable and machine readable, `details` appears when there are field level problems:

```json
{
  "error": {
    "code": "validation_failed",
    "message": "The given data was invalid.",
    "details": { "name": ["The name field is required."] },
    "request_id": "01a1a6c5-a4ea-7d9d-a2a1-a91123fd5749"
  }
}
```

| Status | Typical codes |
|--------|---------------|
| 404 | `not_found`, `party_not_found`, `device_not_found`, `contract_not_found`, `contract_device_not_found` |
| 409 | `device_already_exists`, `device_in_use`, `party_already_exists`, `party_in_use`, `contract_number_already_exists`, `serial_number_already_assigned`, `conflict` |
| 422 | `validation_failed`, `invalid_contract_period`, `invalid_value`, `device_not_found` / `party_not_found` (unknown `device_id` / `party_id` in a body) |
| 405 / 400 / 5xx | `method_not_allowed`, `bad_request`, `server_error` (never leaks internals) |

### Traffic log

Every `/api` request and its response is stored in the `api_logs` table: method, path, full URI, client IP,
user agent, headers, bodies, status, duration and a `request_id`. The id is also sent back as the
`X-Request-Id` header (a client supplied one is honoured) and appears in error bodies, so a client report can
be matched to its log row. Secrets (`Authorization`, `Cookie`, `password`, `token`, ...) are redacted and
bodies are capped (`API_LOG_MAX_BODY_BYTES`, see `config/api_log.php`). Logging happens after the response is
sent and can never fail a request. Only `/api` calls are logged, not `/docs`, `/readme` or `/up`.

```sql
SELECT method, path, response_status, duration_ms, request_id FROM api_logs ORDER BY id DESC LIMIT 20;
```

## Architecture

```
app/                                 PSR-4 namespace App\
├── Api/                             HTTP layer, one folder per module
│   ├── Party/     Controllers (one per action) · Requests · Resources · Routes/PartiesRoutes.php
│   ├── Device/    Controllers · Requests · Resources · Routes/DevicesRoutes.php
│   ├── Contract/  Controllers · Requests · Resources · Routes/ContractsRoutes.php
│   ├── ApiLog/    Middleware/LogApiTraffic
│   └── Docs/      Controllers · Middleware (Basic auth) · Routes · Views   (README page, Swagger UI)
├── Modules/                         business logic, no HTTP
│   ├── Party/     Exceptions · Handlers (Payloads, Results) · Models · Repositories
│   ├── Device/    Exceptions · Handlers (Payloads, Results) · Models · Repositories
│   ├── Contract/  Exceptions · Handlers (Payloads, Results) · Models · Repositories · ValueObjects
│   └── ApiLog/    Handlers · Models · Repositories · Support (PayloadSanitizer)
└── Core/                            shared: Exceptions · Pagination · Http (error renderer, request id) · Providers
database/                            migrations · factories · seeders (DemoDataSeeder)
docs/                                openapi.yaml · postman/ · DEVELOPMENT_PLAN.md
docker/                              Dockerfile and nginx config; docker-compose.yaml is in the project root
```

* **One handler per use case.** A controller turns the validated request into a `*Payload`, calls the module's
  `*Handler`, and renders the returned `*Result` through a `Resource`. Handlers never see HTTP; controllers never
  see Eloquent.
* **Modules depend downward only**: `Api` → `Modules` → `Core`. `Contract` uses the `Device` and `Party` modules (to
  check that they exist); `Device` and `Party` know nothing about contracts.
* **Repositories** wrap Eloquent; handlers hold the business rules (uniqueness, serial numbers, contract period).
* **Business errors know no HTTP.** They extend `NotFoundException` / `ConflictException` /
  `RuleViolationException` (`Core/Exceptions`); one renderer maps those families to 404 / 409 / 422.
* **Routes are discovered**: `Core/Providers/RouteServiceProvider` loads `app/Api/*/Routes/*Routes.php`, REST
  modules under `/api/v1`, `Docs` at the root. A new module needs no wiring.
* **Race safety**: handlers pre-check for friendly messages; the unique indexes and foreign keys are the final
  authority (a violated unique index is rendered as 409, a blocked device delete as `device_in_use`).

## Factories

`database/factories` has a factory for every model (`Party`, `Device`, `Contract`, `ContractDevice`, `ApiLog`), wired
in with `#[UseFactory]`. Generated data respects the database rules (unique tax numbers, unique manufacturer + model,
valid contract periods, one serial number per unit). Handy states: `Party::factory()->minimal()`,
`Contract::factory()->openEnded()`, `->expired()`, `->withDevices(3)`, `ContractDevice::factory()->for($device)`,
`ApiLog::factory()->failed()`. `APP_FAKER_LOCALE=hu_HU` (in `.env.example`) makes the data Hungarian.

## Testing

```bash
php artisan test                         # everything
php artisan test --filter=PartyApiTest   # one class
composer test                            # same as the first line, clears the config cache first
```

113 tests: feature tests for every endpoint and error path, the traffic log (including redaction and failure
isolation), Basic auth on the docs, the demo seeder, the factories, a guard that keeps `docs/openapi.yaml` in sync with
the registered routes, and unit tests for the sanitiser, pagination and the contract period. `tests/TestCase.php`
refuses to run on anything but in-memory SQLite, so the suite can never wipe a real database (not even from inside
the compose container, which exports `DB_CONNECTION=mysql`).

## Code quality

| Tool | What | Command | Config |
|------|------|---------|--------|
| Pint | code style (Laravel preset plus project rules, PSR-12 based) | `composer lint` / `composer lint:fix` | `pint.json` |
| PHPStan + Larastan | static analysis, Eloquent-aware | `composer analyse` | `phpstan.neon` |
| PHPUnit | tests | `composer test` | `phpunit.xml` |

`composer check` runs all three. Larastan is registered automatically by `phpstan/extension-installer`.

**Git pre-commit hook** (`.githooks/pre-commit`): when PHP files are staged it runs Pint on them and blocks the commit
on any finding. The PHPStan step (project-wide) is currently commented out in the hook. `composer install` enables it
(`git config core.hooksPath .githooks`, also available as `composer hooks`); skip it once with `git commit --no-verify`.
The hook does not trust whichever `php` is first on `PATH` (an IDE or a shell may put another version there): it uses
`$PHP_BIN` if set, otherwise the first PHP that satisfies `composer.json` (currently 8.5; `PATH`, then Homebrew
locations) and prints which one it picked. If none qualifies it says so, e.g.
`PHP_BIN=/opt/homebrew/opt/php@8.5/bin/php git commit`.

## Troubleshooting

| Symptom | Cause and fix |
|---------|---------------|
| Lists are empty after the install | Nothing is seeded by default: run the seeder, see [Demo data](#demo-data-seeder). |
| Port already in use on start | Another service uses 8080 or 3306: `APP_PORT=8081 DB_HOST_PORT=3307 docker compose up -d`. |
| A code change has no effect in Docker | The code is baked into the image: `docker compose up -d --build`. |
| `/docs` answers 401 | Wrong credentials, or `DOCS_USERNAME` / `DOCS_PASSWORD` is empty (the docs are then locked for everyone). |
| Schema or data is in an unwanted state | `docker compose down -v` deletes the database volume; the next `up` rebuilds the schema from the migrations. |
| `Your Composer dependencies require a PHP version ">= 8.5.0"` | The `php` on your `PATH` is older than 8.5 (Homebrew often keeps several). Put PHP 8.5 first, or run `/opt/homebrew/opt/php@8.5/bin/php vendor/bin/...`. |
| Pre-commit hook says no PHP >= 8.5 was found | Set `PHP_BIN` to a PHP 8.5 binary, see [Code quality](#code-quality). |
| `Access denied` from MySQL (local PHP) | The database or user from `.env` does not exist: run the SQL in [Option B](#option-b-local-php). |
| Tests refuse to run ("require in-memory SQLite") | Intentional safety guard, see [Testing](#testing); do not point the suite at a real database. |
| DBeaver: "Public Key Retrieval is not allowed" | See [Connecting with DBeaver](#connecting-with-dbeaver-or-any-sql-client). |

## Development plan

Requirements traceability, architecture decisions, delivery phases and the list of open items:
`docs/DEVELOPMENT_PLAN.md`.
