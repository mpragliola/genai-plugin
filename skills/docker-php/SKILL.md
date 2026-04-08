---
name: docker-php
description: >
  Scaffolds production-grade Docker setup for PHP/Laravel applications.
  Multi-stage builds, php-fpm + nginx, dev/test/prod targets.
  Trigger: "docker", "containerize", "Dockerfile", "docker-compose".
disable-model-invocation: true
---

# Docker PHP scaffolding — org conventions

## Rules (non-negotiable)

### Container architecture
- php-fpm and nginx run as SEPARATE containers. Never combine them.
  Why: single-process principle. Independent scaling, healthchecks, log streams.
- php-fpm listens on port 9000. Never expose port 9000 publicly.
- nginx handles HTTP (port 80), serves static files, proxies PHP to fpm via FastCGI.
- In Kubernetes: same pod, separate containers, shared emptyDir volume.

### Image strategy
- Base image: `php:8.3-fpm-alpine`. Always alpine. Fewer CVEs, smaller surface.
- Multi-stage Dockerfile with named stages: `base`, `dev`, `test`, `prod`.
- `dev` stage adds xdebug, pcov. Mounts source via volume. Never copies source.
- `test` stage copies source + dev dependencies. CMD runs phpunit.
- `prod` stage copies source + no-dev dependencies. Opcache ON. Runs as non-root.
- Queue workers and schedulers reuse the `prod` stage with a different CMD.

### Composer layer caching
Always copy dependency files BEFORE source code:
```dockerfile
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader
COPY . .
RUN composer dump-autoload --optimize
```
This caches `composer install` unless dependencies actually change.

### Non-root production
- Production containers run as `www-data` (USER directive before CMD).
- Filesystem is read-only except explicitly mounted volumes (storage, cache).

### Health checks
- php-fpm: `HEALTHCHECK CMD php-fpm -t || exit 1`
- nginx: `HEALTHCHECK CMD curl -sf http://localhost/health || exit 1`
- Laravel: use the built-in `/up` route (since Laravel 11).

### Logging
- App logs to stdout/stderr. 12-factor.
- php-fpm access logs disabled (mirrored by nginx).
- Error logs to stderr.

### Docker Compose targets
- `docker compose up` → development (volumes, xdebug, local DB)
- `docker compose -f docker-compose.yml -f docker-compose.prod.yml up` → production
- `docker compose -f docker-compose.yml -f docker-compose.test.yml run --rm test` → CI

### .dockerignore
Always exclude: `.git`, `vendor/`, `node_modules/`, `.env`, `tests/`, `.claude/`,
`specs/`, `checkpoints/`, `docker-compose*.yml`.

## Process

### Step 1: Detect or ask
Check project for Laravel/Symfony/vanilla. Ask only what can't be inferred:
database choice, Redis needed, Node.js asset building, extra PHP extensions.

### Step 2: Generate
Adapt the templates in this skill's `templates/` directory. Generate:
```
docker/
  php/Dockerfile
  php/php-dev.ini
  php/php-prod.ini
  nginx/Dockerfile
  nginx/default.conf
docker-compose.yml
docker-compose.prod.yml
docker-compose.test.yml
.dockerignore
```

### Step 3: Explain
After generating, briefly explain the stage strategy and how docker-compose
targets work. Don't over-explain — the developer can read the files.

## Reference
- Read `templates/Dockerfile.php` for the multi-stage PHP build.
- Read `templates/Dockerfile.nginx` for the nginx + asset build.
- Read `templates/default.conf` for the FastCGI proxy config.
- Read `templates/docker-compose.yml` for the dev environment.
- Read `templates/docker-compose.prod.yml` for production overrides.
