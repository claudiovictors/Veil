# <p align="center">Slenix Veil</p>

<p align="center">
<img src="https://raw.githubusercontent.com/claudiovictors/veil/main/art/logo.svg" width="120" alt="Veil Logo">
</p>

<p align="center">
<a href="#"><img src="https://img.shields.io/badge/PHP-8.1%2B-blue" alt="PHP Version"></a>
<a href="#"><img src="https://img.shields.io/badge/Version-1.0-green" alt="Version"></a>
<a href="#"><img src="https://img.shields.io/badge/Slenix-2.5%2B-purple" alt="Slenix Version"></a>
<a href="#"><img src="https://img.shields.io/badge/License-MIT-orange" alt="License"></a>
</p>

---

## About Veil

Veil is the official authentication scaffolding package for the Slenix Framework.
Inspired by Laravel Breeze, it gives you a clean and minimal auth starter in seconds.

Veil is a **code generator** — it copies controllers, middlewares, views and migrations
directly into your project, giving you full ownership of the generated code from day one.

What Veil provides out of the box:

* Login and Register pages
* AuthMiddleware and GuestMiddleware
* Users migration
* Beautiful Luna views with minimal CSS
* Auth routes auto-appended to `routes/web.php`
* Zero runtime overhead — install and forget

---

## Installation

Install the package via Composer:

```bash
composer require slenix/veil
```

Then run the installer via the Celestial CLI:

```bash
php celestial veil:install
```

To overwrite already published files:

```bash
php celestial veil:install --force
```

---

## What gets generated

After running `veil:install`, the following files are added to your project:

```
app/
├── Controllers/
│   └── AuthController.php
└── Middlewares/
    ├── AuthMiddleware.php
    └── GuestMiddleware.php

views/
└── auth/
    ├── layout.luna.php
    ├── login.luna.php
    ├── register.luna.php
    └── dashboard.luna.php

database/
└── migrations/
    └── xxxx_xx_xx_xxxxxx_create_users_table.php

routes/
└── web.php  ← auth routes appended automatically
```

---

## After install

Run the migration to create the users table:

```bash
php celestial migrate
```

Then visit `/login` or `/register` in your browser.

---

## How it works

Veil is purely a scaffolding tool. When you run `veil:install`:

1. Stubs are **copied** into your project as real `.php` files
2. Routes are **appended** to `routes/web.php`
3. The package itself is **no longer needed at runtime**

This means Veil has zero runtime overhead — it is a dev-time tool,
just like Laravel Breeze.

---

## Requirements

| Dependency | Version  |
|------------|----------|
| PHP        | >= 8.1   |
| Slenix     | >= 2.5   |

---

## Roadmap

- [x] Login / Register / Logout scaffolding
- [x] AuthMiddleware and GuestMiddleware
- [x] Beautiful Luna views
- [x] Users migration
- [ ] Full auth() integration (Slenix 2.6)
- [ ] Password reset flow
- [ ] Email verification

---

## Security Vulnerabilities

If you discover a security vulnerability within Veil, please report it
responsibly by opening a private issue or contacting the maintainers.

All security vulnerabilities will be handled promptly.

---

## License

Veil is open-source software licensed under the **MIT License**.