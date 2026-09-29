<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $order->order_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        .muted { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        th { background: #f3f4f6; }
        .num { text-align: right; }
        .totals td { border: none; padding: 3px 8px; }
        .grand td { font-weight: bold; font-size: 14px; border-top: 2px solid #1f2937; }
    </style>
</head>
<body>
@php($c = config('store.currency_symbol'))
<h1>{{ config('app.name') }} — Tax Invoice</h1>
<div class="muted">
    Bill no. {{ $order->order_number }} · {{ $order->created_at->format('d M Y, h:i A') }}<br>
    Customer: {{ $order->customer->name }} &lt;{{ $order->customer->email }}&gt;
</div>

<table>
    <thead>
    <tr>
        <th>Product</th><th>Code</th><th class="num">Qty</th><th class="num">Price</th>
        <th class="num">Tax %</th><th class="num">Tax</th><th class="num">Line total</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($order->items as $item)
        <tr>
            <td>{{ $item->product_name }}</td>
            <td>{{ $item->product_code }}</td>
            <td class="num">{{ $item->quantity }}</td>
            <td class="num">{{ $c }}{{ $item->unit_price }}</td>
            <td class="num">{{ $item->tax_percent }}</td>
            <td class="num">{{ $c }}{{ $item->line_tax }}</td>
            <td class="num">{{ $c }}{{ $item->line_total }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<table class="totals" style="width: 45%; margin-left: 55%;">
    <tr><td>Subtotal</td><td class="num">{{ $c }}{{ $order->subtotal }}</td></tr>
    <tr><td>Tax</td><td class="num">{{ $c }}{{ $order->tax_total }}</td></tr>
    <tr class="grand"><td>Grand total</td><td class="num">{{ $c }}{{ $order->grand_total }}</td></tr>
    @if ($order->amount_paid !== null)
        <tr><td>Paid</td><td class="num">{{ $c }}{{ $order->amount_paid }}</td></tr>
        <tr><td>Change returned</td><td class="num">{{ $c }}{{ $order->change_due }}</td></tr>
    @endif
</table>
</body>
</html>
