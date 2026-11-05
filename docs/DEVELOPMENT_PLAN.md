# Development plan - Asset Register API

Status as of 2026-11-04. Legend: ✅ done and verified · 🟡 done, verification gap · ⬜ open.

## 1. Goal and scope

A REST API (no UI) that registers **devices**, **contracts** and the **devices placed on contracts**. A device
type can be on many contracts (N:N), each placed unit carrying its own serial number.

Out of scope unless stated otherwise: user management, API authentication, a front end.

## 2. Requirements traceability

| # | Requirement (from the brief) | Implementation | Verified by |
|---|------------------------------|----------------|-------------|
| 1 | Contract data, contracting party and devices | `Party`, `Contract` and `Device` models, `contracts.party_id` FK, `contract_devices` pivot | feature tests, MySQL schema inspected |
| 2 | N:N device type ↔ contract, different serial per unit | `unique(device_id, serial_number)`, `ContractDeviceAttachHandler` | `ContractApiTest` (many contracts, same type, different serials) |
| 2a | Contracting party (*szerződő fél*) CRUD; every contract belongs to one party | `Party` module, `/api/v1/parties`, `party_id` on contracts, FK `RESTRICT` | `PartyApiTest` (16), contract party tests |
| 3 | Device CRUD (list + single) | `/api/v1/devices` | `DeviceApiTest` (17), Postman folder 1 |
| 4 | Contract CRUD (list + single) | `/api/v1/contracts` | `ContractApiTest` (22), Postman folder 2 |
| 5 | Contract detail shows its devices, the list does not | `ContractDetailsResource` vs `ContractResource` | dedicated tests + Postman assertion |
| 6 | Place / take off a device on a contract | `POST /contracts/{id}/devices`, `DELETE .../{assignment}` | tests + Postman folder 3 |
| 7 | Unified error handling | `ApiExceptionRenderer`, one envelope, stable codes, `request_id` | `ErrorHandlingTest` (8), all error cases in Postman folder 6 |
| 8 | Request/response logging in the database | `LogApiTraffic` → `api_logs`, redaction, truncation, failure isolation | `ApiTrafficLogTest` (6), rows inspected in MySQL |
| 9 | OpenAPI docs on a public URL with Basic auth | `/docs`, `/docs/openapi.yaml`, `RequireDocsCredentials` | `DocsTest` (5), `OpenApiSpecTest` (spec ↔ routes drift guard) |
| 10 | Laravel 13 | 13.40.0 | `php artisan --version` |
| 11 | Supported PHP, ideally 8.5 | `^8.5`, container 8.5.12 | `docker compose exec app php -v` |
| 12 | MySQL | MySQL 8.4 | full suite of requests run against it |
| 13 | Clean code (SOLID, DRY), DDD | Api / Modules / Core layers, handlers with payloads and results, shared `PaginatedCollection` | review, see §3 |
| 14 | PSR-4 and PSR-12 | Pint (`pint.json`, Laravel preset, PSR-12 based) and PHPStan + Larastan, both enforced by the pre-commit hook; `composer dump-autoload --strict-psr` | all clean |
| 15 | Docker package (`docker-compose.yaml`) | nginx + PHP-FPM app + MySQL, self-contained images | fresh-copy build and run (§6) |
| 16 | Project can be built from migrations | 4 migrations, applied on container start | fresh MySQL volume; rollback/re-apply round trip |

## 3. Architecture decisions

| Decision | Reason |
|----------|--------|
| Layers `app/Api` (HTTP), `app/Modules` (business logic), `app/Core` (shared), namespace `App\` | DDD: each module owns its handlers, models and repositories; the HTTP layer is thin |
| One handler per use case, with a `Payload` in and a `Result` out; one single-action controller per endpoint | Controllers never see Eloquent, handlers never see HTTP |
| Business rules (uniqueness, serial numbers, contract period) live in handlers and the `ContractPeriod` value object | One place per rule; DB constraints stay the final authority |
| UUIDv7 identifiers (`HasUuids`) | Time-ordered keys, safe to expose |
| `Contract` depends on `Device`, never the reverse; a blocked device delete is detected from the foreign-key error (`device_in_use`) | No circular module dependency |
| Business errors are three families (`NotFound`/`Conflict`/`RuleViolation`) in `Core/Exceptions`, with no HTTP knowledge | One renderer maps them to 404 / 409 / 422 |
| A physical unit (device + serial) can be on one contract only | Reading of the brief; enforced by domain check and unique index |
| `PUT` only (full replacement), no `PATCH` | Simpler contract, smaller surface |
| Use-case pre-checks for friendly errors, DB unique indexes as final authority (→ 409) | Race safety |
| Traffic log written in `terminate()` and never allowed to fail a request | No client-visible latency or failures from logging |
| Self-contained images, configuration only from the container environment | Reproducible; no host files needed |

## 4. Delivery phases (all completed)

| Phase | Content | Status |
|-------|---------|--------|
| 0 | Laravel 13 skeleton, strip web/frontend parts | ✅ |
| 1 | Core: pagination, error families, error renderer, request id | ✅ |
| 2 | Device module and API (handlers, model, repository, controllers) | ✅ |
| 3 | Contract module and API (handlers incl. placement, models, repositories, controllers); Party module and API (added later, see §9) | ✅ |
| 4 | Migrations (devices, contracts, contract_devices, api_logs) | ✅ |
| 5 | API traffic log (middleware, sanitiser, repository) | ✅ |
| 6 | OpenAPI document, Basic-auth docs UI, drift test | ✅ |
| 7 | Docker: nginx + app + MySQL, healthchecks, migrate on start | ✅ |
| 8 | Test suite (112 tests), Pint, safety guard against non-SQLite test DB | ✅ |
| 9 | Postman collection (26 requests, 55 assertions), public README page, DBeaver access | ✅ |

## 5. Quality gates (definition of done)

```bash
vendor/bin/pint --test                        # code style
vendor/bin/phpstan analyse                    # static analysis (Larastan)
composer check                                # lint + analyse + test in one go
php artisan test                              # 112 tests
composer validate --strict
composer dump-autoload -o --strict-psr        # PSR-4
composer audit                                # known vulnerabilities
npx newman run docs/postman/asset-register.postman_collection.json   # against a running stack
```

Last run (2026-11-04): all pass. 112 tests / 630 assertions, no known vulnerabilities, 35 Postman requests /
76 assertions on MySQL, including from a fresh copy of the repository without `.env` and `vendor/`.

## 6. Audit - what is still missing

Checked against the brief and by running the project. **Every item in the brief is implemented.** The gaps below
are about delivery and robustness, not missing functionality.

### Should be done before handing the project over

| # | Gap | Evidence | Proposed action |
|---|-----|----------|-----------------|
| 1 | ⬜ **Nothing is committed** | `git log` is empty; every file is untracked | Commit (a clean commit set was verified: no `.env`, no `vendor/`, all required files present) |
| 2 | 🟡 **Tests run on SQLite only; production is MySQL** | MySQL is case-insensitive, SQLite is not: unit `sn-AB` after `SN-ab` is rejected (409) on MySQL but the SQLite suite would accept it. FK, JSON and `mediumText` behaviour is also only checked by hand/Postman | Run the suite against a MySQL service in CI (and lift the SQLite-only guard for that explicit case); add a test pinning the case-insensitive serial rule |
| 3 | ⬜ **No CI pipeline** | no `.github/` | GitHub Actions: Pint, tests on MySQL, OpenAPI drift test, `composer audit`, Newman against the compose stack |
| 4 | ⬜ **`api_logs` grows without bound** | no retention or pruning | Retention setting + `Prunable`/scheduled prune (e.g. 30 days) |

### Small clean-ups

| # | Gap | Proposed action |
|---|-----|-----------------|
| 5 | Dead skeleton leftovers: `config/auth.php` still imports the removed `App\Models\User` (the empty `AppServiceProvider`, `routes/console.php` and empty directories are already gone) | Remove `config/auth.php` (no auth is used) |
| 6 | The concurrent-write path (unique-index violation → 409 `conflict`) has no automated test | Add a test that bypasses the pre-check and asserts the 409 |
| 7 | Public README names the default docs password | Already warned in the README; for a real deployment set `DOCS_PASSWORD` (consider refusing to start with `change-me`) |

### Optional improvements (not in the brief)

| # | Idea |
|---|------|
| 8 | List filtering, sorting and search (e.g. contracts by partner/number, devices by manufacturer) |
| 9 | Expose `created_at` / `updated_at` in responses |
| 10 | API authentication, rate limiting, CORS - only the documentation is protected today |
| 11 | Swagger UI is loaded from a CDN, so `/docs` needs internet access in the browser; vendor the assets or switch to an offline renderer |
| 12 | Factories and the demo seeder are done (`database/factories`, `DemoDataSeeder`) |
| 13 | Container hardening: restart policies, `web` healthcheck, TLS termination, read-only filesystem |
| 14 | Raise PHPStan above level 6 and drop the ignored identifiers (`argument.type`, `return.type`, ...) from `phpstan.neon`; the `app/` code already passed level 9 without ignores |

## 7. Recommended next steps

1. Commit the current state (item 1).
2. Add MySQL-backed test execution and CI (items 2, 3, 6) - the largest remaining risk.
3. Add `api_logs` retention (item 4) and remove the dead leftovers (item 5).
4. Pick from the optional list only if the reviewers expect it.


## 8. Restructure (2026-11-05)

The code was moved from `src/Inventory` (bounded contexts with Domain / Application / Infrastructure / Presentation) to
the `app/Api` · `app/Modules` · `app/Core` layout. The REST contract did not change: the feature tests, the OpenAPI
file and the Postman collection pass unmodified. Trade-offs of the new layout: Eloquent models and concrete
repositories replace the framework-free entities and repository interfaces, so business rules sit in handlers rather
than in an aggregate; in return there is less mapping code and one obvious place per use case.


## 9. Contracting party (2026-11-05)

A contract used to carry a free-text `partner_name`. It now references a **Party** entity (`party_id`, required),
with its own CRUD at `/api/v1/parties`. Contract responses embed the `party` object in both the list and the detail.
This is a breaking API change (`partner_name` is gone), made before any release: the migrations were edited in place
(`parties` is created first), so an existing development database must be recreated (`docker compose down -v`).
