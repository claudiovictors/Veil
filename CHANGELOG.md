# Changelog

All notable changes to this project will be documented in this file.

## [1.4.1] - 2026-10-02

### Breaking

- Logout is now **POST `/logout` only** (was GET). Forms and clients must send CSRF (`@csrf` or `X-CSRF-TOKEN` header).
- Dashboard route name is `dashboard` (layouts no longer reference `dashboard.show`).
- Theme `localStorage` key unified to `theme` (was `slenix-theme` in Veil layouts).

### Added

- Dual-stack install: `--stack=luna|react`, `--force`, `--no-interaction`.
- Interactive stack selection via `Prompt` when `--stack` is omitted (defaults to Luna in non-interactive/CI).
- React multi-page auth (session cookie + CSRF; no SPA router / no Inertia).
- JSON-aware `auth` / `guest` middlewares (`expectsJson()` → 401 / `{ redirect }`).
- Idempotent route injection with `// @veil-routes` … `// @end-veil-routes`.
- Login rate limit: `throttle:5,1` on `POST /login`.
- CSRF token regeneration after login, register, and logout (`CSRF::regenerate()`).
- Stub layout: `src/Stubs/shared/`, `luna/`, `react/`.

### Changed

- User model is published only when `app/Models/User.php` is missing.
- Migrations are no longer published by Veil (project base already ships users migration).
- Install command resolves project root by walking up until it finds `celestial`.
- `VeilServiceProvider::stubsPath()` uses `__DIR__ . '/Stubs'`.

### Fixed

- Layout links used non-existent route name `dashboard.show`.
- CSRF-vulnerable GET logout and GET logout form.
- Theme key mismatch with Slenix welcome page.
- Dead `publishModel` / `publishMigrations` paths that were never called (or pointed at missing stubs).
- Route injection only checked a single marker and was not safely replaceable.

### Security

- Generic login error message (no email enumeration on failed login).
- Registration still uses `unique:users,email` (may reveal existing emails) — intentional for a minimal starter kit; documented in README.
- Session ID regenerated on login/logout via `SessionGuard`.
- Logout is POST-only with CSRF.

---

## [1.4.0]

(previous release notes…)
All notable changes to Slenix Veil will be documented in this file.

## [1.4.0] - 2026-06-24

### Added
- `LoginRequest` and `RegisterRequest` form request stubs now published to `app/Http/Requests/` on install
- Veil logo (`logo.png`) now published to `public/` on install, replacing the default Slenix logo
- `publishFormRequests()` method added to `VeilInstallCommand` to handle form request scaffolding

### Changed
- Welcome view is no longer replaced during install — the project's existing `welcome.luna.php` is preserved
- Dashboard view stub renamed from `dashboard.stub` to `index.stub` for consistency with the `dashboard/index.luna.php` destination
- Settings page (`/settings`) and its view stub removed from scaffolding
- `publishAssets()` now handles both CSS stylesheets and the Veil logo in a single step
- `@version 1.4.0` added to `VeilInstallCommand` class docblock

### Fixed
- `target="_blank"` attribute was incorrectly placed inside the `<i>` icon tag on the GitHub link in the dashboard view
- `redirect()` after successful registration now points to `/dashboard` instead of `/login`
- Missing `return` statement before `redirect()->withFlash()` in `AuthController@login`

---

## [1.3.0] - 2026-05-09

### Added
- `DashboardController` scaffolding published on install
- Settings page (`/settings`) with dedicated view and route
- CSS assets (`style.css` and `auth.css`) now published to `public/css/` on install
- Welcome view is automatically replaced during install with Veil's own landing page

### Changed
- View structure reorganised into `layouts/`, `auth/`, and `dashboard/` subdirectories
- `delete()` method now requires explicit text confirmation (`"delete"`) with distinct, descriptive error messages for each failing step
- Flash messages improved across all auth actions for clarity and consistency
- Full PhpDoc documentation added to `AuthController`, `VeilInstallCommand`, and `routes.stub`
- `publishController()` renamed to `publishControllers()` and now handles both `AuthController` and `DashboardController`

### Fixed
- `DashboardController` stub had wrong extension (`.php` → `.stub`)
- View destination paths updated to match the new directory structure

---

## [1.0.0] - 2025-01-01

### Added
- Initial release of Slenix Veil
- Login and Register scaffolding
- `AuthMiddleware` and `GuestMiddleware`
- Beautiful Luna views (layout, login, register, dashboard)
- Users migration
- Auth routes auto-appended to `routes/web.php`
- `php celestial veil:install` command
- `--force` flag to overwrite existing files