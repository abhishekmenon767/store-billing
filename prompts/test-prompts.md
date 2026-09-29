# Test prompts

## 1. Test case ideas

> Give me a list of test cases for my Laravel store billing app: order creation, stock checks, payment/change, validation, order history, low stock, and the queued confirmation email. Group them by feature and include edge cases, not just the happy path.

## 2. Order creation

> Write Pest feature tests for `POST /api/orders`: check subtotal, tax and grand total are correct, stock is reduced, the `SendOrderConfirmation` job is queued, and an existing customer is reused by email. Use factories, `RefreshDatabase` and `Queue::fake()`.

## 3. Stock edge cases

> Add tests for insufficient stock: the API should return 409 with the short products, and no order, stock change or job should happen. Also test that if one item in an order is short, the whole order is rolled back, and that the last unit can only be sold once.

## 4. Validation and payment

> Add tests for validation: invalid email, missing name, unknown product and zero quantity should return 422. Also test that the change is calculated when `amount_paid` is sent, and that an amount lower than the grand total is rejected.

## 5. History, low stock, email

> Write tests for `GET /api/customers/{email}/orders` (newest first, 404 for an unknown email), `GET /api/products/low-stock` (default threshold from config and a `?threshold=` override), and check the confirmation job sends the email only once even if it runs twice.

## 6. Concurrency

> Requirement: if two orders for the last unit come in at the same time, only one should succeed. Write a Pest test that uses `pcntl_fork()` to place several orders at the same moment against MySQL and checks that exactly one succeeds and stock ends at 0. Keep the test simple.

## 7. Separate test database

> Make sure the tests run on a separate MySQL database (`store_billing_test`) so `migrate:fresh` never wipes my real data.
