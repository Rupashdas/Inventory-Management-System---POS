# POS (Point of Sale) - Laravel

A simple Point of Sale (POS) application built with Laravel. This repository contains the backend API, models, migrations, and seeders for managing products, categories, customers, invoices, and users.

## Table of Contents

- Project overview
- Requirements
- Installation
- Environment
- Database (migrations & seeding)
- Default user account
- Running the application
- Running tests
- Useful commands
- Contributing
- License

## Project overview

This POS project provides CRUD APIs and a minimal web interface (where applicable) to manage:

- Products
- Categories
- Customers
- Invoices and invoice products
- Users and authentication

The codebase follows standard Laravel conventions and is intended for local development, testing, and as a starting point for custom extensions.

## Requirements

- PHP 8.1+ (or the version required by the installed Laravel version)
- Composer
- A supported database (MySQL, MariaDB, SQLite, PostgreSQL)
- Node.js and npm (for frontend assets, optional)

## Installation

1. Clone the repository:

```bash
git clone <repo-url>
cd pos
```

2. Install PHP dependencies:

```bash
composer install
```

3. Install JS dependencies and build assets (optional):

```bash
npm install
npm run build
```

4. Create an `.env` file from the example and set the application key:

```bash
cp .env.example .env
php artisan key:generate
```

5. Configure database connection settings in `.env` (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

## Environment

Important `.env` keys you will likely set:

- `APP_NAME`, `APP_URL`
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `MAIL_*` for e-mail delivery (used for OTP or notifications)

## Database (migrations & seeding)

Run migrations and (optionally) seed the database:

```bash
php artisan migrate
php artisan db:seed --class=CategorySeeder
php artisan db:seed
```

Note: The project includes factories and seeders for sample data. You can refresh and reseed with:

```bash
php artisan migrate:fresh --seed
```

## Default user account

The following user account is provided for development/testing and is referenced in the project documentation:

```
{
  "firstName": "John",
  "lastName": "Doe",
  "email": "admin@example.com",
  "password": "password",
  "mobile": "+123456789"
}
```

- Email: `admin@example.com`
- Password: `password`

Warning: This account uses an insecure password for local/testing only. Do not use these credentials in production.

If you want this user created automatically, ensure your seeders create it or run a tinker script, for example:

```bash
php artisan tinker
\App\Models\User::create([
  'firstName' => 'John',
  'lastName' => 'Doe',
  'email' => 'admin@example.com',
  'password' => bcrypt('password'),
  'mobile' => '+123456789'
]);
```

## Running the application

Start the local development server:

```bash
php artisan serve
```

Visit `http://127.0.0.1:8000` (or the `APP_URL` you configured).

If using Docker, Valet, or other envs, use the corresponding run instructions.

## Running tests

- Run PHPUnit tests:

```bash
vendor/bin/phpunit
```

## Useful commands

- `php artisan route:list` — view routes
- `php artisan migrate` — run migrations
- `php artisan db:seed` — run seeders
- `php artisan tinker` — interact with the app in an interactive shell

## Contributing

Contributions are welcome. Please follow these steps:

1. Fork the repository.
2. Create a feature branch.
3. Make changes and add tests where applicable.
4. Open a pull request describing your changes.

## License

This project is open-sourced software. See the `LICENSE` file for details (if present).

---

If you'd like, I can also:

- Add a seeder to create the default admin user automatically.
- Add example `.env` notes or a sample Postman collection for the API endpoints.

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
