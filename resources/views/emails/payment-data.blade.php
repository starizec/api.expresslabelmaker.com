<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('payment.email_title') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #045cb8;">{{ __('payment.offer_request') }}</h2>
        
        <div style="margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
            <h3 style="color: #045cb8; margin-top: 0;">{{ __('payment.licence_information') }}</h3>
            <p><strong>{{ __('payment.licence_uid') }}:</strong> {{ $licence->licence_uid }}</p>
            <p><strong>{{ __('payment.domain_name') }}:</strong> {{ $domain->name }}</p>
            <p><strong>{{ __('payment.valid_from') }}:</strong> {{ $licence->valid_from && method_exists($licence->valid_from, 'format') ? $licence->valid_from->format('d.m.Y') : ($licence->valid_from ?? 'N/A') }}</p>
            <p><strong>{{ __('payment.valid_until') }}:</strong> {{ $licence->valid_until && method_exists($licence->valid_until, 'format') ? $licence->valid_until->format('d.m.Y') : ($licence->valid_until ?? 'N/A') }}</p>
        </div>

        <div style="margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
            <h3 style="color: #045cb8; margin-top: 0;">{{ __('payment.user_information') }}</h3>
            <p><strong>{{ __('payment.first_name') }}:</strong> {{ $user->first_name ?? 'N/A' }}</p>
            <p><strong>{{ __('payment.last_name') }}:</strong> {{ $user->last_name ?? 'N/A' }}</p>
            <p><strong>{{ __('payment.email') }}:</strong> {{ $user->email }}</p>
            <p><strong>{{ __('payment.company_name') }}:</strong> {{ $user->company_name ?? 'N/A' }}</p>
            <p><strong>{{ __('payment.company_address') }}:</strong> {{ $user->company_address ?? 'N/A' }}</p>
            <p><strong>{{ __('payment.town') }}:</strong> {{ $user->town ?? 'N/A' }}</p>
            <p><strong>{{ __('payment.country') }}:</strong> {{ $user->country ?? 'N/A' }}</p>
            <p><strong>{{ __('payment.vat_number') }}:</strong> {{ $user->vat_number ?? 'N/A' }}</p>
        </div>

        <p style="color: #666; font-size: 12px; margin-top: 30px;">
            {{ __('payment.email_footer') }}
        </p>
    </div>
</body>
</html>
