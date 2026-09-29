<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Order · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">
<div x-data="billing" x-cloak class="min-h-screen">
    <header class="bg-slate-800 text-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6">
            <h1 class="text-xl font-semibold">Store Billing <span class="text-slate-400">—</span> New Order</h1>
            <span class="hidden text-sm text-slate-400 sm:inline">{{ now()->format('D, d M Y') }}</span>
        </div>
    </header>

    <main class="mx-auto grid max-w-7xl gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[1fr_340px]">
        <div class="space-y-6">
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-base font-semibold">Customer</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-medium text-slate-600">Email</span>
                        <input type="email" x-model="email" @change="lookupCustomer" @keydown.enter.prevent="lookupCustomer"
                               placeholder="e.g. thomas@example.com"
                               class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <span class="mt-1 block text-xs" :class="{
                            'text-slate-400': lookupState === 'loading',
                            'text-emerald-600': lookupState === 'found',
                            'text-amber-600': lookupState === 'new'}"
                              x-text="{loading: 'Looking up…', found: `Returning customer · ${customer?.orders_count} order(s)`, new: 'New customer — please enter a name', idle: ''}[lookupState]"></span>
                        <template x-for="msg in errors['customer.email'] ?? []"><span class="block text-xs text-red-600" x-text="msg"></span></template>
                    </label>
                    <label class="block">
                        <span class="text-sm font-medium text-slate-600">Name</span>
                        <input type="text" x-model="name" :readonly="lookupState === 'found'" placeholder="auto-filled if email exists"
                               class="mt-1 w-full rounded-lg px-3 py-2 ring-1 ring-slate-300 read-only:bg-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <template x-for="msg in errors['customer.name'] ?? []"><span class="block text-xs text-red-600" x-text="msg"></span></template>
                    </label>
                </div>
                <template x-if="recentOrders.length">
                    <div class="mt-4 text-sm">
                        <p class="mb-1 font-medium text-slate-600">Recent orders</p>
                        <ul class="divide-y divide-slate-100 rounded-lg border border-slate-100">
                            <template x-for="o in recentOrders" :key="o.id">
                                <li class="flex justify-between px-3 py-1.5">
                                    <a :href="o.links.bill_pdf" target="_blank" class="text-blue-600 hover:underline" x-text="o.order_number"></a>
                                    <span class="text-slate-500" x-text="new Date(o.created_at).toLocaleDateString()"></span>
                                    <span class="font-medium" x-text="'₹' + o.grand_total"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </template>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-base font-semibold">Products</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-100 text-left text-slate-600">
                        <tr>
                            <th class="rounded-l-lg px-3 py-2">Product</th>
                            <th class="px-3 py-2">Qty</th>
                            <th class="px-3 py-2 text-right">Price</th>
                            <th class="px-3 py-2 text-right">Tax</th>
                            <th class="px-3 py-2 text-right">Line Total</th>
                            <th class="rounded-r-lg px-3 py-2"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        <template x-for="(line, i) in lines" :key="line.product.id">
                            <tr>
                                <td class="px-3 py-2">
                                    <span x-text="line.product.name"></span>
                                    <span class="block text-xs" :class="exceedsStock(line) ? 'text-red-600' : 'text-slate-400'"
                                          x-text="`${line.product.stock} in stock`"></span>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" min="1" x-model.number="line.quantity"
                                           class="w-20 rounded-md px-2 py-1 ring-1 ring-slate-300" :class="exceedsStock(line) && 'ring-red-400'">
                                </td>
                                <td class="px-3 py-2 text-right" x-text="'₹' + line.product.price"></td>
                                <td class="px-3 py-2 text-right text-slate-500" x-text="line.product.tax_percent + '%'"></td>
                                <td class="px-3 py-2 text-right font-medium" x-text="money(linePaise(line).total)"></td>
                                <td class="px-3 py-2 text-right">
                                    <button type="button" @click="removeLine(i)" class="text-slate-400 hover:text-red-600" title="Remove">✕</button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="!lines.length"><td colspan="6" class="px-3 py-6 text-center text-slate-400">No products yet</td></tr>
                        </tbody>
                    </table>
                </div>

                <form @submit.prevent="addLine" class="mt-4 flex flex-wrap items-center gap-3">
                    <select x-model="pickProductId" class="min-w-0 flex-1 rounded-lg px-3 py-2 ring-1 ring-slate-300">
                        <option value="">+ Select a product to add…</option>
                        <template x-for="p in products" :key="p.id">
                            <option :value="p.id" :disabled="p.stock === 0"
                                    x-text="`${p.name} (${p.code}) — ₹${p.price} · ${p.stock} left`"></option>
                        </template>
                    </select>
                    <input type="number" min="1" x-model.number="pickQuantity" class="w-20 rounded-lg px-3 py-2 ring-1 ring-slate-300" aria-label="Quantity">
                    <button type="submit" :disabled="!pickProductId"
                            class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700 disabled:opacity-40">+ Add Product</button>
                </form>
            </section>

            <section class="grid gap-6 md:grid-cols-[1fr_260px]">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-base font-semibold">Payment</h2>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt>Subtotal</dt><dd class="font-semibold" x-text="money(totals.subtotal)"></dd></div>
                        <div class="flex justify-between"><dt>Tax</dt><dd class="font-semibold" x-text="money(totals.tax)"></dd></div>
                        <div class="flex justify-between text-base"><dt>Grand Total</dt><dd class="font-bold" x-text="money(totals.total)"></dd></div>
                    </dl>
                    <hr class="my-4 border-dashed border-slate-300">
                    <label class="block">
                        <span class="text-sm font-medium text-slate-600">Amount given by customer</span>
                        <input type="number" min="0" step="0.01" x-model="amountGiven" placeholder="₹0.00"
                               class="mt-1 w-full rounded-lg px-3 py-2 ring-1 ring-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <template x-for="msg in errors['amount_paid'] ?? []"><span class="block text-xs text-red-600" x-text="msg"></span></template>
                    </label>
                    <div class="mt-3 flex flex-wrap items-baseline justify-between gap-2 text-sm" x-show="balance !== null">
                        <span class="font-semibold">Balance to return:</span>
                        <span :class="balance < 0 ? 'text-red-600 font-semibold' : 'text-slate-600'">
                            <span x-text="balance < 0 ? `Short by ${money(-balance)}` : money(balance)"></span>
                            <span x-show="balance > 0" x-text="'→ ' + balanceBreakdown"></span>
                        </span>
                    </div>
                </div>

                <div class="space-y-3">
                    <button type="button" @click="generateBill" :disabled="!canSubmit"
                            class="w-full rounded-xl bg-emerald-700 px-4 py-4 text-lg font-semibold text-white shadow-sm hover:bg-emerald-800 disabled:opacity-40">
                        <span x-text="submitting ? 'Generating…' : 'Generate Bill'"></span>
                    </button>
                    <p class="text-xs text-slate-500">Saves the order, deducts stock, shows the bill here and emails a PDF copy to the customer (queued).</p>

                    <div x-show="errorMessage" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                        <p class="font-medium" x-text="errorMessage"></p>
                        <ul class="mt-1 list-disc pl-5">
                            <template x-for="s in shortages" :key="s.product_id">
                                <li x-text="`${s.name}: requested ${s.requested}, only ${s.available} left`"></li>
                            </template>
                        </ul>
                    </div>
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border-2 border-amber-400 bg-amber-50 p-5">
                <h2 class="mb-3 font-semibold text-amber-900">⚠ Low Stock Alert</h2>
                <p class="mb-2 text-xs text-amber-700" x-show="lowStockThreshold" x-text="`Below ${lowStockThreshold} units`"></p>
                <ul class="space-y-1.5 text-sm text-amber-900">
                    <template x-for="p in lowStock" :key="p.id">
                        <li class="flex justify-between">
                            <span x-text="'• ' + p.name"></span>
                            <span class="font-medium" :class="p.stock === 0 && 'text-red-600'" x-text="p.stock === 0 ? 'Out of stock' : `${p.stock} units left`"></span>
                        </li>
                    </template>
                    <li x-show="!lowStock.length" class="text-amber-700">All products are well stocked.</li>
                </ul>
            </section>

            <template x-if="bill">
                <section class="rounded-xl border border-emerald-300 bg-white p-5 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="font-semibold text-emerald-800">Bill generated</h2>
                        <button type="button" @click="bill = null" class="text-slate-400 hover:text-slate-600">✕</button>
                    </div>
                    <p class="text-sm"><span class="font-mono" x-text="bill.order_number"></span></p>
                    <p class="text-sm text-slate-500" x-text="`${bill.customer.name} · ${bill.customer.email}`"></p>
                    <ul class="my-3 divide-y divide-slate-100 text-sm">
                        <template x-for="item in bill.items" :key="item.product_id">
                            <li class="flex justify-between py-1">
                                <span x-text="`${item.product_name} × ${item.quantity}`"></span>
                                <span x-text="'₹' + item.line_total"></span>
                            </li>
                        </template>
                    </ul>
                    <dl class="space-y-1 text-sm">
                        <div class="flex justify-between"><dt>Subtotal</dt><dd x-text="'₹' + bill.subtotal"></dd></div>
                        <div class="flex justify-between"><dt>Tax</dt><dd x-text="'₹' + bill.tax_total"></dd></div>
                        <div class="flex justify-between font-bold"><dt>Grand total</dt><dd x-text="'₹' + bill.grand_total"></dd></div>
                        <template x-if="bill.amount_paid !== null">
                            <div class="flex justify-between text-slate-600"><dt>Change</dt><dd x-text="'₹' + bill.change_due"></dd></div>
                        </template>
                    </dl>
                    <a :href="bill.links.bill_pdf" target="_blank"
                       class="mt-4 block rounded-lg bg-slate-800 px-3 py-2 text-center text-sm font-medium text-white hover:bg-slate-900">Download PDF</a>
                    <p class="mt-2 text-xs text-slate-500" x-text="`Confirmation email queued for ${bill.customer.email}`"></p>
                </section>
            </template>
        </aside>
    </main>
</div>
</body>
</html>
