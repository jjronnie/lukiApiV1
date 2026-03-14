<html lang="en">
<body style="font-family: Arial, sans-serif; background: #f5f7fb; color: #111827; margin: 0; padding: 24px;">
    <div style="max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 18px; padding: 32px;">
        <p style="margin: 0 0 8px; color: #6b7280;">Luki Provider</p>
        <h1 style="margin: 0 0 18px; font-size: 28px;">Service enrollment declined</h1>
        <p style="margin: 0 0 16px; line-height: 1.6;">
            Hi {{ $providerProfile->display_name }}, your request to enroll for
            <strong>{{ $providerService->service?->name ?? 'this service' }}</strong> was declined.
        </p>

        <div style="padding: 18px; border-radius: 16px; background: #fff7ed; border: 1px solid #fdba74;">
            <p style="margin: 0 0 8px;"><strong>Reason</strong></p>
            <p style="margin: 0; line-height: 1.6;">{{ $providerService->review_reason ?: 'Please review your profile details and try again.' }}</p>
        </div>

        <p style="margin: 22px 0 0; line-height: 1.6;">
            You can return to the provider app to review your services and submit a fresh request when ready.
        </p>
    </div>
</body>
</html>
