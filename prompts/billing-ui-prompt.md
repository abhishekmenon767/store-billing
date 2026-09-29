# Prompt: Build the billing screen from the wireframe

## Short version

> I have a wireframe for a "Store Billing — New Order" screen (attached). Build it as a Blade page in my Laravel app using Tailwind and Alpine.js.
>
> Sections:
> - Customer: email + name. When the email is entered, look up the customer and auto-fill the name if they exist.
> - Products: a table (Product, Qty, Price, Line Total), a dropdown to add a product, and an "+ Add Product" button.
> - Low Stock Alert box on the right, loaded from `/api/products/low-stock`.
> - Payment: subtotal, tax, grand total, an "amount given by customer" input, and the balance to return with a note breakdown (e.g. ₹23 → 1×20 + 1×2 + 1×1).
> - A green "Generate Bill" button that calls `POST /api/orders` and shows the bill on the page with a PDF download link.
>
> Show validation and out-of-stock errors from the API. Keep it simple and clean; the wireframe is only a layout guide.

## Detailed version

> I've attached a low-fidelity wireframe of a "Store Billing — New Order" screen. Build it as a single Blade page (`resources/views/billing.blade.php`) in my Laravel 13 app using Tailwind CSS (already set up with Vite) and Alpine.js for interactivity. Don't use Vue/React or any component library.
>
> **Layout (desktop: main column + right sidebar; mobile: single column):**
> 1. **Header bar:** dark background, title "Store Billing — New Order", today's date on the right.
> 2. **Customer card:** Email and Name inputs side by side. When the email field loses focus, call `GET /api/customers/{email}/orders?per_page=3`.
>    - On 200: auto-fill the name, make it read-only, show "Returning customer · N orders" and list the last 3 orders (order number linked to its PDF, date, total).
>    - On 404: show "New customer — please enter a name".
> 3. **Products card:** a table with Product (with "X in stock" below it), Qty (editable number input), Price, Tax %, Line Total and a remove (✕) button. Below it, a product dropdown loaded from `GET /api/products` (disable out-of-stock items), a quantity input and a blue "+ Add Product" button. Adding a product that's already in the table increases its quantity instead of adding a new row. Highlight the quantity in red if it's more than the available stock.
> 4. **Payment card:** Subtotal, Tax and Grand Total, calculated live as items change. Then a dashed divider, an "Amount given by customer" input and a "Balance to return" line that shows the change and a note/coin breakdown (₹500, 200, 100, 50, 20, 10, 5, 2, 1, e.g. `₹23.00 → 1×₹20 + 1×₹2 + 1×₹1`). If the amount is too low, show "Short by ₹X" in red.
> 5. **Generate Bill button:** big and green, next to the payment card, with a small caption: "Saves the order, deducts stock, shows the bill here and emails a PDF copy to the customer". Disable it until there's an email, at least one product, and enough cash (if cash was entered).
> 6. **Low Stock Alert sidebar:** amber-bordered card loaded from `GET /api/products/low-stock`. Show the threshold and each product with "N units left", or "Out of stock" in red.
>
> **Behaviour:**
> - Calculate the live totals the same way the backend does: per line, `round(price × qty × tax% / 100, 2)`, then add up the lines. The server response is still what gets saved.
> - "Generate Bill" sends `POST /api/orders` with `{ customer: {email, name}, items: [{product_id, quantity}], amount_paid }`.
>   - On **201:** show a "Bill generated" card in the sidebar (order number, customer, items, totals, change, "Download PDF" button using `data.links.bill_pdf`, and "Confirmation email queued for …"), clear the form, and reload the products and low-stock list.
>   - On **409:** show the message and list each `errors.shortages` entry as "Name: requested X, only Y left", then reload the products so the stock numbers are fresh.
>   - On **422:** show each `errors[field]` message under the matching input.
> - Every API response looks like `{ success, message, data, meta?, errors? }`.
>
> Put the Alpine component in `resources/js/billing.js` and register it in `resources/js/app.js`. Keep the markup readable, with no unnecessary comments. Make it clean and modern, but the wireframe is only a layout guide, so don't copy its exact styling.
