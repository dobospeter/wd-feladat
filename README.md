# Asset Register API

REST API for registering **devices**, **contracts** and the devices placed on contracts.
Built with **Laravel 13**, **PHP 8.5**, **MySQL 8.4**, structured as **DDD** (bounded contexts with
Domain / Application / Infrastructure / Presentation layers), following PSR-4 and PSR-12.

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
docker compose exec app vendor/bin/pint --test  # PSR-12 check
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
Device (type)  1 ──< ContractDevice >── 1  Contract
                     serial_number
```

* A **device** is a device *type* (e.g. Dell Latitude 5440), unique by manufacturer + model.
* A **contract** has a unique number, a partner, a period (`ends_on` optional) and notes.
* The N:N relation is the `contract_devices` table. Each row is one physical **unit**: it carries its own
  **serial number**. The same device type can therefore be on many contracts, and several times on one
  contract, always with different serial numbers.

Decisions where the brief left room (all enforced in the domain *and* by database constraints):

* A physical unit (`device` + `serial_number`) can be on **one contract only** (`unique(device_id, serial_number)`).
* A device that is placed on a contract **cannot be deleted** (409 `device_in_use`, FK `RESTRICT`).
* Deleting a contract **releases** its devices (FK `CASCADE`).
* `PUT` is a full replacement; there is no `PATCH`.
* A device placed on a contract is identified by an `assignment_id`, returned in the contract's `devices`.

## API

Base path `/api/v1`. JSON in, JSON out. Identifiers are UUIDv7, dates are `YYYY-MM-DD`.

| Method | Path                                      | Description                                  |
|--------|-------------------------------------------|----------------------------------------------|
| GET    | `/devices`                                | List devices (`page`, `per_page` ≤ 100)      |
| POST   | `/devices`                                | Create a device                              |
| GET    | `/devices/{device}`                       | Get a device                                 |
| PUT    | `/devices/{device}`                       | Replace a device                             |
| DELETE | `/devices/{device}`                       | Delete a device                              |
| GET    | `/contracts`                              | List contracts (**without** devices)         |
| POST   | `/contracts`                              | Create a contract                            |
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
| 404 | `not_found`, `device_not_found`, `contract_not_found`, `contract_device_not_found` |
| 409 | `device_already_exists`, `device_in_use`, `contract_number_already_exists`, `serial_number_already_assigned`, `conflict` |
| 422 | `validation_failed`, `invalid_contract_period`, `invalid_value`, `device_not_found` (unknown `device_id` in a body) |
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
src/                                 PSR-4 namespace Inventory\
├── Shared/        kernel: Identifier, Guard, Page, domain exception families, error renderer, request id
├── Device/        Domain · Application (use cases, DTO) · Infrastructure (Eloquent) · Presentation (HTTP)
├── Contract/      Contract aggregate (owns its ContractDevice units), same four layers
├── ApiLog/        traffic log: sanitiser, recorder, repository, middleware
└── Docs/          Basic-auth protected OpenAPI UI
bootstrap/providers.php   wires one service provider per context (no application code lives in app/)
```

* **Dependencies point inward.** Domain has no Laravel dependency (apart from the `symfony/uid` library);
  Eloquent models live in Infrastructure and never leave it; repositories are interfaces in the domain.
* **Contract is an aggregate root**; devices are placed/removed only through `Contract::attachDevice()` /
  `detachDevice()`. Contract and Device reference each other by id only.
* **Contexts stay decoupled.** "Is this device still on a contract?" is a port (`DeviceUsageChecker`) declared
  by Device and implemented by Contract.
* **Domain errors know no HTTP.** They extend `NotFoundException` / `ConflictException` /
  `RuleViolationException`; one renderer maps those families to 404 / 409 / 422.
* **Thin controllers**: FormRequest → DTO → one use-case class → Resource.
* **Race safety**: use cases pre-check for friendly messages, the unique indexes are the final authority and a
  violation is rendered as 409.

## Development plan

Requirements traceability, architecture decisions, delivery phases and the list of open items:
`docs/DEVELOPMENT_PLAN.md`.

## Tests

```bash
php artisan test
```

81 tests: feature tests for every endpoint and error path, the traffic log (including redaction and failure
isolation), Basic auth on the docs, a guard that keeps `docs/openapi.yaml` in sync with the registered routes,
and unit tests for the domain and the sanitiser. `tests/TestCase.php` refuses to run on anything but in-memory
SQLite, so the suite can never wipe a real database (not even from inside the compose container, which exports
`DB_CONNECTION=mysql`).
