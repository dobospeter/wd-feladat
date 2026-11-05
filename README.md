# Asset Register API

REST API for registering **devices**, **contracts** and the devices placed on contracts.
Built with **Laravel 13**, **PHP 8.5**, **MySQL 8.4**, structured as **DDD** (an `Api` layer, a `Modules` layer
and a shared `Core`), following PSR-4 and PSR-12.

## Quick start (Docker)

```bash
docker compose up -d --build
```

Exactly three containers, nothing else:

| Service | Image                    | Role                                                        |
|---------|--------------------------|-------------------------------------------------------------|
| `web`   | nginx 1.27 (alpine)      | Public entry point, forwards every request to the app       |
| `app`   | PHP 8.5-FPM (alpine)     | The Laravel application (code and `vendor/` baked in)       |
| `db`    | MySQL 8.4                | Data, persisted in the `db-data` volume                     |

The app image is self-contained: Composer and git exist only in a build stage, the source is not mounted and
there is no `.env` inside. All configuration comes from the container environment (`docker-compose.yaml`),
and on start the app container creates the whole schema from the migrations. After changing the code, run
`docker compose up -d --build` again.

| What          | URL                                          |
|---------------|----------------------------------------------|
| API           | http://localhost:8080/api/v1                 |
| This README   | http://localhost:8080/readme (public)        |
| Documentation | http://localhost:8080/docs (HTTP Basic auth) |

Documentation credentials come from `DOCS_USERNAME` / `DOCS_PASSWORD` (compose defaults: `docs` / `change-me`).
**While either value is empty the documentation is locked for everyone.** This README is served publicly at `/readme`
(`/` redirects there), so it names these defaults: **set your own `DOCS_PASSWORD` before exposing the app.** Port and database credentials can be
overridden the same way (`APP_PORT`, `DB_*`), either in the shell or in a `.env` next to `docker-compose.yaml`
(compose reads it for substitution). `APP_ENV=production` and `APP_DEBUG=false` are fixed in the compose file on
purpose, so a developer's local `.env` can never switch debug output on in the container.

```bash
docker compose exec app php artisan test        # run the test suite (always on in-memory SQLite)
docker compose exec app vendor/bin/pint --test  # code style (pint.json)
docker compose exec app vendor/bin/phpstan analyse --memory-limit=1G   # static analysis (Larastan)
docker compose down -v                          # stop and drop the database volume
```

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

### Without Docker

Requires PHP 8.5 and a MySQL server: copy `.env.example` to `.env`, point `DB_*` at your MySQL, then
`composer setup` (installs, generates the key, migrates) and `php artisan serve`.

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

Decisions where the brief left room (all enforced in the domain *and* by database constraints):

* A physical unit (`device` + `serial_number`) can be on **one contract only** (`unique(device_id, serial_number)`).
* A device that is placed on a contract **cannot be deleted** (409 `device_in_use`, FK `RESTRICT`).
* A party that has contracts **cannot be deleted** (409 `party_in_use`, FK `RESTRICT`).
* Deleting a contract **releases** its devices (FK `CASCADE`).
* `PUT` is a full replacement; there is no `PATCH`.
* A device placed on a contract is identified by an `assignment_id`, returned in the contract's `devices`.

## API

Base path `/api/v1`. JSON in, JSON out. Identifiers are UUIDv7, dates are `YYYY-MM-DD`.

| Method | Path                                      | Description                                  |
|--------|-------------------------------------------|----------------------------------------------|
| GET    | `/parties`                                | List contracting parties (`page`, `per_page` ≤ 100) |
| POST   | `/parties`                                | Create a party                               |
| GET    | `/parties/{party}`                        | Get a party                                  |
| PUT    | `/parties/{party}`                        | Replace a party                              |
| DELETE | `/parties/{party}`                        | Delete a party                               |
| GET    | `/devices`                                | List devices (`page`, `per_page` ≤ 100)      |
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

Full request/response schemas: `docs/openapi.yaml`, rendered at `/docs`.

### Postman

`docs/postman/asset-register.postman_collection.json` contains a request (with tests) for every endpoint, the
documentation and health routes, and examples of each error case. Import it into Postman and run it top to bottom
in the Collection Runner: requests store `deviceId`, `contractId` and `assignmentId` in collection variables, and
step 4 cleans up. Variables to adjust: `baseUrl` (default `http://localhost:8080`), `docsUser`, `docsPassword`.
From the command line: `npx newman run docs/postman/asset-register.postman_collection.json`.

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
sent and can never fail a request.

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
```

* **One handler per use case.** A controller turns the validated request into a `*Payload`, calls the module's
  `*Handler`, and renders the returned `*Result` through a `Resource`. Handlers never see HTTP; controllers never
  see Eloquent.
* **Modules depend downward only**: `Api` → `Modules` → `Core`. `Contract` uses the `Device` module (to check a
  device exists); `Device` knows nothing about contracts.
* **Repositories** wrap Eloquent; handlers hold the business rules (uniqueness, serial numbers, contract period).
* **Business errors know no HTTP.** They extend `NotFoundException` / `ConflictException` /
  `RuleViolationException` (`Core/Exceptions`); one renderer maps those families to 404 / 409 / 422.
* **Routes are discovered**: `Core/Providers/RouteServiceProvider` loads `app/Api/*/Routes/*Routes.php`, REST
  modules under `/api/v1`, `Docs` at the root. A new module needs no wiring.
* **Race safety**: handlers pre-check for friendly messages; the unique indexes and foreign keys are the final
  authority (a violated unique index is rendered as 409, a blocked device delete as `device_in_use`).

## Code quality

| Tool | What | Command | Config |
|------|------|---------|--------|
| Pint | code style (Laravel preset plus project rules, PSR-12 based) | `composer lint` / `composer lint:fix` | `pint.json` |
| PHPStan + Larastan | static analysis, Eloquent-aware | `composer analyse` | `phpstan.neon` |
| PHPUnit | tests | `composer test` | `phpunit.xml` |

`composer check` runs all three. Larastan is registered automatically by `phpstan/extension-installer`.

**Git pre-commit hook** (`.githooks/pre-commit`): when PHP files are staged it runs Pint on them and blocks the commit
on any finding. The PHPStan step (project-wide) is currently commented out in the hook. `composer install` enables it (`git config core.hooksPath .githooks`,
also available as `composer hooks`); skip it once with `git commit --no-verify`. The hook does not trust whichever
`php` is first on `PATH` (an IDE or a shell may put another version there): it uses `$PHP_BIN` if set, otherwise the
first PHP that satisfies `composer.json` (currently 8.5; `PATH`, then Homebrew locations) and prints which one it
picked. If none qualifies it says so, e.g. `PHP_BIN=/opt/homebrew/opt/php@8.5/bin/php git commit`.

## Development plan

Requirements traceability, architecture decisions, delivery phases and the list of open items:
`docs/DEVELOPMENT_PLAN.md`.

## Tests

```bash
php artisan test
```

98 tests: feature tests for every endpoint and error path, the traffic log (including redaction and failure
isolation), Basic auth on the docs, a guard that keeps `docs/openapi.yaml` in sync with the registered routes,
and unit tests for the domain and the sanitiser. `tests/TestCase.php` refuses to run on anything but in-memory
SQLite, so the suite can never wipe a real database (not even from inside the compose container, which exports
`DB_CONNECTION=mysql`).
