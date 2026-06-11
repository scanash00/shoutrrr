# Laravel + React Starter Kit

A batteries-included starting point for building Laravel applications with a React frontend via [Inertia](https://inertiajs.com). It builds on Laravel's official React starter kit and layers on multi-tenancy, a fuller auth surface, social login, a production container image, and a modern tooling chain.

The frontend stack is React 19, TypeScript, [Tailwind v4](https://tailwindcss.com), and the [shadcn/ui](https://ui.shadcn.com) + [radix-ui](https://www.radix-ui.com) component libraries. The backend runs PHP 8.5 and Laravel 13.

## What's different from the base kit

- **Workspaces (multi-tenancy).** First-class workspaces with memberships, roles, and email invitations. Models are workspace-scoped via `HasWorkspaceScope`, and the current workspace is resolved on login.
- **Fuller authentication.** Built on [Laravel Fortify](https://laravel.com/docs/fortify) — registration, password reset, and email verification, plus **two-factor authentication (TOTP)** and **passkeys (WebAuthn)**.
- **Social login.** OAuth sign-in through [Laravel Socialite](https://laravel.com/docs/socialite) (Google) with connected-account linking on top of the standard credentials flow.
- **Production Docker image.** Multi-stage `docker/Dockerfile` running [Laravel Octane](https://laravel.com/docs/octane) on **FrankenPHP** worker mode for high-throughput serving.
- **Typed routes.** [Laravel Wayfinder](https://github.com/laravel/wayfinder) generates TypeScript functions for controllers and named routes, imported from `@/actions` and `@/routes`.
- **Modern tooling** (see below).

## Tooling

| Concern | Tool | Command |
| --- | --- | --- |
| Tests | [Pest](https://pestphp.com) | `composer test` |
| PHP style | [Pint](https://laravel.com/docs/pint) | `composer lint` |
| PHP static analysis | [Larastan](https://github.com/larastan/larastan) (level 7) | `composer types:check` |
| PHP refactoring | [Rector](https://getrector.com) | `composer refactor:check` / `composer refactor` |
| JS package manager | [Bun](https://bun.sh) | `bun install` |
| JS lint | [oxlint](https://oxc.rs) | `bun run lint:check` |
| JS format | [oxfmt](https://oxc.rs) | `bun run format:check` |

Run the full local gate (lint, format, type-check, refactor check, and the Pest suite) with:

```bash
composer ci:check
```

## Getting started

```bash
composer setup   # install deps, copy .env, generate key, migrate, build assets
composer dev      # serve + queue + logs + Vite, all at once
```

## CI

GitHub Actions runs two workflows on push and pull requests:

- **tests** — installs dependencies, builds assets, runs Larastan, and executes the Pest suite on PHP 8.5.
- **linter** — runs Pint, oxfmt, and oxlint.

## License

Open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
