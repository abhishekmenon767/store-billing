const DENOMINATIONS = [500, 200, 100, 50, 20, 10, 5, 2, 1];

const toPaise = (value) => Math.round(Number(value || 0) * 100);
const percentOf = (paise, basisPoints) => Math.floor((paise * basisPoints + 5000) / 10000);
const money = (paise) => '₹' + (paise / 100).toFixed(2);

async function api(method, url, body) {
    const response = await fetch(url, {
        method,
        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
        body: body ? JSON.stringify(body) : undefined,
    });
    const data = response.status === 204 ? null : await response.json().catch(() => null);
    return { ok: response.ok, status: response.status, data };
}

export default () => ({
    products: [],
    lowStock: [],
    lowStockThreshold: null,

    email: '',
    name: '',
    customer: null,
    recentOrders: [],
    lookupState: 'idle',

    lines: [],
    pickProductId: '',
    pickQuantity: 1,

    amountGiven: '',
    submitting: false,
    errors: {},
    errorMessage: '',
    shortages: [],
    bill: null,

    async init() {
        await Promise.all([this.loadProducts(), this.loadLowStock()]);
    },

    async loadProducts() {
        const { data } = await api('GET', '/api/products');
        this.products = data?.data ?? [];
    },

    async loadLowStock() {
        const { data } = await api('GET', '/api/products/low-stock');
        this.lowStock = data?.data ?? [];
        this.lowStockThreshold = data?.meta?.threshold;
    },

    async lookupCustomer() {
        const email = this.email.trim();
        this.customer = null;
        this.recentOrders = [];
        if (!email || !email.includes('@')) {
            this.lookupState = 'idle';
            return;
        }

        this.lookupState = 'loading';
        const { ok, data } = await api('GET', `/api/customers/${encodeURIComponent(email)}/orders?per_page=3`);
        if (ok) {
            this.customer = data.meta.customer;
            this.name = data.meta.customer.name;
            this.recentOrders = data.data;
            this.lookupState = 'found';
        } else {
            this.lookupState = 'new';
        }
    },

    productById(id) {
        return this.products.find((p) => p.id === Number(id));
    },

    addLine() {
        const product = this.productById(this.pickProductId);
        const quantity = parseInt(this.pickQuantity, 10);
        if (!product || !(quantity > 0)) return;

        const existing = this.lines.find((l) => l.product.id === product.id);
        existing ? (existing.quantity += quantity) : this.lines.push({ product, quantity });

        this.pickProductId = '';
        this.pickQuantity = 1;
    },

    removeLine(index) {
        this.lines.splice(index, 1);
    },

    linePaise(line) {
        const subtotal = toPaise(line.product.price) * line.quantity;
        const tax = percentOf(subtotal, toPaise(line.product.tax_percent));
        return { subtotal, tax, total: subtotal + tax };
    },

    get totals() {
        return this.lines.reduce(
            (acc, line) => {
                const l = this.linePaise(line);
                return { subtotal: acc.subtotal + l.subtotal, tax: acc.tax + l.tax, total: acc.total + l.total };
            },
            { subtotal: 0, tax: 0, total: 0 },
        );
    },

    get balance() {
        if (this.amountGiven === '' || this.amountGiven === null) return null;
        return toPaise(this.amountGiven) - this.totals.total;
    },

    get balanceBreakdown() {
        if (this.balance === null || this.balance < 0) return '';
        let rupees = Math.floor(this.balance / 100);
        const parts = [];
        for (const d of DENOMINATIONS) {
            const count = Math.floor(rupees / d);
            if (count) parts.push(`${count}×₹${d}`);
            rupees %= d;
        }
        const paise = this.balance % 100;
        if (paise) parts.push(`${paise}p`);
        return parts.join(' + ');
    },

    get canSubmit() {
        return !this.submitting && this.email.trim() && this.lines.length && (this.balance === null || this.balance >= 0);
    },

    exceedsStock(line) {
        return line.quantity > line.product.stock;
    },

    async generateBill() {
        this.submitting = true;
        this.errors = {};
        this.errorMessage = '';
        this.shortages = [];

        const payload = {
            customer: { email: this.email.trim(), name: this.name.trim() || null },
            items: this.lines.map((l) => ({ product_id: l.product.id, quantity: l.quantity })),
        };
        if (this.amountGiven !== '') payload.amount_paid = Number(this.amountGiven).toFixed(2);

        const { ok, status, data } = await api('POST', '/api/orders', payload);
        this.submitting = false;

        if (ok) {
            this.bill = data.data;
            this.resetForm();
            await Promise.all([this.loadProducts(), this.loadLowStock()]);
            return;
        }

        this.errorMessage = data?.message ?? `Request failed (${status}).`;
        if (status === 409) {
            this.shortages = data.errors?.shortages ?? [];
            await this.loadProducts();
            this.lines.forEach((l) => (l.product = this.productById(l.product.id) ?? l.product));
        }
        if (status === 422) this.errors = data.errors ?? {};
    },

    resetForm() {
        this.email = '';
        this.name = '';
        this.customer = null;
        this.recentOrders = [];
        this.lookupState = 'idle';
        this.lines = [];
        this.amountGiven = '';
    },

    money,
    toPaise,
});
