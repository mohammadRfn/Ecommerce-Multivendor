<x-mail::message>
    <h1 style="text-align: center; font-size: 24px;">
        Congratulations! Your Order has been Successfully Completed.
    </h1>

    <p style="text-align: center; font-size: 18px;">Thank you for your purchase. Your order has been successfully processed!</p>

    <x-mail::button :url="route('orders.show', $order->id)">
        View Order Details
    </x-mail::button>

    <h3 style="font-size: 20px; margin-bottom: 15px;">Order Summary</h3>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <thead>
            <tr>
                <th style="text-align: left; border-bottom: 1px solid #ddd; padding: 8px;">Order ID</th>
                <th style="text-align: left; border-bottom: 1px solid #ddd; padding: 8px;">Order Date</th>
                <th style="text-align: left; border-bottom: 1px solid #ddd; padding: 8px;">Customer Name</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">#{{ $order->id }}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">{{ $order->created_at->format('F d, Y') }}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">{{ $order->customer_name }}</td>
            </tr>
        </tbody>
    </table>

    <h4 style="font-size: 18px;">Shipping Information:</h4>
    <p style="font-size: 16px;">
        <strong>Shipping Address:</strong> {{ $order->shipping_address }}<br>
        <strong>Email:</strong> {{ $order->customer_email }}<br>
        <strong>Phone:</strong> {{ $order->customer_phone }}
    </p>

    <h4 style="font-size: 18px;">Order Items:</h4>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <thead>
            <tr>
                <th style="text-align: left; border-bottom: 1px solid #ddd; padding: 8px;">Product</th>
                <th style="text-align: left; border-bottom: 1px solid #ddd; padding: 8px;">Quantity</th>
                <th style="text-align: left; border-bottom: 1px solid #ddd; padding: 8px;">Price</th>
                <th style="text-align: left; border-bottom: 1px solid #ddd; padding: 8px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">{{ $item->product->name }}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">{{ $item->quantity }}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${{ number_format($item->price, 2) }}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${{ number_format($item->price * $item->quantity, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="font-size: 18px; font-weight: bold; text-align: right;">
        <strong>Total: ${{ number_format($order->total, 2) }}</strong>
    </p>

    <h4 style="font-size: 18px;">Payment Method:</h4>
    <p style="font-size: 16px;">
        <strong>Payment Method:</strong> {{ $order->payment_method }}<br>
        <strong>Status:</strong> {{ $order->payment_status }}
    </p>

    <h4 style="font-size: 18px;">Shipping Information:</h4>
    <p style="font-size: 16px;">
        <strong>Shipping Status:</strong> {{ $order->shipping_status }}<br>
        <strong>Tracking Number:</strong> {{ $order->tracking_number ?? 'Not yet assigned' }}
    </p>

    <hr style="border-top: 2px solid #ddd; margin: 20px 0;">

    <p style="text-align: center; font-size: 16px;">We will notify you once your order has shipped. If you have any questions, feel free to contact us.</p>

    <p style="text-align: center; font-size: 16px;">Thank you for shopping with us!</p>

    <p style="text-align: center; font-size: 16px;">
        The {{ config('app.name') }} Team
    </p>
</x-mail::message>
