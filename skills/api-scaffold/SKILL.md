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

### Versioning convention
- All routes live under `/api/v{n}/`. Current default: `v1`.
- The OpenAPI spec `info.version` major number must match the route prefix (spec `1.x.x` → routes `/api/v1/`).
- **Non-breaking additions** (new optional fields, new endpoints): no version bump required.
- **Breaking changes** (removed fields, changed response shape, removed endpoints): create a new prefix (`/api/v2/`). Never modify a published versioned contract.
- Version prefix is URL-only. Never use `Accept: application/vnd.api+json;version=2` or `?version=2`.

### File structure for a new resource
When scaffolding a resource called `LeaseOffer`:
```
app/
  Http/
    Controllers/Api/V1/LeaseOfferController.php
    Controllers/
      HealthController.php          (GET /health — no auth, load balancer probe)
    Controllers/Api/V1/
      StatusController.php          (GET /api/v1/status — auth required)
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
  Feature/
    HealthControllerTest.php
    Api/V1/
      LeaseOfferControllerTest.php
      StatusControllerTest.php
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

### Step 0: OpenAPI spec
Check for `docs/openapi/<resource-kebab-case>.yaml`.

- **File does not exist:** write it using the template below, then present it to the user and wait for approval before proceeding to Step 1.
- **File already exists:** read it, present it to the user for confirmation, and wait for approval before proceeding to Step 1.

Use this template (substitute `<Resource>` and `<resources>` throughout):

```yaml
openapi: "3.1.0"
info:
  title: "<Resource> API"
  version: "1.0.0"
paths:
  /api/v1/<resources>:
    get:
      summary: List <resources>
      security: [{ bearerAuth: [] }]
      responses:
        "200":
          description: Paginated list
          content:
            application/json:
              schema:
                type: object
                properties:
                  data: { type: array, items: { $ref: "#/components/schemas/<Resource>" } }
                  meta: { type: object }
        "401": { description: Unauthenticated }
        "403": { description: Forbidden }
    post:
      summary: Create <resource>
      security: [{ bearerAuth: [] }]
      requestBody:
        required: true
        content:
          application/json:
            schema: { $ref: "#/components/schemas/<Resource>Input" }
      responses:
        "201":
          description: Created
          content:
            application/json:
              schema: { $ref: "#/components/schemas/<Resource>" }
        "422": { description: Validation error }
        "401": { description: Unauthenticated }
  /api/v1/<resources>/{id}:
    get:
      summary: Show <resource>
      parameters:
        - { name: id, in: path, required: true, schema: { type: integer } }
      responses:
        "200":
          description: OK
          content:
            application/json:
              schema: { $ref: "#/components/schemas/<Resource>" }
        "404": { description: Not found }
        "401": { description: Unauthenticated }
    put:
      summary: Update <resource>
      parameters:
        - { name: id, in: path, required: true, schema: { type: integer } }
      requestBody:
        required: true
        content:
          application/json:
            schema: { $ref: "#/components/schemas/<Resource>Input" }
      responses:
        "200":
          description: OK
          content:
            application/json:
              schema: { $ref: "#/components/schemas/<Resource>" }
        "422": { description: Validation error }
        "404": { description: Not found }
    delete:
      summary: Delete <resource>
      parameters:
        - { name: id, in: path, required: true, schema: { type: integer } }
      responses:
        "204": { description: Deleted }
        "404": { description: Not found }
components:
  securitySchemes:
    bearerAuth:
      type: http
      scheme: bearer
  schemas:
    <Resource>:
      type: object
      properties:
        id: { type: integer }
        # add resource-specific fields here
        created_at: { type: string, format: date-time }
        updated_at: { type: string, format: date-time }
    <Resource>Input:
      type: object
      required: []
      properties: {}
      # add required fields and their types here
```

Present the completed spec to the user and wait for approval before proceeding to Step 1.

### Step 1: Ask what to scaffold
Use AskUserQuestion: "What resource? What fields? Any special behavior (soft deletes, slugs, search)?"

### Step 2: Plan
List all files that will be created. Wait for approval.

If this is the first resource in the project (i.e. `HealthController` does not yet exist), also scaffold the two standard healthcheck endpoints:

**`app/Http/Controllers/HealthController.php`**
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

final class HealthController extends Controller
{
    public function check(): JsonResponse
    {
        return response()->json([
            'status'    => 'ok',
            'timestamp' => now()->toIso8601String(),
            'version'   => config('app.version', 'unknown'),
        ]);
    }
}
```

**`app/Http/Controllers/Api/V1/StatusController.php`**
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class StatusController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status'      => 'ok',
            'version'     => config('app.version', 'unknown'),
            'environment' => app()->environment(),
        ]);
    }
}
```

**Routes to register in `routes/api.php`:**
```php
// Public — no auth (load balancer / orchestrator probe)
Route::get('/health', [\App\Http\Controllers\HealthController::class, 'check']);

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/status', [\App\Http\Controllers\Api\V1\StatusController::class, 'index']);
    // ... resource routes
});
```

**Feature tests:**

`tests/Feature/HealthControllerTest.php`:
```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class HealthControllerTest extends TestCase
{
    public function test_health_returns_200_with_expected_structure(): void
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'timestamp', 'version'])
                 ->assertJson(['status' => 'ok']);
    }
}
```

`tests/Feature/Api/V1/StatusControllerTest.php`:
```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Tests\TestCase;

class StatusControllerTest extends TestCase
{
    public function test_status_returns_200_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/status');

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'version', 'environment'])
                 ->assertJson(['status' => 'ok']);
    }

    public function test_status_returns_401_when_unauthenticated(): void
    {
        $response = $this->getJson('/api/v1/status');
        $response->assertStatus(401);
    }
}
```

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
