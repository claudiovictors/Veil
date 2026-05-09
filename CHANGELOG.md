# Changelog

All notable changes to Slenix Veil will be documented in this file.

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
- AuthMiddleware and GuestMiddleware
- Beautiful Luna views (layout, login, register, dashboard)
- Users migration
- Auth routes auto-appended to `routes/web.php`
- `php celestial veil:install` command
- `--force` flag to overwrite existing files