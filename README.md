# Inventory Management System &mdash; POS

A point-of-sale and stock-control application built with Laravel 10. One account
is one shop: its own catalogue, customers, invoices and stock, kept separate from
every other account's at the query level.

- **Till** &mdash; build a sale from the shelf, apply a discount, take payment.
- **Stock** &mdash; every product carries a balance and its own reorder point.
  Selling decrements it, deleting an invoice puts it back, and every movement is
  written to a ledger that explains the balance.
- **Reports** &mdash; sales over a date range, and stock on hand, both as PDFs.

## Requirements

- PHP 8.1+
- Composer
- **MySQL 8** (see the note under Testing &mdash; SQLite is not a safe substitute here)
- Node.js and npm, only if you want to rebuild front-end assets

## Installation

```bash
git clone <repo-url>
cd inventory-pos
composer install
cp .env.example .env
php artisan key:generate
```

Then edit `.env`:

- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` for your MySQL server.
- **`JWT_KEY`** &mdash; required, and must be at least 32 characters. Sessions are
  signed JWTs held in a cookie rather than Laravel's auth guard, so without this
  the application cannot sign anybody in.

Create the database and run the migrations:

```bash
mysql -u root -e "CREATE DATABASE inventory_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed
```

## Running it

```bash
php artisan serve
```

Then open `http://127.0.0.1:8000`.

## Testing

```bash
vendor/bin/phpunit
```

The suite needs its own database:

```bash
mysql -u root -e "CREATE DATABASE inventory_pos_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

It runs against **MySQL, not SQLite**, deliberately. This project was originally
developed against SQLite, which does not enforce `VARCHAR` lengths &mdash; so a
`password VARCHAR(50)` column looked fine locally and rejected every 60-character
bcrypt hash the moment it met MySQL. The queries are MySQL's too (`DATE()`,
`CAST(x AS UNSIGNED)`, `SELECT ... FOR UPDATE`). A suite on SQLite would have
agreed with the bug rather than caught it.

## How stock works

`products.stock` is the balance; `stock_movements` is how that balance came to
be. Nothing writes one without the other:

| Reason          | Written when                                      |
| --------------- | ------------------------------------------------- |
| `purchase`      | opening stock, or a restock                       |
| `sale`          | a sale is recorded                                |
| `sale_reversal` | an invoice is deleted and its stock goes back     |
| `adjustment`    | a manual correction                               |

Stock is deliberately **not** editable on the product form. It moves through
restocking and through sales, both of which write a ledger row; letting a form
overwrite the balance would put the two permanently out of step.

Sales take a row lock (`lockForUpdate`) while checking and decrementing, so two
tills selling the last unit at the same moment cannot both succeed.

## Where the money is calculated

On the server, from the prices in the database. The browser sends only which
products, how many, which customer, and what discount percentage was agreed;
the subtotal, discount, tax and payable amount are all computed in
`InvoiceController` and written from there. Totals posted by a client are
ignored.

## API

All routes below sit behind `TokenVerificationMiddleware`, which reads the JWT
from the `token` cookie and puts `userID` and `userEmail` onto the request.

| Method | Path                     | Purpose                              |
| ------ | ------------------------ | ------------------------------------ |
| POST   | `/user-registration`     | Create an account                    |
| POST   | `/user-login`            | Sign in, sets the token cookie       |
| POST   | `/send-otp`              | Email a reset code                   |
| POST   | `/verify-otp`            | Check the reset code                 |
| POST   | `/reset-password`        | Set a new password                   |
| GET    | `/summary`               | Dashboard counters                   |
| GET    | `/sales-trend`           | Takings per day, last 14 days        |
| GET    | `/low-stock`             | Products at or below reorder point   |
| GET    | `/top-products`          | Best sellers by units                |
| GET    | `/recent-invoices`       | Five most recent sales               |
| GET    | `/list-product`          | Catalogue with stock                 |
| POST   | `/create-product`        | Add a product (multipart)            |
| POST   | `/update-product`        | Edit a product (multipart)           |
| POST   | `/delete-product`        | Remove a product                     |
| POST   | `/restock-product`       | Add stock, writes a movement         |
| POST   | `/product-stock-history` | Movement ledger for one product      |
| GET    | `/list-category`         | Categories with product counts       |
| GET    | `/list-customer`         | Customers                            |
| POST   | `/invoice-create`        | Record a sale                        |
| GET    | `/invoice-select`        | Invoices, newest first               |
| POST   | `/invoice-details`       | One invoice with its lines           |
| POST   | `/invoice-delete`        | Delete a sale and restore its stock  |
| GET    | `/sales-report/{from}/{to}` | Sales PDF for a date range        |
| GET    | `/stock-report`          | Stock-on-hand PDF                    |

Category, customer and product endpoints follow the same
create / list / by-id / update / delete shape.

## Demo account

If you seed the database, sign in with:

```
admin@example.com / password
```

This is a development account with a deliberately weak password. Do not ship it.

## License

MIT.
