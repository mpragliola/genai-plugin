---
name: setup-testing
description: >
  Installs and scaffolds PHPUnit + Behat testing infrastructure for PHP projects.
  Framework-aware: detects Laravel, Symfony, or framework-agnostic.
  Trigger: "setup testing", "install phpunit", "install behat", "testing infrastructure".
allowed-tools: Read, Bash, Write, Glob
---

# Testing infrastructure setup

## Overview

Install PHPUnit and Behat, then scaffold the configuration files and bootstrap contexts needed for the TDD pipeline. Run this once per project before any API scaffolding or feature development.

## Step 1: Detect framework

Check the project root:
- `artisan` present → **Laravel**
- `bin/console` present → **Symfony**
- Neither → **framework-agnostic**

## Step 2: Install packages

**All frameworks:**
```bash
composer require --dev phpunit/phpunit behat/behat behat/mink-extension behat/mink-browserkit-driver
```

**Symfony only (run after the above):**
```bash
composer require --dev friends-of-behat/symfony-extension
```

**Failure modes:**
- `composer` not found: output `"Error: composer not found. Install Composer and re-run."` Stop.
- Framework detected but `vendor/` absent: output `"Warning: vendor/ directory not found — run composer install first. Continuing with config scaffolding."` Continue.

## Step 3: Scaffold config files

Write the following files. Do not overwrite any file that already exists — skip it and report "already exists".

### `phpunit.xml`

**Laravel:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="TELESCOPE_ENABLED" value="false"/>
    </php>
</phpunit>
```

**Symfony:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Project Test Suite">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
    <php>
        <ini name="display_errors" value="1"/>
        <env name="APP_ENV" value="test"/>
        <env name="KERNEL_CLASS" value="App\Kernel"/>
    </php>
</phpunit>
```

**Agnostic:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Project Test Suite">
            <directory suffix="Test.php">./tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

### `behat.yml`

**Laravel and agnostic (same content):**
```yaml
default:
  suites:
    unit:
      paths: ["%paths.base%/features/unit"]
      contexts: [FeatureContext]
    api:
      paths: ["%paths.base%/features/api"]
      contexts: [ApiContext]
  extensions:
    Behat\MinkExtension:
      base_url: "%env(APP_URL)%"
      sessions:
        default:
          browser_kit: ~
```

**Symfony:**
```yaml
default:
  suites:
    unit:
      paths: ["%paths.base%/features/unit"]
      contexts: [FeatureContext]
    api:
      paths: ["%paths.base%/features/api"]
      contexts: [ApiContext]
  extensions:
    Behat\MinkExtension:
      base_url: "%env(APP_URL)%"
      sessions:
        default:
          browser_kit: ~
    FriendsOfBehat\SymfonyExtension: ~
```

### `features/bootstrap/FeatureContext.php`

**Laravel:**
```php
<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FeatureContext implements Context
{
    use RefreshDatabase;

    protected \Illuminate\Foundation\Application $app;

    public function __construct()
    {
        $this->app = require __DIR__ . '/../../bootstrap/app.php';
        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    }
}
```

**Symfony:**
```php
<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use FriendsOfBehat\SymfonyExtension\Context\KernelAwareContext;
use Symfony\Component\HttpKernel\KernelInterface;

class FeatureContext implements Context, KernelAwareContext
{
    private KernelInterface $kernel;

    public function setKernel(KernelInterface $kernel): void
    {
        $this->kernel = $kernel;
    }
}
```

**Agnostic:**
```php
<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;

class FeatureContext implements Context
{
}
```

### `features/bootstrap/ApiContext.php`

All frameworks (same file):
```php
<?php

declare(strict_types=1);

use Behat\MinkExtension\Context\MinkContext;

class ApiContext extends MinkContext
{
    /**
     * Send an HTTP request via Mink's browserkit session.
     *
     * @param array<string, mixed> $body
     */
    public function sendRequest(string $method, string $path, array $body = []): void
    {
        $client = $this->getSession()->getDriver()->getClient();
        $client->request(
            $method,
            $path,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            json_encode($body, JSON_THROW_ON_ERROR)
        );
    }

    public function assertResponseStatus(int $expectedCode): void
    {
        $actual = $this->getSession()->getStatusCode();
        \PHPUnit\Framework\Assert::assertSame(
            $expectedCode,
            $actual,
            "Expected HTTP {$expectedCode}, got {$actual}."
        );
    }

    /**
     * Assert a value at a dot-notation JSON path (e.g. 'data.0.id').
     *
     * @param mixed $expectedValue
     */
    public function assertJsonPath(string $path, mixed $expectedValue): void
    {
        $body = json_decode(
            $this->getSession()->getPage()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $actual = $this->resolveDotPath($body, $path);
        \PHPUnit\Framework\Assert::assertSame(
            $expectedValue,
            $actual,
            "Expected JSON path '{$path}' to be "
                . json_encode($expectedValue)
                . ', got '
                . json_encode($actual)
        );
    }

    /**
     * @param array<string, mixed> $data
     * @return mixed
     */
    private function resolveDotPath(array $data, string $path): mixed
    {
        foreach (explode('.', $path) as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return null;
            }
            $data = $data[$segment];
        }

        return $data;
    }
}
```

### Feature directories

Create empty placeholder files so git tracks the directories:
- `features/unit/.gitkeep`
- `features/api/.gitkeep`

## Step 4: Report

Output:
```
Testing infrastructure installed and scaffolded:

Packages installed:
  ✓ phpunit/phpunit
  ✓ behat/behat
  ✓ behat/mink-extension
  ✓ behat/mink-browserkit-driver
  [✓ friends-of-behat/symfony-extension  — Symfony only]

Files created:
  ✓ phpunit.xml
  ✓ behat.yml
  ✓ features/bootstrap/FeatureContext.php
  ✓ features/bootstrap/ApiContext.php
  ✓ features/unit/.gitkeep
  ✓ features/api/.gitkeep

Next step: run /genai-enabler:api-scaffold or /genai-enabler:feature to start building.
```
