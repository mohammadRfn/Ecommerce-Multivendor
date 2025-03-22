<x-mail::message>
<h1 style="text-align: center; font-size: 24px;">
    Congratsulations! You have new Order.
</h1>

<x-mail::button :url="$order->id">
    View Order Details
</x-mail>

<h3 style="font-size: 20px; margin-bottom: 15px;">Order Summary</h3>

<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
    <thead>
        <tr>
            <th style="text-align: left; border-bottom: 1px solid #ddd; padding: 8px;">Product</th>
            <th style="text-align: left; border-bottom: 1px solid #ddd; padding: 8px;">Quantity</th>
            <th style="text-align: left; border-bottom: 1px solid #ddd; padding: 8px;">Price</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($order->items as $item)
        <tr>
            <td style="padding: 8px; border-bottom: 1px solid #ddd;">{{ $item->product->name }}</td>
            <td style="padding: 8px; border-bottom: 1px solid #ddd;">{{ $item->quantity }}</td>
            <td style="padding: 8px; border-bottom: 1px solid #ddd;">${{ number_format($item->price, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<p style="font-size: 18px; font-weight: bold; text-align: right;">
    Total: ${{ number_format($order->total, 2) }}
</p>
</x-mail::message>