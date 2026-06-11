# Deployments — Docker + Laravel Octane (FrankenPHP)

This project ships production Docker images built on
[serversideup/php](https://serversideup.net/open-source/docker-php/) FrankenPHP images,
running the web tier through **Laravel Octane (FrankenPHP worker mode)**. There are two
deployment shapes, both built from one multi-stage `docker/Dockerfile`.

## Two targets — pick the right one

- **Standalone** (`compose.standalone.yaml`, Dockerfile target `standalone`) — one image,
  run as separate containers: `app` (Octane web), `worker` (`queue:work`), `scheduler`
  (`schedule:work`), plus optional `ssr`/`postgres`/`redis`. This is the default/preferred
  shape and matches serversideup's one-process-per-container model. Scale processes
  independently.
- **All-in-one** (`compose.all-in-one.yaml`, Dockerfile target `all-in-one`) — a single
  container where **supervisord** (PID 1, runs as `www-data`) supervises Octane + worker +
  scheduler + a guarded SSR program. Use when one container is required (simple hosts,
  some PaaS).

## Critical facts (do not "fix" these without re-checking)

- **The frankenphp image has NO s6-overlay** (`/init`, `/command/execlineb`, `s6-svscan`,
  `s6-rc` are all absent — s6 only exists in serversideup's `fpm-nginx`/`fpm-apache`
  variants). The all-in-one target therefore uses **supervisord**, not s6. Do not reintroduce
  s6 service files for this image — they are inert. Config: `docker/all-in-one/supervisord.conf`.
- **Octane uses the image's bundled `frankenphp` binary.** Just `composer require laravel/octane`
  + `octane:start --server=frankenphp`. Do not download a FrankenPHP binary into the project
  (`/frankenphp`, `frankenphp-worker.php`, `**/caddy` are gitignored). `OCTANE_SERVER=frankenphp`.
- **`config/octane.php` is a vendor-published stub** and is excluded from Rector
  (`rector.php` `withSkip`). Don't hand-edit it to satisfy lint/refactor tools.
- **Wayfinder + Docker:** the `wayfinder()` Vite plugin shells out to `php artisan` on every
  `vite build`. The Docker `assets` stage (bun, no PHP) sets `SKIP_WAYFINDER_GENERATE=true`,
  which `vite.config.ts` honors to drop the plugin. The TS is generated in the PHP `vendor`
  stage and copied in. Keep that gate when touching `vite.config.ts`.

## Build & run

```bash
# Build (either target)
docker build -f docker/Dockerfile --target standalone  -t laravel-template:standalone .
docker build -f docker/Dockerfile --target all-in-one   -t laravel-template:aio .

# Standalone, SQLite default (app + worker + scheduler)
APP_KEY="base64:..." docker compose -f compose.standalone.yaml up -d --build
docker compose -f compose.standalone.yaml exec app php artisan migrate --force

# All-in-one
APP_KEY="base64:..." docker compose -f compose.all-in-one.yaml up -d --build
```

Health: the app uses serversideup's native `healthcheck-octane`; worker/scheduler use
`healthcheck-queue`/`healthcheck-schedule`. Web serves on container port `8080`.

## Datastore — SQLite (default) or Postgres + Redis

- Default is **SQLite** + the **database** driver for cache/queue/session. Zero external
  services; the SQLite file lives on a named volume at
  `/var/www/html/database/sqlite/database.sqlite` (created by `docker/shared/entrypoint.d/10-init-app.sh`).
- For **Postgres + Redis**, enable the compose profiles and override env:
  ```
  docker compose -f compose.standalone.yaml --profile postgres --profile redis up -d
  ```
  In `.env` set `DB_CONNECTION=pgsql`, `DB_HOST=postgres`, **`DB_DATABASE=laravel`**
  (the sqlite-path default is invalid for pgsql — this override is required),
  `DB_USERNAME=laravel`, `DB_PASSWORD=secret`, `CACHE_STORE=redis`,
  `QUEUE_CONNECTION=redis`, `REDIS_HOST=redis`. The `redis` PHP extension is in the image.
- Run migrations after first boot; the database cache/queue/session drivers need their tables.

## Inertia SSR (off by default, runtime-toggleable)

- The SSR bundle is always built (`bun run build:ssr` in the `assets` stage), but SSR is
  controlled at runtime by `INERTIA_SSR_ENABLED` (default `false`, see `config/inertia.php`).
- Standalone: enable the `ssr` profile (or run the `ssr` service). All-in-one: set
  `INERTIA_SSR_ENABLED=true` — the supervised `ssr` program boots `inertia:start-ssr --runtime=bun`
  instead of idling.

## Conventions when changing the Docker setup

- Keep `standalone` and `all-in-one` process commands in sync (`queue:work --tries=3 --max-time=3600`,
  `schedule:work`, the octane flags). The all-in-one supervisord programs mirror the standalone
  service commands.
- New runtime env knobs should be documented in `.env.example` (Docker section) and wired
  through the compose `x-environment` anchors.
- Keep the image non-root (`www-data`); rely on Docker named-volume initialization (which copies
  image dir ownership) rather than chowning volumes at runtime.
