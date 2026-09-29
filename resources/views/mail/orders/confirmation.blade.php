@php($currency = config('store.currency_symbol'))
<x-mail::message>
# Thank you, {{ $order->customer->name }}!

Your order **{{ $order->order_number }}** was placed on {{ $order->created_at->format('d M Y, h:i A') }}.

<x-mail::table>
| Product | Qty | Price | Line total |
|:--------|:---:|------:|-----------:|
@foreach ($order->items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | {{ $currency }}{{ $item->unit_price }} | {{ $currency }}{{ $item->line_total }} |
@endforeach
</x-mail::table>

Subtotal: {{ $currency }}{{ $order->subtotal }}<br>
Tax: {{ $currency }}{{ $order->tax_total }}<br>
**Grand total: {{ $currency }}{{ $order->grand_total }}**

Your bill is attached as a PDF.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
