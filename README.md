# Business Invoicing (Laravel 13 / PHP 8.4)

Rewrite of the legacy `BusinessInvoicing` PHP 5 app (mysql_* functions, AJAX pages) in Laravel 13.
It runs against the **existing database** — tables (`Customers`, `Orders`, `User`, …) and data are unchanged.

## Features (same as the legacy app)

| Role | Access |
|---|---|
| Admin | Customers (price ranges, invoice Excel, revenue chart, lock / pay orders), Users, Orders, Error codes, Expenses, Profit distribution |
| CSR | Add / list orders, complete orders |
| Designer | Complete orders, own completed-order list |
| QA | Orders (verify designs), designers (add / edit / block), completed-order list with stitch correction |

Order price = legacy rules (`app/Services/PricingService.php`, covered by `tests/Unit/PricingServiceTest.php`).

## What changed from the legacy app

- PHP 8.4 / Laravel 13; all SQL is parameterised (the old code was open to SQL injection).
- Passwords are bcrypt-hashed. Old plain-text passwords keep working and are hashed on first login;
  run `php artisan users:hash-passwords` once to hash everyone immediately.
- "Forgot password" emails a **new** password (old one can no longer be read back).
- Excel exports use PhpSpreadsheet and now require login (the old `invoice-excel.php` was public).
- Flash (SWF) charts replaced with Chart.js.
- CSRF protection, login rate limiting, role checks on every route.
- Database migration (non-destructive): widens `User.Password` to 255, adds `User.remember_token`,
  and adds indexes on `Orders` for fast listings.

## Run locally

Requirements: PHP 8.3+ (8.4 recommended), Composer, MySQL/MariaDB.

```bash
composer install
cp .env.example .env          # set DB_DATABASE / DB_USERNAME / DB_PASSWORD
php artisan key:generate
# import the existing SQL dump into that database, then:
php artisan migrate
php artisan serve
```

## Configuration

`config/binvoice.php` — roles, order statuses, websites/prefixes, owner profit share (30%), page sizes.
`APP_TIMEZONE` defaults to `America/New_York`, matching the timestamps already stored by the old server.
