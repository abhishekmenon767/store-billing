# Store Billing — Order & Inventory Mini-System

A Laravel 13 app for a retail counter: it records customer orders against a product catalog and keeps stock in sync.

## Setup

Requirements: PHP 8.3+, Composer, Node 20+, MySQL 8+.

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
```

Create the databases and update the `DB_*` values in `.env` (and `DB_DATABASE` in `phpunit.xml` for tests):

```sql
CREATE DATABASE store_billing;
CREATE DATABASE store_billing_test;
```

```bash
php artisan migrate --seed
php artisan serve          # http://localhost:8000
php artisan queue:work     # sends the order confirmation emails
```

Confirmation emails go to a [Mailtrap](https://mailtrap.io) sandbox inbox: put your Mailtrap SMTP username and password in `MAIL_USERNAME` / `MAIL_PASSWORD`. To skip SMTP, set `MAIL_MAILER=log` and the emails are written to `storage/logs/laravel.log` instead.

## Tests

```bash
php artisan test
```

The tests cover:
- order creation;
- insufficient stock and rollback;
- the last unit being sold only once;
- payment and change;
- validation;
- order history;
- low stock;
- the confirmation job.

`tests/Concurrency` forks real processes against MySQL to check that simultaneous orders for the last unit can't oversell. It needs the `pcntl` extension.

## API

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/orders` | Create an order |
| GET | `/api/orders/{order_number}` | Get one order |
| GET | `/api/customers/{email}/orders` | Customer's order history (paginated) |
| GET | `/api/products` | Product list |
| GET | `/api/products/low-stock?threshold=10` | Products below the stock threshold |
| GET | `/orders/{order_number}/bill.pdf` | PDF bill |

Create order request:

```json
{
  "customer": { "email": "thomas@example.com", "name": "Thomas" },
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 2, "quantity": 5 }
  ],
  "amount_paid": 250
}
```

All responses follow the same format:

```json
{ "success": true, "message": "...", "data": {} }
{ "success": false, "message": "...", "errors": {} }
```

- `201`: order created
- `409`: not enough stock (`errors.shortages` lists the products)
- `422`: validation error

## Structure

- **Controllers** extend a base `Controller` that has the response helpers. They only use resource methods (`index`, `store`, `show`) and call an action.
- **Actions** (`app/Actions`) hold the business logic, e.g. `PlaceOrderAction`, `GetCustomerOrdersAction`, `ListLowStockProductsAction`.
- **Form Requests** handle validation.
- **Models**: `Customer`, `Product`, `Order`, `OrderItem`, `StockMovement`.
- **Job**: `SendOrderConfirmation` is queued after the order is saved and emails a PDF bill.

## Database

- `customers`: name, email (unique)
- `products`: name, code (unique), price, tax_percent, stock (unsigned)
- `orders`: order_number, customer, subtotal, tax_total, grand_total, amount_paid, change_due
- `order_items`: product, quantity, and a copy of the product's name, price and tax at the time of sale
- `stock_movements`: a log of every stock change (sale or restock)

## Handling concurrent orders

`PlaceOrderAction` runs in a database transaction:

1. The product rows are locked with `lockForUpdate()`, in id order so two orders can't deadlock.
2. Stock is checked for every item. If any item is short, the order fails with `409` and nothing is saved.
3. Stock is reduced with `decrement()` only `where stock >= quantity`, and the stock column is unsigned. So even without the lock, stock can't go negative.

If two orders arrive together for the last unit, the second one waits for the lock, then sees stock 0 and fails.

## Assumptions

- No authentication; this is an internal counter tool.
- Customers are matched by email. If the email already exists, the stored name is kept.
- If the same product is sent twice in one order, the quantities are added together.
- "Low stock" means stock strictly below the threshold. The default is 10 (`STORE_LOW_STOCK_THRESHOLD` in `.env`).
- Tax is a single percentage per product, calculated per line and rounded to 2 decimals. Prices are before tax.
- `amount_paid` is optional. When sent, it must cover the grand total, and the change is returned with a note/coin breakdown.
- Order confirmation emails are sent to a Mailtrap sandbox, so no real customer receives them.

## Prompt log

See the [`prompts/`](prompts/) folder.
