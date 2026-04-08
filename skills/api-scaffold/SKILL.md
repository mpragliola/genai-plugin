---
name: api-scaffold
description: >
  Scaffolds a new Laravel API endpoint or microservice following org conventions.
  Use when creating new API endpoints, resources, or entire API services.
  Trigger: "scaffold", "new endpoint", "new API", "create resource", "new service".
disable-model-invocation: true
---

# API scaffolding — org conventions

## Rules (non-negotiable)

These are architectural decisions. Not suggestions. Follow them exactly.

### Architecture
- Controllers are thin dispatchers. Max 5 lines per method. No business logic.
- Business logic lives in Action classes: `app/Actions/<Domain>/<Verb><Noun>Action.php`
- One Action = one public `execute()` method. Actions are testable in isolation.
- Database access goes through Repository classes. Never call Eloquent directly in Actions or Controllers.
- Input validation uses Form Requests. Never validate inside controllers or actions.

### API conventions
- All responses use JSON:API format via `spatie/laravel-json-api` or a thin resource wrapper.
- Every endpoint returns proper HTTP status codes: 200, 201, 204, 400, 401, 403, 404, 422, 500.
- Error responses always include: `{"error": {"code": "...", "message": "..."}}`.
- Pagination is cursor-based for list endpoints. Never use offset pagination.
- API versioning via URL prefix: `/api/v1/`. Never via headers.

### File structure for a new resource
When scaffolding a resource called `LeaseOffer`:
```
app/
  Http/
    Controllers/Api/V1/LeaseOfferController.php
    Requests/LeaseOffer/
      IndexLeaseOfferRequest.php
      StoreLeaseOfferRequest.php
      UpdateLeaseOfferRequest.php
  Actions/LeaseOffer/
    ListLeaseOffersAction.php
    CreateLeaseOfferAction.php
    UpdateLeaseOfferAction.php
    DeleteLeaseOfferAction.php
  Repositories/LeaseOfferRepository.php
  Models/LeaseOffer.php
  Resources/LeaseOfferResource.php
routes/
  api.php (add route group)
tests/
  Unit/Actions/LeaseOffer/
    CreateLeaseOfferActionTest.php
    ...
  Feature/Api/V1/
    LeaseOfferControllerTest.php
database/
  migrations/xxxx_create_lease_offers_table.php
  factories/LeaseOfferFactory.php
```

### PHP conventions
- `declare(strict_types=1);` in every file.
- Full type hints on all parameters and return types. No `mixed` unless truly necessary.
- PSR-12 formatting (enforced by hook — don't worry about it manually).
- Nullable types explicit: `?string`, never implicit null.
- No `dd()`, `dump()`, `var_dump()` in committed code.

### Testing
- Every Action gets a unit test with data providers for edge cases.
- Every Controller gets a feature test covering: happy path, validation failure, auth failure, not found.
- Use factories and seeders. Never hardcode test data.

### Security
- Every endpoint behind auth middleware unless explicitly public.
- Rate limiting on all public endpoints.
- Mass assignment protection via `$fillable`, never `$guarded = []`.

## Process

### Step 1: Ask what to scaffold
Use AskUserQuestion: "What resource? What fields? Any special behavior (soft deletes, slugs, search)?"

### Step 2: Plan
List all files that will be created. Wait for approval.

### Step 3: Generate
Create all files following the structure above. Use the examples in this skill's
`examples/` directory as the reference for code style.

### Step 4: Test
Delegate to the **test-writer** agent to create tests for the new resource.

### Step 5: Verify
Run `php artisan test --filter=<Resource>` to confirm everything passes.

## Reference
- Read `examples/Controller.php` for the thin controller pattern.
- Read `examples/Action.php` for the Action class pattern.
- Read `examples/Repository.php` for the Repository pattern.
- Read `examples/FormRequest.php` for validation pattern.
- Read `examples/Resource.php` for JSON:API resource pattern.
- Read `examples/FeatureTest.php` for controller test pattern.
