<html lang="en">
<body style="font-family: Arial, sans-serif; background: #f5f7fb; color: #111827; margin: 0; padding: 24px;">
    <div style="max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 18px; padding: 32px;">
        <p style="margin: 0 0 8px; color: #6b7280;">Luki Online</p>
        <h1 style="margin: 0 0 18px; font-size: 28px;">Your booking is complete</h1>
        <p style="margin: 0 0 22px; line-height: 1.6;">
            Thanks for booking with Luki. Here is a quick summary of your completed service.
        </p>

        <div style="padding: 18px; border-radius: 16px; background: #f8fafc; border: 1px solid #e5e7eb;">
            <p style="margin: 0 0 10px;"><strong>Service:</strong> {{ $order->service_name_snapshot ?? $order->service?->name ?? 'Service' }}</p>
            <p style="margin: 0 0 10px;"><strong>Tier:</strong> {{ $order->service_tier_name_snapshot ?? $order->serviceTier?->name ?? 'Standard' }}</p>
            <p style="margin: 0 0 10px;"><strong>Provider:</strong> {{ $order->providerProfile?->display_name ?? 'Assigned provider' }}</p>
            <p style="margin: 0 0 10px;"><strong>Location:</strong> {{ $order->address_text }}</p>
            <p style="margin: 0;"><strong>Total:</strong> UGX {{ number_format($order->total_amount) }}</p>
        </div>

        <p style="margin: 22px 0 0; line-height: 1.6;">
            You can open the app any time to view the full order details or leave a rating for your provider.
        </p>

        <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 28px 0;">

        <p style="margin: 0 0 10px; color: #6b7280; font-size: 13px;">
            You are receiving this email because booking emails are enabled for your account.
        </p>
        <p style="margin: 0; font-size: 13px;">
            <a href="{{ $preferenceUrl }}" style="color: #0f766e;">Manage email preferences or unsubscribe</a>
        </p>
    </div>
</body>
</html>
