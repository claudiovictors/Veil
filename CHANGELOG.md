# Changelog

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